<?php

/**
 * IF calc - Sistema de Gestão Acadêmica
 * 
 * Dashboard principal que integra dados do SUAP para fornecer uma visão 
 * consolidada do desempenho acadêmico, incluindo notas, frequências e 
 * previsões de aulas.
 * 
 * @author Seu Nome <Kellyson.medeiros.pdf@gmail.com>
 * @version 1.0.0
 */

require_once 'config.php';
require_once 'calendario.php';
require_once 'api_utils.php';
require_once 'status_falta.php';
session_start();
require_once 'horarios.php';

// Autenticação SUAP
if (!isset($_SESSION['access_token'])) {
    header('Location: login.php');
    exit;
}

// Verificação adicional de autenticação
if (!isset($_SESSION['user_id']) || !isset($_SESSION['access_token_expires'])) {
    // Informações de usuário incompletas
    error_log("Autenticação falhou: Faltam variáveis de sessão. user_id=" .
        (isset($_SESSION['user_id']) ? "definido" : "não definido") . ", access_token_expires=" .
        (isset($_SESSION['access_token_expires']) ? "definido" : "não definido"));

    session_unset();
    session_destroy();
    header("Location: login.php?erro=sessao_expirada");
    exit;
} elseif ($_SESSION['access_token_expires'] < time()) {
    // Token expirado
    error_log("Autenticação falhou: Token expirado em " . date('Y-m-d H:i:s', $_SESSION['access_token_expires']));

    session_unset();
    session_destroy();
    header("Location: login.php?erro=sessao_expirada");
    exit;
} else {
    // Log de sessão bem-sucedida para diagnóstico
    error_log("Autenticação bem-sucedida: user_id=" . $_SESSION['user_id'] .
        ", token expira em " . date('Y-m-d H:i:s', $_SESSION['access_token_expires']));

    // Verificar se o token vai expirar em menos de 5 minutos (300 segundos)
    // Se sim, tentamos renová-lo silenciosamente para evitar interrupções
    if ($_SESSION['access_token_expires'] - time() < 300 && isset($_SESSION['refresh_token'])) {
        error_log("Token vai expirar em breve. Tentando renovar...");
        try {
            // Preparar requisição para renovar o token
            $refresh_request = [
                'grant_type' => 'refresh_token',
                'refresh_token' => $_SESSION['refresh_token'],
                'client_id' => SUAP_CLIENT_ID,
                'client_secret' => SUAP_CLIENT_SECRET
            ];

            // Configurar CURL
            $ch = curl_init(SUAP_URL . "/o/token/");
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($refresh_request),
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            $token_data = json_decode($response, true);

            // Se conseguimos um novo token, atualizar a sessão
            if ($token_data && isset($token_data['access_token'])) {
                $_SESSION['access_token'] = $token_data['access_token'];

                // Atualizar tempo de expiração
                $expires_in = isset($token_data['expires_in']) ? $token_data['expires_in'] : 3600;
                $_SESSION['access_token_expires'] = time() + $expires_in;

                // Atualizar refresh_token se houver um novo
                if (isset($token_data['refresh_token'])) {
                    $_SESSION['refresh_token'] = $token_data['refresh_token'];
                }

                error_log("Token renovado com sucesso!");
            }
        } catch (Exception $e) {
            error_log("Erro ao renovar token: " . $e->getMessage());
            // Continuar com o token atual mesmo se falhar a renovação
        }
    }
}

// Verificação de requisições à API
function verificarRespostaAPI($resposta, $codigoHTTP)
{
    if ($codigoHTTP == 401 || $codigoHTTP == 403) {
        // Token inválido ou acesso negado
        session_unset();
        session_destroy();
        header("Location: login.php?erro=token_invalido");
        exit;
    }
    return $resposta;
}



// Autenticação SUAP
if (!isset($_SESSION['access_token'])) {
    $auth_url = SUAP_URL . "/o/authorize/?" . http_build_query([
        'response_type' => 'code',
        'client_id' => SUAP_CLIENT_ID,
        'redirect_uri' => REDIRECT_URI
    ]);
    header("Location: " . $auth_url);
    exit;
}

/**
 * Realiza requisições autenticadas à API do SUAP
 * 
 * @param string $endpoint Endpoint da API a ser consultado
 * @return array|null Dados retornados pela API ou null em caso de erro
 */
function getSuapData($endpoint)
{
    $ch = curl_init(SUAP_URL . "/api/v2/" . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $_SESSION['access_token']
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    error_log("Resposta da API: " . $response);
    error_log("Código HTTP: " . $httpcode);

    if (curl_errno($ch)) {
        error_log("Erro CURL: " . curl_error($ch));
        curl_close($ch);
        return null;
    }
    curl_close($ch);

    // Verifica se a resposta é válida
    $jsonData = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Erro ao decodificar JSON: " . json_last_error_msg());
        error_log("Resposta original: " . $response);

        // Tentativa de limpar a resposta (remover caracteres inválidos)
        $cleanResponse = preg_replace('/[\x00-\x1F\x7F]/u', '', $response);
        $jsonData = json_decode($cleanResponse, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Segunda tentativa falhou: " . json_last_error_msg());
            return null;
        }
    }

    // Verificar se ocorreu um erro de autenticação
    return verificarRespostaAPI($jsonData, $httpcode);
}

/**
 * Calcula a média direta (MD) usando o sistema de pesos do IF
 * 
 * Fórmula: MD = (2*N1 + 3*N2) / 5
 * 
 * @param float|null $n1 Nota do 1º bimestre
 * @param float|null $n2 Nota do 2º bimestre
 * @return float|null Média direta ou null se alguma nota não existir
 */
function calcularMediaDireta($n1, $n2)
{
    if ($n1 === null || $n2 === null) {
        return null;
    }
    return (2 * $n1 + 3 * $n2) / 5;
}

/**
 * Calcula a nota necessária no próximo bimestre para aprovação direta
 * 
 * Sistema do IF: MD = (2*N1 + 3*N2) / 5 >= 60
 * 
 * @param array $disciplina Dados da disciplina do boletim
 * @return array|null Informações sobre a nota necessária
 */
function calcularNotaNecessariaIF($disciplina)
{
    // Extrai as notas do bimestre
    $n1 = null;
    $n2 = null;

    // Verifica se existem notas nos bimestres
    if (isset($disciplina['nota_etapa_1']['nota'])) {
        $n1 = floatval($disciplina['nota_etapa_1']['nota']);
    }
    if (isset($disciplina['nota_etapa_2']['nota'])) {
        $n2 = floatval($disciplina['nota_etapa_2']['nota']);
    }

    $resultado = array(
        'n1' => $n1,
        'n2' => $n2,
        'media_atual' => null,
        'nota_necessaria' => null,
        'situacao' => 'indefinida',
        'pode_passar_direto' => false,
        'precisa_af' => false,
        'ja_aprovado' => false,
        'ja_reprovado' => false
    );

    // Se tem as duas notas, calcula a média final
    if ($n1 !== null && $n2 !== null) {
        $md = calcularMediaDireta($n1, $n2);
        $resultado['media_atual'] = $md;

        if ($md >= 60) {
            $resultado['situacao'] = 'aprovado_direto';
            $resultado['ja_aprovado'] = true;
            $resultado['pode_passar_direto'] = true;
        } elseif ($md >= 20) {
            $resultado['situacao'] = 'avaliacao_final';
            $resultado['precisa_af'] = true;
        } else {
            $resultado['situacao'] = 'reprovado_nota';
            $resultado['ja_reprovado'] = true;
        }
        return $resultado;
    }

    // Se só tem N1, calcula o que precisa no N2
    if ($n1 !== null && $n2 === null) {
        // MD = (2*N1 + 3*N2) / 5 >= 60
        // 3*N2 >= 300 - 2*N1
        // N2 >= (300 - 2*N1) / 3
        $nota_necessaria = (300 - 2 * $n1) / 3;
        $nota_necessaria = max(0, min(100, $nota_necessaria));

        $resultado['nota_necessaria'] = $nota_necessaria;
        $resultado['situacao'] = 'aguardando_n2';

        if ($nota_necessaria <= 100) {
            $resultado['pode_passar_direto'] = true;
        }

        return $resultado;
    }

    // Se só tem N2 (caso raro), calcula o que precisaria no N1
    if ($n1 === null && $n2 !== null) {
        // MD = (2*N1 + 3*N2) / 5 >= 60
        // 2*N1 >= 300 - 3*N2
        // N1 >= (300 - 3*N2) / 2
        $nota_necessaria = (300 - 3 * $n2) / 2;
        $nota_necessaria = max(0, min(100, $nota_necessaria));

        $resultado['nota_necessaria'] = $nota_necessaria;
        $resultado['situacao'] = 'aguardando_n1';

        return $resultado;
    }

    // Nenhuma nota ainda
    $resultado['situacao'] = 'aguardando_notas';
    return $resultado;
}

/**
 * Calcula a nota necessária na Avaliação Final considerando as 3 fórmulas
 * 
 * @param float $n1 Nota do 1º bimestre
 * @param float $n2 Nota do 2º bimestre
 * @return array Informações sobre a avaliação final
 */
function calcularAvaliacaoFinal($n1, $n2)
{
    $md = calcularMediaDireta($n1, $n2);

    // As 3 fórmulas para MFD >= 60:
    // 1. MFD = (MD + NAF) / 2 >= 60 → NAF >= 120 - MD
    $naf1 = 120 - $md;

    // 2. MFD = (2*NAF + 3*N2) / 5 >= 60 → NAF >= (300 - 3*N2) / 2
    $naf2 = (300 - 3 * $n2) / 2;

    // 3. MFD = (2*N1 + 3*NAF) / 5 >= 60 → NAF >= (300 - 2*N1) / 3
    $naf3 = (300 - 2 * $n1) / 3;

    // A menor nota necessária (mais favorável ao aluno)
    $naf_necessaria = min($naf1, $naf2, $naf3);
    $naf_necessaria = max(0, min(100, $naf_necessaria));

    return array(
        'md' => $md,
        'naf_necessaria' => $naf_necessaria,
        'formula_1' => max(0, min(100, $naf1)),
        'formula_2' => max(0, min(100, $naf2)),
        'formula_3' => max(0, min(100, $naf3)),
        'melhor_opcao' => $naf_necessaria,
        'pode_passar' => $naf_necessaria <= 100
    );
}

/**
 * Calcula notas necessárias considerando uma simulação
 * 
 * @param array $disciplina Dados da disciplina
 * @param float|null $nota_simulada Nota simulada pelo usuário
 * @param int $bimestre_simulado Qual bimestre está sendo simulado (1 ou 2)
 * @return array Resultado da simulação
 */
function simularNota($disciplina, $nota_simulada, $bimestre_simulado)
{
    $n1 = isset($disciplina['nota_etapa_1']['nota']) ? floatval($disciplina['nota_etapa_1']['nota']) : null;
    $n2 = isset($disciplina['nota_etapa_2']['nota']) ? floatval($disciplina['nota_etapa_2']['nota']) : null;

    // Aplica a simulação
    if ($bimestre_simulado == 1) {
        $n1 = $nota_simulada;
    } else {
        $n2 = $nota_simulada;
    }

    // Calcula com a nota simulada
    $calculo = calcularNotaNecessariaIF(array(
        'nota_etapa_1' => array('nota' => $n1),
        'nota_etapa_2' => array('nota' => $n2)
    ));

    return $calculo;
}

/**
 * Função de compatibilidade - mantém a função original para código legado
 */
function calcularNotaNecessaria($notas, $pesos = array(2, 2, 3, 3))
{
    // Adapta para o novo sistema do IF (apenas 2 bimestres)
    $n1 = isset($notas["nota_etapa_1"]['nota']) ? $notas["nota_etapa_1"]['nota'] : null;
    $n2 = isset($notas["nota_etapa_2"]['nota']) ? $notas["nota_etapa_2"]['nota'] : null;

    $resultado = calcularNotaNecessariaIF(array('nota_etapa_1' => array('nota' => $n1), 'nota_etapa_2' => array('nota' => $n2)));

    return $resultado['nota_necessaria'];
}

/**
 * Determina qual é a próxima aula considerando a lógica de dias
 * 
 * @param array $horarios Horários das disciplinas
 * @return array Informações sobre a próxima aula
 */
function getProximaAula($horarios)
{
    error_log("=== DEBUG GETPROXIMAAULA INICIO ===");
    error_log("Horários recebidos: " . (is_array($horarios) ? count($horarios) : 'não é array'));
    
    $hoje = date('N'); // 1=Segunda ... 7=Domingo
    $horaAtual = date('H:i');
    error_log("Hoje: {$hoje}, Hora atual: {$horaAtual}");

    // Primeiro, verifica se ainda há aulas hoje
    $aulasHoje = getAulasHoje($horarios);
    error_log("Aulas hoje: " . count($aulasHoje));

    if (!empty($aulasHoje)) {
        error_log("Há aulas hoje, ordenando...");
        // Ordena as aulas de hoje por horário
        $aulasOrdenadas = ordenarAulasPorHorario($aulasHoje);
        error_log("Aulas ordenadas: " . count($aulasOrdenadas));

        foreach ($aulasOrdenadas as $aula) {
            error_log("Verificando aula: " . print_r($aula, true));
            // Extrai hora de início da aula (formato "07:00 - 07:45")
            if (isset($aula['horario_detalhado'])) {
                $horaInicio = explode(' - ', $aula['horario_detalhado'])[0];
                error_log("Hora início: {$horaInicio}, Hora atual: {$horaAtual}");
                if ($horaInicio > $horaAtual) {
                    error_log("Aula ainda não começou hoje");
                    return array(
                        'tipo' => 'hoje',
                        'dia_nome' => 'hoje',
                        'aulas' => array($aula),
                        'data' => new DateTime()
                    );
                }
            }
        }
        error_log("Todas as aulas de hoje já passaram");
    }

    // Se não há mais aulas hoje, busca amanhã
    error_log("Buscando aulas de amanhã...");
    $amanha = ($hoje >= 5) ? 1 : $hoje + 1; // Se for sex/sab/dom, próximo é segunda
    error_log("Amanhã calculado: {$amanha}");
    $aulasAmanha = getAulasAmanha($horarios);
    error_log("Aulas amanhã: " . count($aulasAmanha));

    if (!empty($aulasAmanha)) {
        error_log("Há aulas amanhã");
        $diasSemana = array(1 => 'Segunda-feira', 2 => 'Terça-feira', 3 => 'Quarta-feira', 4 => 'Quinta-feira', 5 => 'Sexta-feira', 6 => 'Sábado', 7 => 'Domingo');
        return array(
            'tipo' => 'amanha',
            'dia_nome' => $diasSemana[$amanha],
            'aulas' => ordenarAulasPorHorario($aulasAmanha),
            'data' => new DateTime('+1 day')
        );
    }

    // Se não há aulas amanhã, busca no próximo dia útil
    error_log("Buscando aulas em dias futuros...");
    for ($i = 2; $i <= 7; $i++) {
        $diaFuturo = ($hoje + $i - 1) % 7 + 1;
        error_log("Verificando dia futuro {$i}: {$diaFuturo}");
        if ($diaFuturo > 5) {
            error_log("Pulando fim de semana: {$diaFuturo}");
            continue; // Pula fins de semana
        }

        $aulasFuturas = getAulasDoDia($horarios, $diaFuturo + 1); // +1 para conversão SUAP
        error_log("Aulas no dia {$diaFuturo}: " . count($aulasFuturas));
        if (!empty($aulasFuturas)) {
            error_log("Encontrou aulas no dia {$diaFuturo}");
            $diasSemana = array(1 => 'Segunda-feira', 2 => 'Terça-feira', 3 => 'Quarta-feira', 4 => 'Quinta-feira', 5 => 'Sexta-feira');
            return array(
                'tipo' => 'futuro',
                'dia_nome' => $diasSemana[$diaFuturo],
                'aulas' => ordenarAulasPorHorario($aulasFuturas),
                'data' => new DateTime('+' . $i . ' days')
            );
        }
    }

    // Nenhuma aula encontrada
    error_log("Nenhuma aula encontrada em nenhum dia");
    return array(
        'tipo' => 'nenhuma',
        'dia_nome' => '',
        'aulas' => array(),
        'data' => null
    );
}

/**
 * Processa o horário das aulas de um dia específico
 * 
 * @param array $horarios Dados dos horários retornados pela API
 * @param int $dia Dia da semana (1-7, onde 1 é segunda)
 * @return array Aulas do dia especificado
 */
function getAulasDoDia($horarios, $dia)
{
    if (!is_array($horarios)) {
        return [];
    }

    $aulas = [];

    foreach ($horarios as $disciplina) {
        if (!isset($disciplina['horarios_de_aula']) || empty($disciplina['horarios_de_aula'])) {
            continue;
        }

        $horariosArray = parseHorario($disciplina['horarios_de_aula']);

        foreach ($horariosArray as $h) {
            if ($h['dia'] == $dia) {
                $local = 'Local não definido';

                // Tenta obter o local de várias formas possíveis
                if (isset($disciplina['locais_de_aula']) && !empty($disciplina['locais_de_aula'])) {
                    if (is_array($disciplina['locais_de_aula'])) {
                        $local = implode(', ', $disciplina['locais_de_aula']);
                    } else {
                        $local = $disciplina['locais_de_aula'];
                    }
                } elseif (isset($disciplina['local']) && !empty($disciplina['local'])) {
                    $local = $disciplina['local'];
                }

                $aulas[] = [
                    'sigla' => $disciplina['sigla'] ?? ($disciplina['codigo'] ?? ''),
                    'disciplina' => $disciplina['sigla'] ?? ($disciplina['codigo'] ?? ''),
                    'descricao' => $disciplina['descricao'] ?? ($disciplina['nome'] ?? $disciplina['disciplina'] ?? ''),
                    'locais' => is_array($disciplina['locais_de_aula'] ?? []) ? $disciplina['locais_de_aula'] : [$local],
                    'local' => $local,
                    'horario' => $h
                ];
            }
        }
    }

    return $aulas;
}

/**
 * Processa o horário das aulas para uma data específica
 * 
 * @param array $horarios Dados dos horários retornados pela API
 * @param DateTime $data Data específica para buscar as aulas
 * @return array Aulas da data especificada
 */
function getAulasDeData($horarios, $data)
{
    // Obtém o dia da semana (1 = segunda, 7 = domingo)
    $diaSemana = (int)$data->format('N');

    // No SUAP: 2=Segunda, 3=Terça, 4=Quarta, 5=Quinta, 6=Sexta
    // Converte do formato ISO (1-7) para SUAP (2-6)
    $diaSemana = ($diaSemana == 7) ? 0 : $diaSemana + 1;

    // Se for domingo, retorna array vazio
    if ($diaSemana < 2 || $diaSemana > 6) {
        return [];
    }

    $aulas = [];
    foreach ($horarios as $disciplina) {
        if (!empty($disciplina['horarios_de_aula'])) {
            $horariosParsed = parseHorario($disciplina['horarios_de_aula']);

            foreach ($horariosParsed as $horario) {
                if ($horario['dia'] == $diaSemana) {
                    $aulas[] = [
                        'sigla' => $disciplina['sigla'] ?? '',
                        'descricao' => $disciplina['descricao'] ?? '',
                        'turma' => $disciplina['id'] ?? '',
                        'horario' => $horario,
                        'locais' => !empty($disciplina['locais_de_aula']) ? $disciplina['locais_de_aula'] : []
                    ];
                }
            }
        }
    }

    return $aulas;
}

/**
 * Processa o horário das aulas do dia seguinte
 * 
 * Lida com casos especiais como sexta-feira e fim de semana,
 * ajustando para mostrar as aulas do próximo dia útil.
 */
function getAulasAmanha($horarios)
{
    // Pega o dia atual (1-7, onde 1 é segunda)
    $hoje = date('N');
    $amanha = $hoje + 1;

    // Se for sexta (5), ajusta para segunda (1)
    // Se for final de semana (6 ou 7), ajusta para segunda (1)
    if ($hoje >= 5) {
        $amanha = 1;  // Segunda = 1 na notação do date('N')
    }

    // Converte para o formato do SUAP (2-6), onde 2=Segunda
    $amanhaSuap = ($amanha == 7) ? 0 : $amanha + 1;

    return getAulasDoDia($horarios, $amanhaSuap);
}

/**
 * Processa o horário das aulas de hoje
 * 
 * @param array $horarios Dados dos horários retornados pela API
 * @return array Aulas de hoje
 */
function getAulasHoje($horarios)
{
    $hoje = date('N'); // (1-7, onde 1 é segunda)

    // Converte para o formato do SUAP (2-6), onde 2=Segunda
    $hojeSuap = $hoje + 1;

    // Se for final de semana (6-sábado ou 7-domingo), retorna vazio
    // Comentado para testes - Isso permitirá ver aulas mesmo nos fins de semana
    // if ($hoje > 5) {
    //     return [];
    // }

    return getAulasDoDia($horarios, $hojeSuap);
}

/**
 * Ordena as aulas cronologicamente e adiciona detalhes dos horários
 * 
 * @param array $aulasAmanha Array de aulas a serem ordenadas
 * @return array Aulas ordenadas com horários detalhados
 */
function ordenarAulasPorHorario($aulasAmanha)
{
    $horarios = [
        // Horários da manhã
        'M1' => ['turno' => 'M', 'aula' => '1', 'hora' => '07:00 - 07:45'],
        'M2' => ['turno' => 'M', 'aula' => '2', 'hora' => '07:45 - 08:30'],
        'M3' => ['turno' => 'M', 'aula' => '3', 'hora' => '08:50 - 09:35'],
        'M4' => ['turno' => 'M', 'aula' => '4', 'hora' => '09:35 - 10:20'],
        'M5' => ['turno' => 'M', 'aula' => '5', 'hora' => '10:30 - 11:15'],
        'M6' => ['turno' => 'M', 'aula' => '6', 'hora' => '11:15 - 12:00'],
        // Horários da tarde (vespertino)
        'V1' => ['turno' => 'V', 'aula' => '1', 'hora' => '13:00 - 13:45'],
        'V2' => ['turno' => 'V', 'aula' => '2', 'hora' => '13:45 - 14:30'],
        'V3' => ['turno' => 'V', 'aula' => '3', 'hora' => '14:50 - 15:35'],
        'V4' => ['turno' => 'V', 'aula' => '4', 'hora' => '15:35 - 16:20'],
        'V5' => ['turno' => 'V', 'aula' => '5', 'hora' => '16:30 - 17:15'],
        'V6' => ['turno' => 'V', 'aula' => '6', 'hora' => '17:15 - 18:00']
    ];

    $aulasOrdenadas = [];

    foreach ($aulasAmanha as $aula) {
        // Debug: vamos logar a estrutura da aula
        error_log("Processando aula: " . print_r($aula, true));

        if (isset($aula['horario']) && isset($aula['horario']['aulas']) && is_array($aula['horario']['aulas'])) {
            foreach ($aula['horario']['aulas'] as $numeroAula) {
                $chave = $aula['horario']['turno'] . $numeroAula;
                if (isset($horarios[$chave])) {
                    $aulaCompleta = $aula;
                    $aulaCompleta['horario_detalhado'] = $horarios[$chave]['hora'];
                    $aulaCompleta['ordem'] = $chave;
                    $aulasOrdenadas[] = $aulaCompleta;
                }
            }
        } else {
            // Se não tem horário estruturado, ainda adiciona a aula
            $aulaCompleta = $aula;
            $aulaCompleta['horario_detalhado'] = 'Horário não definido';
            $aulaCompleta['ordem'] = 'Z99'; // Para ficar no final
            $aulasOrdenadas[] = $aulaCompleta;
        }
    }

    if (!empty($aulasOrdenadas)) {
        usort($aulasOrdenadas, function ($a, $b) {
            return strcmp($a['ordem'], $b['ordem']);
        });
    }

    return $aulasOrdenadas;
}

/**
 * Avalia se é seguro faltar à aula com base na frequência atual
 * 
 * Retorna um status que indica o risco de faltar:
 * - 'success': pode faltar com segurança
 * - 'warning': próximo do limite de faltas
 * - 'danger': não deve faltar
 */
function podeFaltarAmanha($disciplina)
{
    if (!isset($disciplina['percentual_carga_horaria_frequentada'])) {
        return 'danger';
    }

    $frequenciaAtual = $disciplina['percentual_carga_horaria_frequentada'];
    $totalFaltas = $disciplina['numero_faltas'];
    $maximoFaltas = ceil($disciplina['carga_horaria'] * 0.25);

    // Se já estiver próximo do limite (restando 3 faltas ou menos)
    if (($maximoFaltas - $totalFaltas) <= 3 && ($maximoFaltas - $totalFaltas) > 0) {
        return 'warning';
    }

    // Se ainda pode faltar com folga
    if (($maximoFaltas - $totalFaltas) > 3) {
        return 'success';
    }

    return 'danger';
}

/**
 * Calcula o impacto de uma falta na frequência da disciplina
 * 
 * @return array|null Dados do impacto ou null se não houver dados suficientes
 */
function agruparAulasConsecutivas($aulas)
{
    if (empty($aulas)) {
        return array();
    }
    
    $aulasAgrupadas = array();
    $grupoAtual = array();
    $disciplinaAtual = null;
    
    foreach ($aulas as $aula) {
        $disciplina = $aula['disciplina'] ?? $aula['descricao'] ?? '';
        
        // Se é a primeira aula ou se a disciplina mudou
        if ($disciplinaAtual === null || $disciplina !== $disciplinaAtual) {
            
            // Salva o grupo anterior se existir
            if (!empty($grupoAtual)) {
                $aulasAgrupadas[] = $grupoAtual;
            }
            
            // Inicia novo grupo
            $grupoAtual = array($aula);
            $disciplinaAtual = $disciplina;
        } else {
            // Adiciona ao grupo atual (mesma disciplina)
            $grupoAtual[] = $aula;
        }
    }
    
    // Adiciona o último grupo
    if (!empty($grupoAtual)) {
        $aulasAgrupadas[] = $grupoAtual;
    }
    
    return $aulasAgrupadas;
}

function calcularImpactoFalta($disciplina)
{
    if (!isset($disciplina['percentual_carga_horaria_frequentada'])) {
        return null;
    }

    $frequenciaAtual = $disciplina['percentual_carga_horaria_frequentada'];
    $totalAulas = $disciplina['carga_horaria_cumprida'];
    $totalFaltas = $disciplina['numero_faltas'];
    $cargaTotal = $disciplina['carga_horaria'];
    $maximoFaltas = ceil($cargaTotal * 0.25);
    $presencaMinima = 75; // Porcentagem mínima de frequência

    if ($totalAulas == 0) return null;

    // Calcula a nova frequência considerando mais uma falta
    $novaFrequencia = (($totalAulas - $totalFaltas - 1) / $totalAulas) * 100;

    // Calcula quantas aulas podem ser perdidas ainda
    $faltasRestantes = $maximoFaltas - $totalFaltas;

    // Calcula a porcentagem de faltas
    $porcentagemFaltas = ($totalFaltas / $cargaTotal) * 100;

    // Calcula a porcentagem de faltas após mais uma falta
    $porcentagemFaltasAposUmaFalta = (($totalFaltas + 1) / $cargaTotal) * 100;

    // Calcula o risco
    $nivelRisco = 'baixo';
    if ($faltasRestantes <= 3 && $faltasRestantes > 0) {
        $nivelRisco = 'medio';
    } else if ($faltasRestantes <= 0) {
        $nivelRisco = 'alto';
    }

    // Calcula a proporção para a barra de progresso
    $proporcaoFaltas = min(100, ($totalFaltas / $maximoFaltas) * 100);

    return [
        'atual' => $frequenciaAtual,
        'nova' => max(0, $novaFrequencia),
        'impacto' => $frequenciaAtual - $novaFrequencia,
        'faltas_atuais' => $totalFaltas,
        'maximo_faltas' => $maximoFaltas,
        'faltas_restantes' => max(0, $faltasRestantes),
        'porcentagem_faltas' => $porcentagemFaltas,
        'porcentagem_faltas_nova' => $porcentagemFaltasAposUmaFalta,
        'nivel_risco' => $nivelRisco,
        'proporcao_faltas' => $proporcaoFaltas,
        'aulas_totais' => $cargaTotal,
        'aulas_dadas' => $totalAulas,
        'aulas_restantes' => $cargaTotal - $totalAulas,
        'presenca_minima' => $presencaMinima
    ];
}

// Carregamento inicial dos dados
$meusDados = getSuapData("minhas-informacoes/meus-dados/");

// Armazena todas as respostas da API para depuração
$apiResponses = [
    'meusDados' => $meusDados
];

$boletim = [];
$horarios = [];
$anoLetivo = date('Y');
$periodoLetivo = '1';

if ($meusDados && isset($meusDados['matricula'])) {
    // Detectar automaticamente o período adequado baseado na situação do aluno
    $situacao = isset($meusDados['vinculo']['situacao']) ? $meusDados['vinculo']['situacao'] : '';

    if ($situacao === 'Concluído' || $situacao === 'Desligado' || $situacao === 'Evadido') {
        // Para alunos formados/desligados, buscar dados dos últimos anos
        error_log("Aluno com situação '{$situacao}' - buscando dados históricos");

        // Tentar anos anteriores (2024, 2023, 2022)
        $anosParaTentar = [2024, 2023, 2022, 2021];
        $periodosParaTentar = [2, 1]; // Primeiro semestre 2, depois 1

        foreach ($anosParaTentar as $ano) {
            foreach ($periodosParaTentar as $periodo) {
                error_log("Tentando buscar dados para {$ano}.{$periodo}");

                $boletimTeste = getSuapData("minhas-informacoes/boletim/{$ano}/{$periodo}/");
                $horariosTeste = getSuapData("minhas-informacoes/turmas-virtuais/{$ano}/{$periodo}/");
                if (is_array($boletimTeste) && !empty($boletimTeste)) {
                    $boletim = $boletimTeste;
                    $anoLetivo = $ano;
                    $periodoLetivo = $periodo;
                    error_log("Dados encontrados para {$ano}.{$periodo} - " . count($boletim) . " disciplinas");
                    break 2; // Sai dos dois loops
                }
            }
        }

        // Se encontrou boletim, buscar horários correspondentes
        if (!empty($boletim)) {
            $horarios = getSuapData("minhas-informacoes/turmas-virtuais/{$anoLetivo}/{$periodoLetivo}/");
        }
    } else {
        // Para alunos ativos, usar ano/período atual
        $boletim = getSuapData("minhas-informacoes/boletim/{$anoLetivo}/{$periodoLetivo}/");
        $horarios = getSuapData("minhas-informacoes/turmas-virtuais/{$anoLetivo}/{$periodoLetivo}/");
    }

    // Limpa e corrige os dados antes de armazenar
    $boletim = is_array($boletim) ? $boletim : [];
    $horarios = is_array($horarios) ? sanitizarDadosAPI($horarios) : [];

    // Adiciona as respostas para depuração
    $apiResponses['boletim'] = $boletim;
    $apiResponses['horarios'] = $horarios;

    error_log("Dados finais carregados - Boletim: " . count($boletim) . " disciplinas, Horários: " . count($horarios) . " turmas para {$anoLetivo}.{$periodoLetivo}");
    
    // Log para verificar se os horários estão corretos
    error_log("=== VERIFICAÇÃO HORÁRIOS ===");
    error_log("Horários após sanitização: " . count($horarios));
    if (is_array($horarios) && !empty($horarios)) {
        foreach ($horarios as $index => $disciplina) {
            error_log("Disciplina {$index}: " . ($disciplina['sigla'] ?? 'sem sigla') . " - " . ($disciplina['horarios_de_aula'] ?? 'sem horário'));
        }
    }
    error_log("=== FIM VERIFICAÇÃO HORÁRIOS ===");
    
    // Log para verificar se a variável $horarios está sendo modificada
    error_log("=== VERIFICAÇÃO VARIÁVEL HORÁRIOS ===");
    error_log("Variável \$horarios definida com " . count($horarios) . " elementos");
    error_log("=== FIM VERIFICAÇÃO VARIÁVEL HORÁRIOS ===");
}

// Preparação dos dados para a view
$diasSemana = [1 => 'Segunda-feira', 2 => 'Terça-feira', 3 => 'Quarta-feira', 4 => 'Quinta-feira', 5 => 'Sexta-feira', 6 => 'Sábado', 7 => 'Domingo'];
$hoje = date('N'); // 1 (Segunda) até 7 (Domingo)
$amanha = ($hoje >= 5) ? 1 : $hoje + 1; // Ajustado para corresponder à lógica de getAulasAmanha()
$diaHoje = $diasSemana[$hoje];
$diaAmanha = $diasSemana[$amanha];

// Define o padrão de exibição (hoje, amanhã, ou data específica)
$mostrarDia = 'amanha'; // Valor padrão

// Se dia=hoje está especificado na URL
if (isset($_GET['dia']) && $_GET['dia'] === 'hoje') {
    $mostrarDia = 'hoje';
    $tituloDia = 'Hoje';
    $nomeDia = $diaHoje;
    $dataExibicao = null; // Data atual
}
// Se uma data específica é fornecida
else if (isset($_GET['data'])) {
    $dataFormatada = $_GET['data'];
    $dataExibicao = new DateTime($dataFormatada);

    if ($dataExibicao) {
        $mostrarDia = 'data';
        $tituloDia = 'Dia selecionado';

        // Obter o dia da semana (1-7)
        $diaSemanaNumero = (int)$dataExibicao->format('N');
        $nomeDia = $diasSemana[$diaSemanaNumero];

        // Verificar se é feriado
        $feriado = verificarFeriado($dataExibicao);
        if ($feriado) {
            // É feriado, não há aulas
            $nomeDia .= ' - ' . $feriado . ' (Feriado)';
        }
    } else {
        $mostrarDia = 'amanha';
        $tituloDia = 'Amanhã';
        $nomeDia = $diaAmanha;
        $dataExibicao = null;
    }
}
// Padrão é mostrar amanhã
else {
    $tituloDia = 'Amanhã';
    $nomeDia = $diaAmanha;
    $dataExibicao = null;
}

// Configuração da página
$pageTitle = 'Dashboard - IF calc';
ob_start(); // Inicia o buffer de saída

// Calcular estatísticas do usuário
$totalDisciplinas = isset($boletim) ? count($boletim) : 0;
$mediaGeral = 0;
$frequenciaGeral = 0;
$statusGeral = 'Bom';

if ($totalDisciplinas > 0) {
    $somaNotas = 0;
    $somaFrequencia = 0;
    $countNotas = 0;
    $countFrequencia = 0;

    foreach ($boletim as $disciplina) {
        if (isset($disciplina['media_final_disciplina']) && $disciplina['media_final_disciplina'] !== null) {
            $somaNotas += $disciplina['media_final_disciplina'];
            $countNotas++;
        }
        if (isset($disciplina['percentual_carga_horaria_frequentada']) && $disciplina['percentual_carga_horaria_frequentada'] !== null) {
            $somaFrequencia += $disciplina['percentual_carga_horaria_frequentada'];
            $countFrequencia++;
        }
    }

    if ($countNotas > 0) {
        $mediaGeral = $somaNotas / $countNotas;
    }
    if ($countFrequencia > 0) {
        $frequenciaGeral = $somaFrequencia / $countFrequencia;
    }

    // Determinar status geral
    if ($mediaGeral >= 80 && $frequenciaGeral >= 90) {
        $statusGeral = 'Excelente';
    } elseif ($mediaGeral >= 70 && $frequenciaGeral >= 85) {
        $statusGeral = 'Muito Bom';
    } elseif ($mediaGeral >= 60 && $frequenciaGeral >= 75) {
        $statusGeral = 'Bom';
    } else {
        $statusGeral = 'Precisa Melhorar';
    }
}

// Determinar se pode faltar hoje
$podeFaltarHoje = true;
$aulasDeHoje = isset($horarios) ? getAulasHoje($horarios) : [];
if (!empty($aulasDeHoje)) {
    foreach ($aulasDeHoje as $aula) {
        if (isset($boletim)) {
            foreach ($boletim as $item) {
                if (isset($aula['sigla']) && strpos($item['disciplina'], $aula['sigla']) !== false) {
                    $status = podeFaltarAmanha($item);
                    if ($status === 'danger') {
                        $podeFaltarHoje = false;
                        break 2;
                    }
                }
            }
        }
    }
}

// Obter informações da próxima aula
error_log("=== DEBUG GETPROXIMAAULA ===");
error_log("Horários disponíveis: " . (is_array($horarios) ? count($horarios) : 'não é array'));
error_log("Dados dos horários: " . print_r($horarios, true));

$proximaAula = getProximaAula($horarios);
error_log("Resultado getProximaAula: " . print_r($proximaAula, true));

$podeFaltarProximaAula = true;

// Determinar se pode faltar na próxima aula
if (!empty($proximaAula['aulas'])) {
    foreach ($proximaAula['aulas'] as $aula) {
        if (isset($boletim)) {
            foreach ($boletim as $item) {
                if (isset($aula['sigla']) && strpos($item['disciplina'], $aula['sigla']) !== false) {
                    $status = podeFaltarAmanha($item);
                    if ($status === 'danger') {
                        $podeFaltarProximaAula = false;
                        break 2;
                    }
                }
            }
        }
    }
}
?>

<!-- Grid Background -->
<div class="grid-background">
    <div class="grid-overlay"></div>

    <!-- Main Container -->
    <div class="max-w-4xl mx-auto px-6 py-12">

        <!-- Header Section -->
        <div class="text-center mb-12">
            <div class="relative inline-block mb-6">
                <?php if (isset($meusDados['url_foto_150x200'])): ?>
                    <img src="<?php echo htmlspecialchars($meusDados['url_foto_150x200']); ?>"
                        alt="<?php echo isset($meusDados['nome_usual']) ? htmlspecialchars($meusDados['nome_usual']) : 'Estudante'; ?>"
                        class="profile-image">
                <?php else: ?>
                    <div class="profile-placeholder">
                        <i class="fas fa-user-graduate text-4xl"></i>
                    </div>
                <?php endif; ?>
                <div class="status-indicator"></div>
            </div>

            <h1 class="main-title">
                <?php echo isset($meusDados['nome_usual']) ? htmlspecialchars($meusDados['nome_usual']) : 'Estudante'; ?>
            </h1>
            <p class="registration-code">
                <?php echo isset($meusDados['matricula']) ? htmlspecialchars($meusDados['matricula']) : ''; ?>
            </p>

            <div class="course-info">
                <i class="fas fa-graduation-cap w-4 h-4"></i>
                <span><?php echo isset($meusDados['vinculo']['curso']) ? htmlspecialchars($meusDados['vinculo']['curso']) : 'Curso não informado'; ?></span>
            </div>
        </div>

        <!-- Status Principal - Pode Faltar na Próxima Aula -->
        <div class="main-status-card <?php echo $podeFaltarProximaAula ? 'can-skip' : 'cannot-skip'; ?>">
            <div class="status-content">
                <div class="status-icon-wrapper">
                    <?php if ($podeFaltarProximaAula): ?>
                        <i class="fas fa-check-circle status-icon-large text-emerald-500"></i>
                    <?php else: ?>
                        <i class="fas fa-exclamation-triangle status-icon-large text-red-500"></i>
                    <?php endif; ?>
                </div>

                <h2 class="status-title <?php echo $podeFaltarProximaAula ? 'text-emerald-400' : 'text-red-400'; ?>">
                    <?php echo $podeFaltarProximaAula ? 'PODE FALTAR' : 'CUIDADO!'; ?>
                </h2>

                <p class="status-description">
                    <?php
                    if ($proximaAula['tipo'] === 'nenhuma') {
                        echo 'Nenhuma aula próxima encontrada.';
                    } else {
                        echo $podeFaltarProximaAula
                            ? 'Sua frequência permite faltar na próxima aula.'
                            : 'Evite faltar - você está próximo do limite de faltas.';
                    }
                    ?>
                </p>

                <?php if ($proximaAula['tipo'] !== 'nenhuma'): ?>
                    <div class="next-class-info">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Próxima: <?php echo $proximaAula['dia_nome']; ?></span>
                        <?php if (!empty($proximaAula['aulas'])): ?>
                            <span class="class-count"><?php echo count($proximaAula['aulas']); ?> aula(s)</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="frequency-info">
                    Frequência geral: <span class="frequency-value"><?php echo number_format($frequenciaGeral, 1); ?>%</span>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-content">
                    <i class="fas fa-book stat-icon text-blue-400"></i>
                    <div class="stat-value"><?php echo $totalDisciplinas; ?></div>
                    <div class="stat-label">Disciplinas</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-content">
                    <div class="stat-icon-custom bg-purple-500">M</div>
                    <div class="stat-value"><?php echo number_format($mediaGeral, 1); ?></div>
                    <div class="stat-label">Média</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-content">
                    <i class="fas fa-user stat-icon text-emerald-400"></i>
                    <div class="stat-value"><?php echo number_format($frequenciaGeral, 1); ?>%</div>
                    <div class="stat-label">Frequência</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-content">
                    <div class="stat-icon-custom bg-green-500">✓</div>
                    <div class="stat-value"><?php echo $statusGeral; ?></div>
                    <div class="stat-label">Status</div>
                </div>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="footer-info">
            <div class="period-badge">
                <?php echo $anoLetivo; ?>.<?php echo $periodoLetivo; ?>
            </div>
            <div class="last-update">
                <i class="fas fa-clock"></i>
                <span>Atualizado às <?php echo date('H:i'); ?></span>
            </div>
        </div>
    </div>

    <!-- Seção de Próximas Aulas -->
    <?php 
    // Logs para debug da seção de próximas aulas
    error_log("=== DEBUG PRÓXIMAS AULAS ===");
    error_log("Tipo da próxima aula: " . $proximaAula['tipo']);
    error_log("Dia da próxima aula: " . $proximaAula['dia_nome']);
    error_log("Quantidade de aulas: " . count($proximaAula['aulas']));
    error_log("Dados da próxima aula: " . print_r($proximaAula, true));
    
    if ($proximaAula['tipo'] !== 'nenhuma'): 
        error_log("Exibindo seção de próximas aulas");
    ?>
        <div class="aulas-section">
            <h2 class="section-title">
                <i class="fas fa-clock"></i>
                <span>Próximas Aulas - <?php echo $proximaAula['dia_nome']; ?></span>
            </h2>

        <?php 
        error_log("Iniciando loop das aulas");
        
        // Agrupa aulas consecutivas da mesma disciplina
        $aulasAgrupadas = agruparAulasConsecutivas($proximaAula['aulas']);
        error_log("Aulas agrupadas: " . count($aulasAgrupadas) . " grupos");
        
        foreach ($aulasAgrupadas as $grupo):
            $aula = $grupo[0]; // Primeira aula do grupo
            $quantidadeAulas = count($grupo);
            
            error_log("Processando grupo com {$quantidadeAulas} aulas");
            error_log("Disciplina: " . ($aula['disciplina'] ?? $aula['descricao'] ?? 'não definida'));
            error_log("Horários no grupo: " . print_r(array_map(function($a) { return $a['horario_detalhado'] ?? 'sem horário'; }, $grupo), true));
            
            // Encontra a disciplina correspondente no boletim
            $disciplinaBoletim = null;
            $impactoFalta = null;
            $podeFaltar = 'danger';

            if (isset($boletim)) {
                error_log("Procurando disciplina no boletim para sigla: " . ($aula['sigla'] ?? 'não definida'));
                foreach ($boletim as $item) {
                    if (isset($aula['sigla']) && strpos($item['disciplina'], $aula['sigla']) !== false) {
                        $disciplinaBoletim = $item;
                        $impactoFalta = calcularImpactoFalta($item);
                        $podeFaltar = podeFaltarAmanha($item);
                        error_log("Disciplina encontrada: " . $item['disciplina']);
                        error_log("Pode faltar: " . $podeFaltar);
                        break;
                    }
                }
            }

            $statusClass = '';
            $statusText = '';
            $statusIcon = '';

            switch ($podeFaltar) {
                case 'success':
                    $statusClass = 'can-skip';
                    $statusText = 'Pode faltar';
                    $statusIcon = 'check';
                    break;
                case 'warning':
                    $statusClass = 'be-careful';
                    $statusText = 'Cuidado';
                    $statusIcon = 'exclamation';
                    break;
                default:
                    $statusClass = 'avoid-skip';
                    $statusText = 'Evite faltar';
                    $statusIcon = 'times';
                    break;
            }
            
            error_log("Status da aula: {$statusClass} - {$statusText}");
        ?>
            <div class="aula-card compact">
                <div class="aula-header">
                    <div class="aula-info">
                        <div class="aula-title">
                            <h3><?php echo isset($aula['descricao']) ? htmlspecialchars($aula['descricao']) : htmlspecialchars($aula['disciplina'] ?? 'Aula'); ?></h3>
                            <?php if ($quantidadeAulas > 1): ?>
                                <span class="aula-count"><?php echo $quantidadeAulas; ?> aulas consecutivas</span>
                            <?php endif; ?>
                        </div>
                        <div class="aula-details">
                            <?php if ($quantidadeAulas > 1): ?>
                                <?php
                                // Combina os horários de todas as aulas do grupo
                                $horariosGrupo = array();
                                foreach ($grupo as $aulaGrupo) {
                                    if (isset($aulaGrupo['horario_detalhado'])) {
                                        $horariosGrupo[] = $aulaGrupo['horario_detalhado'];
                                    }
                                }
                                $horarioCombinado = implode(' + ', $horariosGrupo);
                                ?>
                                <div class="aula-detail">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo $horarioCombinado; ?></span>
                                </div>
                            <?php else: ?>
                                <?php if (isset($aula['horario_detalhado'])): ?>
                                    <div class="aula-detail">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo $aula['horario_detalhado']; ?></span>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <div class="aula-detail">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>
                                    <?php
                                    if (isset($aula['locais']) && is_array($aula['locais']) && !empty($aula['locais'])) {
                                        echo htmlspecialchars(implode(', ', $aula['locais']));
                                    } else if (isset($aula['local']) && !empty($aula['local'])) {
                                        echo htmlspecialchars($aula['local']);
                                    } else {
                                        echo 'Local não definido';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="status-badge <?php echo $statusClass; ?>">
                        <i class="fas fa-<?php echo $statusIcon; ?>"></i>
                        <span><?php echo $statusText; ?></span>
                    </div>
                </div>

                <?php if ($impactoFalta && isset($impactoFalta['faltas_restantes'])): ?>
                    <div class="attendance-info compact">
                        <div class="attendance-details">
                            <span class="text-muted">
                                <i class="fas fa-user-times"></i>
                                <span><strong><?php echo $impactoFalta['faltas_atuais']; ?></strong>/<strong><?php echo $impactoFalta['maximo_faltas']; ?></strong> faltas</span>
                            </span>
                            <span class="status-badge <?php echo $statusClass; ?>">
                                <strong><?php echo $impactoFalta['faltas_restantes']; ?></strong> restantes
                            </span>
                        </div>
                        <div class="progress-container">
                            <div class="progress-bar-custom <?php echo $podeFaltar; ?>"
                                style="width: <?php echo $impactoFalta['proporcao_faltas']; ?>%"
                                title="<?php echo $impactoFalta['proporcao_faltas']; ?>% das faltas utilizadas">
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php 
        endforeach; 
        error_log("Finalizado loop das aulas");
        ?>
        </div>
    <?php 
    else:
        error_log("Nenhuma próxima aula encontrada - não exibindo seção");
    ?>
        <!-- Estado vazio quando não há próximas aulas -->
        <div class="aulas-section">
            <h2 class="section-title">
                <i class="fas fa-clock"></i>
                <span>Próximas Aulas</span>
            </h2>
            <div class="empty-aulas">
                <i class="fas fa-calendar-times"></i>
                <h4>Nenhuma aula próxima</h4>
                <p>Não há aulas programadas para os próximos dias úteis.</p>
            </div>
        </div>
    <?php 
    endif; 
    error_log("=== FIM DEBUG PRÓXIMAS AULAS ===");
    ?>

    <!-- Seção do Boletim com Simulador -->
    <?php if (isset($boletim) && is_array($boletim) && !empty($boletim)): ?>
        <div class="boletim-section">
            <div class="boletim-header">
                <div class="boletim-title">
                    <i class="fas fa-chart-line"></i>
                    <div>
                        <h2>Boletim Acadêmico</h2>
                        <p>Sistema IF: MD = (2×N1 + 3×N2) ÷ 5</p>
                    </div>
                </div>
                
                <div class="boletim-info-list">
                    <div class="info-item">
                        <i class="fas fa-book"></i>
                        <span><strong><?php echo count($boletim); ?></strong> disciplinas</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-calculator"></i>
                        <span>Média geral: <strong><?php echo number_format($mediaGeral, 1); ?></strong></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-user-check"></i>
                        <span>Frequência: <strong><?php echo number_format($frequenciaGeral, 1); ?>%</strong></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Status: <strong><?php echo $statusGeral; ?></strong></span>
                    </div>
                </div>
            </div>

            <div class="boletim-grid">
                <?php foreach ($boletim as $index => $disciplina):
                    $calculo = calcularNotaNecessariaIF($disciplina);
                    
                    // Extraindo o nome da disciplina
                    $disciplinaTexto = $disciplina['disciplina'];
                    $partes = explode(' - ', $disciplinaTexto, 2);
                    $codigoDisciplina = isset($partes[0]) ? $partes[0] : $disciplinaTexto;
                    $nomeDisciplina = isset($partes[1]) ? $partes[1] : '';
                    
                    // Determinar status visual
                    $statusClass = 'cursando';
                    $statusIcon = 'fas fa-clock';
                    $statusText = 'Cursando';
                    
                    if ($calculo['ja_aprovado']) {
                        $statusClass = 'aprovado';
                        $statusIcon = 'fas fa-check-circle';
                        $statusText = 'Aprovado';
                    } elseif ($calculo['ja_reprovado']) {
                        $statusClass = 'reprovado';
                        $statusIcon = 'fas fa-times-circle';
                        $statusText = 'Reprovado';
                    } elseif ($calculo['precisa_af']) {
                        $statusClass = 'final';
                        $statusIcon = 'fas fa-exclamation-triangle';
                        $statusText = 'Avaliação Final';
                    }
                    
                    // Frequência
                    $frequencia = isset($disciplina['percentual_carga_horaria_frequentada']) 
                        ? $disciplina['percentual_carga_horaria_frequentada'] 
                        : null;
                    
                    // Informações de faltas
                    $impactoFalta = calcularImpactoFalta($disciplina);
                ?>
                    <div class="disciplina-card <?php echo $statusClass; ?>" id="disciplina-<?php echo $index; ?>">
                        <div class="disciplina-header">
                            <div class="disciplina-info">
                                <div class="disciplina-codigo"><?php echo htmlspecialchars($codigoDisciplina); ?></div>
                                <h3 class="disciplina-nome"><?php echo htmlspecialchars($nomeDisciplina); ?></h3>
                            </div>
                            <div class="disciplina-status">
                                <i class="<?php echo $statusIcon; ?>"></i>
                                <span><?php echo $statusText; ?></span>
                            </div>
                        </div>

                        <div class="notas-container">
                            <div class="nota-item">
                                <div class="nota-label">N1</div>
                                <div class="nota-value">
                                    <?php if ($calculo['n1'] !== null): ?>
                                        <span class="nota-numero"><?php echo number_format($calculo['n1'], 1); ?></span>
                                    <?php else: ?>
                                        <span class="nota-pendente">Aguardando</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="nota-item">
                                <div class="nota-label">N2</div>
                                <div class="nota-value">
                                    <?php if ($calculo['n2'] !== null): ?>
                                        <span class="nota-numero"><?php echo number_format($calculo['n2'], 1); ?></span>
                                    <?php else: ?>
                                        <span class="nota-pendente">Aguardando</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="nota-item media">
                                <div class="nota-label">Média</div>
                                <div class="nota-value">
                                    <?php if ($calculo['media_atual'] !== null): ?>
                                        <span class="nota-numero <?php echo $calculo['media_atual'] >= 60 ? 'aprovado' : ($calculo['media_atual'] >= 20 ? 'final' : 'reprovado'); ?>">
                                            <?php echo number_format($calculo['media_atual'], 1); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="nota-pendente">-</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="disciplina-details">
                            <?php if ($calculo['nota_necessaria'] !== null): ?>
                                <div class="detail-item">
                                    <i class="fas fa-target"></i>
                                    <span>Precisa <strong><?php echo number_format($calculo['nota_necessaria'], 1); ?></strong> no próximo bimestre</span>
                                </div>
                            <?php elseif ($calculo['precisa_af']): ?>
                                <?php $af = calcularAvaliacaoFinal($calculo['n1'], $calculo['n2']); ?>
                                <div class="detail-item">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span>AF: <strong><?php echo number_format($af['naf_necessaria'], 1); ?></strong></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($frequencia !== null): ?>
                                <div class="detail-item frequencia">
                                    <i class="fas fa-user-check"></i>
                                    <span>Frequência: <strong><?php echo number_format($frequencia, 1); ?>%</strong></span>
                                    <div class="freq-bar">
                                        <div class="freq-progress" style="width: <?php echo $frequencia; ?>%"></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($impactoFalta && isset($impactoFalta['faltas_restantes'])): 
                                // Determinar estado das faltas
                                $faltasRestantes = $impactoFalta['faltas_restantes'];
                                $estadoFaltas = 'safe';
                                if ($faltasRestantes <= 0) {
                                    $estadoFaltas = 'critico';
                                } elseif ($faltasRestantes <= 3) {
                                    $estadoFaltas = 'alerta';
                                }
                            ?>
                                <div class="detail-item faltas <?php echo $estadoFaltas; ?>">
                                    <i class="fas fa-calendar-times"></i>
                                    <div class="faltas-info">
                                        <span>Faltas: <strong><?php echo $impactoFalta['faltas_atuais']; ?></strong> de <strong><?php echo $impactoFalta['maximo_faltas']; ?></strong></span>
                                        <span class="faltas-restantes">
                                            <strong><?php echo $impactoFalta['faltas_restantes']; ?></strong> restantes
                                        </span>
                                    </div>
                                    <div class="faltas-bar">
                                        <div class="faltas-progress" 
                                             style="width: <?php echo $impactoFalta['proporcao_faltas']; ?>%"
                                             title="<?php echo $impactoFalta['proporcao_faltas']; ?>% das faltas utilizadas">
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="disciplina-actions">
                            <button class="btn-simular" onclick="abrirSimulador(<?php echo $index; ?>, '<?php echo addslashes($nomeDisciplina); ?>')">
                                <i class="fas fa-calculator"></i>
                                <span>Simular</span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Seção de Horários Completos -->
    <?php if (isset($horarios) && is_array($horarios) && !empty($horarios)): ?>
        <?php criarCardsHorariosSemana($horarios, $boletim); ?>
    <?php endif; ?>

    <!-- Modal do Simulador -->
    <div class="modal fade" id="simuladorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark">
                <div class="modal-header">
                    <h5 class="modal-title">Simulador de Notas</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 id="disciplinaNome" class="text-primary"></h6>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Simular N1:</label>
                            <input type="number" class="form-control bg-dark text-white" id="simN1" min="0" max="100" step="0.1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Simular N2:</label>
                            <input type="number" class="form-control bg-dark text-white" id="simN2" min="0" max="100" step="0.1">
                        </div>
                    </div>

                    <div id="resultadoSimulacao" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" onclick="calcularSimulacao()">Calcular</button>
                </div>
            </div>
        </div>
    </div>

<?php
$pageContent = ob_get_clean(); // Captura o conteúdo do buffer
require_once 'base_dark.php'; // Inclui o template base escuro
?>