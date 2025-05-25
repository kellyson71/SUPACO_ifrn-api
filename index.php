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
require_once 'horarios.php';
require_once 'calendario.php';
require_once 'api_utils.php';
require_once 'status_falta.php';
session_start();

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
 * Calcula a nota mínima necessária para aprovação baseado nas notas existentes
 * 
 * Considera os pesos do sistema de avaliação:
 * - 1º e 2º bimestres: peso 2
 * - 3º e 4º bimestres: peso 3
 * 
 * @param array $notas Array com as notas existentes
 * @param array $pesos Array com os pesos de cada etapa
 * @return float|null Nota necessária ou null se todas as notas já existirem
 */
function calcularNotaNecessaria($notas, $pesos = [2, 2, 3, 3])
{
    $media_minima = 60;
    $soma_pesos = array_sum($pesos);
    $soma_atual = 0;
    $peso_restante = 0;

    // Calcula a soma das notas existentes com seus respectivos pesos
    for ($i = 0; $i < 4; $i++) {
        $nota = isset($notas["nota_etapa_" . ($i + 1)]['nota']) ? $notas["nota_etapa_" . ($i + 1)]['nota'] : null;
        if ($nota !== null) {
            $soma_atual += $nota * $pesos[$i];
        } else {
            $peso_restante += $pesos[$i];
        }
    }

    // Se não houver notas faltantes, retorna null
    if ($peso_restante == 0) return null;

    // Calcula a nota necessária
    $pontos_necessarios = ($media_minima * $soma_pesos) - $soma_atual;
    $nota_necessaria = $pontos_necessarios / $peso_restante;

    return max(min($nota_necessaria, 100), 0);
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
    // No SUAP: 2=Segunda, 3=Terça, 4=Quarta, 5=Quinta, 6=Sexta
    // Não precisamos mais converter, pois já estamos usando o mesmo padrão

    $aulas = [];

    foreach ($horarios as $disciplina) {
        if (!empty($disciplina['horarios_de_aula'])) {
            $horariosArray = parseHorario($disciplina['horarios_de_aula']);
            foreach ($horariosArray as $h) {
                if ($h['dia'] == $dia) {
                    $aulas[] = [
                        'sigla' => $disciplina['sigla'] ?? '',
                        'disciplina' => $disciplina['sigla'] ?? '',
                        'descricao' => $disciplina['descricao'] ?? '',
                        'locais' => isset($disciplina['locais_de_aula']) ? $disciplina['locais_de_aula'] : [],
                        'horario' => $h
                    ];
                }
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
?>

<div class="dashboard-container py-4">
    <div class="container">
        <!-- Cabeçalho do dashboard -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-header d-flex justify-content-between align-items-center flex-wrap">
                    <h1 class="h3 mb-3 mb-md-0">
                        <i class="fas fa-tachometer-alt me-2 text-primary"></i>
                        Dashboard Acadêmico
                    </h1>
                    <div class="btn-group">
                        <a href="index.php" class="btn btn-outline-primary active">
                            <i class="fas fa-home"></i> Início
                        </a>
                        <a href="?view=boletim" class="btn btn-outline-primary">
                            <i class="fas fa-chart-line"></i> Boletim
                        </a>
                        <a href="?view=horarios" class="btn btn-outline-primary">
                            <i class="fas fa-calendar-week"></i> Horários
                        </a>
                    </div>
                </div>
            </div>
        </div> <!-- Hero Section Moderno Unificado - Redesenhado -->
        <div class="card border-0 mb-4 overflow-hidden animate-fade-in-up hero-card">
            <div class="hero-wrapper position-relative"> <!-- Background com gradiente e padrão aprimorado -->
                <div class="hero-bg position-absolute w-100 h-100"
                    style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
                    background-image: url('assets/pattern.png');
                    background-size: cover;
                    opacity: 0.92;">
                </div>

                <!-- Overlay para melhorar o contraste -->
                <div class="hero-overlay position-absolute w-100 h-100"
                    style="background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0) 100%);
                    z-index: 2;">
                </div>

                <!-- Conteúdo do Hero -->
                <div class="card-body position-relative py-4 text-white">
                    <div class="row align-items-center">
                        <!-- Coluna do Status Diário -->
                        <div class="col-lg-3 text-center mb-4 mb-lg-0"> <?php
                                                                        // Determinar o status geral do dia
                                                                        $statusDoDia = 'success'; // Padrão: pode faltar
                                                                        $totalAulas = 0;
                                                                        $aulasCriticas = 0;
                                                                        $aulasAlerta = 0;                            // Busca aulas de hoje especificamente para o card de status
                                                                        $aulasDeHoje = isset($horarios) ? getAulasHoje($horarios) : [];
                                                                        $diaAtual = intval(date('N')); // 1 (Segunda) a 7 (Domingo)
                                                                        $mostraDiaAlternativo = false;
                                                                        $nomeDiaAulas = $diasSemana[$diaAtual];
                                                                        $dataAulas = new DateTime();

                                                                        // Para garantir que sempre tenhamos algo para mostrar mesmo se não houver aulas hoje
                                                                        if (empty($aulasDeHoje) && isset($horarios) && !empty($horarios)) {
                                                                            $mostraDiaAlternativo = true;

                                                                            if ($diaAtual >= 6) {
                                                                                // É fim de semana, mostra aulas da próxima segunda
                                                                                $proximaSegunda = 2; // Segunda = 2 no formato SUAP
                                                                                $aulasDeHoje = getAulasDoDia($horarios, $proximaSegunda);
                                                                                $diasParaSegunda = $diaAtual == 7 ? 1 : 8 - $diaAtual;
                                                                                $dataAulas = new DateTime();
                                                                                $dataAulas->modify("+{$diasParaSegunda} days");
                                                                                $nomeDiaAulas = "Segunda-feira";
                                                                            } else {
                                                                                // É dia de semana mas sem aulas, mostra o próximo dia com aulas
                                                                                $diaEncontrado = false;
                                                                                $diasAFente = 0;

                                                                                // Procura aulas nos próximos dias
                                                                                for ($i = 1; $i < 7; $i++) {
                                                                                    $diaTeste = ($diaAtual + $i) % 7;
                                                                                    if ($diaTeste == 0) $diaTeste = 7;

                                                                                    // Converte para formato SUAP (2-6)
                                                                                    $diaTeste = ($diaTeste == 7) ? 0 : $diaTeste + 1;

                                                                                    if ($diaTeste >= 2 && $diaTeste <= 6) { // Formato SUAP: dias úteis
                                                                                        $aulasTest = getAulasDoDia($horarios, $diaTeste);
                                                                                        if (!empty($aulasTest)) {
                                                                                            $aulasDeHoje = $aulasTest;
                                                                                            $diasAFente = $i;
                                                                                            $dataAulas = new DateTime();
                                                                                            $dataAulas->modify("+{$diasAFente} days");
                                                                                            $nomeDiaAulas = $diasSemana[($diaAtual + $i - 1) % 7 + 1];
                                                                                            $diaEncontrado = true;
                                                                                            break;
                                                                                        }
                                                                                    }
                                                                                }

                                                                                // Se não encontrou nos próximos dias, procura no ciclo semanal
                                                                                if (!$diaEncontrado) {
                                                                                    for ($dia = 2; $dia <= 6; $dia++) {
                                                                                        $aulasDeHoje = getAulasDoDia($horarios, $dia);
                                                                                        if (!empty($aulasDeHoje)) {
                                                                                            // Converte de formato SUAP para formato ISO (1-7)
                                                                                            $diaIso = $dia - 1;
                                                                                            // Cálculo de quantos dias a frente
                                                                                            $diasAFente = ($diaIso > $diaAtual) ?
                                                                                                ($diaIso - $diaAtual) : (7 - $diaAtual + $diaIso);
                                                                                            $dataAulas = new DateTime();
                                                                                            $dataAulas->modify("+{$diasAFente} days");
                                                                                            $nomeDiaAulas = $diasSemana[$diaIso];
                                                                                            break;
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        }

                                                                        $aulasHojeOrdenadas = ordenarAulasPorHorario($aulasDeHoje);

                                                                        if (!empty($aulasHojeOrdenadas)) {
                                                                            foreach ($aulasHojeOrdenadas as $aula) {
                                                                                $totalAulas++;

                                                                                // Encontra a disciplina correspondente no boletim
                                                                                $statusAula = 'success';
                                                                                if (isset($boletim)) {
                                                                                    foreach ($boletim as $item) {
                                                                                        if (isset($aula['sigla']) && strpos($item['disciplina'], $aula['sigla']) !== false) {
                                                                                            $statusAula = podeFaltarAmanha($item);
                                                                                            break;
                                                                                        }
                                                                                    }
                                                                                }

                                                                                if ($statusAula === 'danger') $aulasCriticas++;
                                                                                if ($statusAula === 'warning') $aulasAlerta++;
                                                                            }

                                                                            // Determina o status geral do dia
                                                                            if ($aulasCriticas > 0) {
                                                                                $statusDoDia = 'danger';
                                                                            } else if ($aulasAlerta > 0) {
                                                                                $statusDoDia = 'warning';
                                                                            }
                                                                        }

                                                                        $statusInfo = getStatusFaltaImagem($statusDoDia);
                                                                        ?> <div class="status-diario-container">
                                <div class="status-diario-card-modern">
                                    <!-- Parte superior com título -->
                                    <div class="status-card-header">
                                        <h5 class="fw-bold mb-0">
                                            <i class="fas fa-clipboard-check me-2 text-<?php echo $statusDoDia; ?>"></i>
                                            Status do Dia
                                        </h5>
                                        <div class="date-indicator">
                                            <i class="fas fa-calendar-day me-1"></i>
                                            <?php
                                            setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');
                                            $diasemana = array('Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado');
                                            $data = date('Y-m-d');
                                            $diasemana_numero = date('w', strtotime($data));
                                            echo $diasemana[$diasemana_numero] . ', ' . date('d/m/Y');
                                            ?>
                                        </div>
                                    </div>

                                    <!-- Parte central com a imagem -->
                                    <div class="status-card-body">
                                        <div class="status-image-wrapper">
                                            <div class="status-bg-circle status-bg-<?php echo $statusDoDia; ?>"></div>
                                            <img src="<?php echo $statusInfo['imagem']; ?>" alt="Status Diário"
                                                class="status-icon status-pulse-<?php echo $statusDoDia; ?>">
                                        </div>
                                        <h3 class="status-title"><?php echo $statusInfo['descricao']; ?></h3>
                                        <div class="status-detail">
                                            <?php if ($totalAulas > 0): ?> <div class="status-count">
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <span class="status-badge status-badge-<?php echo $statusDoDia; ?>"><?php echo $totalAulas; ?></span>
                                                        <span class="status-label">aulas hoje</span>
                                                    </div>

                                                    <?php if ($aulasCriticas > 0): ?>
                                                        <div class="status-alert status-alert-danger">
                                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                                            <?php echo $aulasCriticas; ?> aula(s) crítica(s)
                                                        </div>
                                                    <?php elseif ($aulasAlerta > 0): ?>
                                                        <div class="status-alert status-alert-warning">
                                                            <i class="fas fa-exclamation-circle me-2"></i>
                                                            <?php echo $aulasAlerta; ?> aula(s) com alerta
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="status-alert status-alert-success">
                                                            <i class="fas fa-check-circle me-2"></i>
                                                            Todas as aulas tranquilas
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Detalhes explicativos sobre o status -->
                                                <div class="status-info-detail">
                                                    <?php if ($aulasCriticas > 0): ?>
                                                        Algumas disciplinas já atingiram ou estão próximas do limite de faltas.
                                                    <?php elseif ($aulasAlerta > 0): ?>
                                                        Esteja atento às faltas em algumas disciplinas hoje.
                                                    <?php else: ?>
                                                        <?php echo $statusInfo['detalhes'] ?? 'Você ainda tem faltas disponíveis nas disciplinas de hoje.'; ?>
                                                    <?php endif; ?>
                                                </div> <?php if (!$mostraDiaAlternativo): ?>
                                                    <!-- Mostra nota normal quando são aulas de hoje -->
                                                    <div class="status-today-note mt-3">
                                                        <i class="fas fa-calendar-day me-1"></i> Estas são suas aulas de hoje
                                                        <span class="hoje-badge">HOJE</span>
                                                    </div>
                                                <?php else: ?>
                                                    <!-- Mostra nota indicando que são aulas de outro dia -->
                                                    <div class="status-today-note mt-3">
                                                        <i class="fas fa-calendar-alt me-1"></i> Exibindo aulas de
                                                        <strong class="text-primary"><?php echo $nomeDiaAulas; ?> (<?php echo $dataAulas->format('d/m'); ?>)</strong>
                                                    </div>
                                                    <div class="status-alternate-day-info">
                                                        <?php if (intval(date('N')) >= 6): ?>
                                                            <i class="fas fa-info-circle me-1"></i> Sem aulas hoje (fim de semana)
                                                        <?php elseif (verificarFeriado(new DateTime())): ?>
                                                            <i class="fas fa-info-circle me-1"></i> Hoje é feriado
                                                        <?php else: ?>
                                                            <i class="fas fa-info-circle me-1"></i> Sem aulas programadas para hoje
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>


                                            <?php else: ?> <div class="status-count">
                                                    <div class="status-alert status-alert-info">
                                                        <i class="fas fa-info-circle me-2 fa-pulse"></i>
                                                        Sem aulas hoje
                                                    </div>
                                                </div>
                                                <div class="status-info-detail">
                                                    <?php
                                                    $diaAtual = intval(date('N'));
                                                    if ($diaAtual >= 6): // É fim de semana
                                                    ?>
                                                        <strong>Hoje é <?php echo $diasSemana[$diaAtual]; ?>.</strong> Aproveite o final de semana!
                                                    <?php elseif (verificarFeriado(new DateTime())): // É feriado
                                                        $nomeFeriado = verificarFeriado(new DateTime());
                                                    ?>
                                                        <strong>Hoje é feriado:</strong> <?php echo $nomeFeriado; ?>. Aproveite o descanso!
                                                    <?php else: ?>
                                                        <strong>Dia sem aulas programadas.</strong> Aproveite para estudar ou descansar!
                                                    <?php endif; ?>
                                                </div>

                                                <div class="status-today-note mt-3">
                                                    <i class="fas fa-calendar-alt me-1"></i> Próximo dia de aulas:
                                                    <strong class="text-primary">
                                                        <?php
                                                        $proxDia = $diaAtual;
                                                        // Encontra o próximo dia útil
                                                        while (++$proxDia % 7 > 5) {
                                                        }
                                                        $proxDiaData = new DateTime();
                                                        $diasAvancar = ($proxDia % 7) - $diaAtual;
                                                        if ($diasAvancar <= 0) $diasAvancar += 7;
                                                        $proxDiaData->modify("+{$diasAvancar} days");
                                                        echo $diasSemana[$proxDia % 7 ?: 7] . ' (' . $proxDiaData->format('d/m') . ')';
                                                        ?>
                                                    </strong>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <!-- Rodapé com ícone -->
                                    <div class="status-card-footer">
                                        <i class="fas <?php echo $statusInfo['icone']; ?> status-footer-icon status-icon-<?php echo $statusDoDia; ?>"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Coluna das Informações do Usuário -->
                        <div class="col-lg-6">
                            <div class="user-profile-container bg-white bg-opacity-10 backdrop-blur-sm rounded-4 p-4">
                                <div class="d-flex align-items-center flex-column flex-md-row">
                                    <?php if (isset($meusDados['url_foto_150x200'])): ?>
                                        <img src="<?php echo htmlspecialchars($meusDados['url_foto_150x200']); ?>"
                                            alt="Foto de perfil"
                                            class="rounded-circle border border-3 border-white shadow me-3"
                                            style="width: 80px; height: 80px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center shadow me-3"
                                            style="width: 80px; height: 80px;">
                                            <i class="fas fa-user-graduate fa-2x"></i>
                                        </div>
                                    <?php endif; ?> <div class="ms-md-3 text-center text-md-start hero-text-container">
                                        <h2 class="h3 mb-0 fw-bold text-white">
                                            <?php echo isset($meusDados['nome_usual']) ? htmlspecialchars($meusDados['nome_usual']) : 'Estudante'; ?>
                                        </h2>
                                        <div class="d-flex flex-wrap mt-2 gap-2 justify-content-center justify-content-md-start">
                                            <div class="badge bg-success fs-7 px-3 py-2">
                                                <i class="fas fa-check-circle me-1"></i>
                                                <?php echo isset($meusDados['vinculo']['situacao']) ? htmlspecialchars($meusDados['vinculo']['situacao']) : 'Estudante Ativo'; ?>
                                            </div>
                                            <div class="badge bg-white text-primary fs-7 px-3 py-2">
                                                <i class="fas fa-id-card me-1"></i>
                                                <?php echo isset($meusDados['matricula']) ? htmlspecialchars($meusDados['matricula']) : ''; ?>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-wrap mt-2 gap-2 justify-content-center justify-content-md-start">
                                            <?php if (isset($meusDados['vinculo']['curso'])): ?>
                                                <div class="badge bg-white text-primary fs-7 px-3 py-2">
                                                    <i class="fas fa-graduation-cap me-1"></i>
                                                    <?php echo htmlspecialchars($meusDados['vinculo']['curso']); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="badge bg-white text-primary fs-7 px-3 py-2">
                                                <i class="fas fa-school me-1"></i>
                                                <?php echo isset($meusDados['vinculo']['campus']) ? htmlspecialchars($meusDados['vinculo']['campus']) : 'Campus'; ?>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <div class="badge bg-white bg-opacity-90 text-primary py-2 px-3 fs-7">
                                                <i class="fas fa-calendar-alt me-2"></i>
                                                <span class="fw-bold">Período <?php echo $anoLetivo; ?>.<?php echo $periodoLetivo; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Coluna do Logo e Botões -->
                        <div class="col-lg-3 mt-4 mt-lg-0">
                            <div class="d-flex flex-column justify-content-between h-100">
                                <div class="text-center text-lg-end mb-4">
                                    <div class="d-inline-block bg-white rounded-4 p-3 shadow-lg mb-3 border border-2 border-white">
                                        <img src="assets/logo.png"
                                            alt="SUPACO Logo"
                                            class="img-fluid rounded-3"
                                            style="width: 80px; height: 80px; object-fit: cover;">
                                    </div>
                                    <h2 class="h3 text-white mb-0 hero-text-container d-inline-block px-3 py-2">
                                        SUPACO <span class="badge bg-white text-primary fs-6 align-middle ms-1">Beta</span>
                                    </h2>
                                    <p class="text-white mb-3 bg-dark bg-opacity-50 d-inline-block py-1 px-2 rounded">
                                        <small>Sistema Útil Pra Aluno Cansado e Ocupado</small>
                                    </p>
                                </div>

                                <div class="d-flex flex-row flex-lg-column justify-content-center gap-2 mb-3">
                                    <a href="index.php" class="btn btn-light">
                                        <i class="fas fa-home me-2"></i> Dashboard
                                    </a>
                                    <a href="logout.php" class="btn btn-outline-light">
                                        <i class="fas fa-sign-out-alt me-2"></i> Sair
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- Métricas e estatísticas do usuário -->
                <div class="card-footer py-3 border-top border-white border-opacity-25 text-white" style="background-color: rgba(3, 37, 76, 0.9); position: relative; z-index: 5;">
                    <div class="row">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-white bg-opacity-25 p-2 rounded-circle me-3 stats-icon-pulse">
                                    <i class="fas fa-graduation-cap text-white"></i>
                                </div>
                                <div class="stats-text-container">
                                    <h6 class="mb-0 fw-bold"><?php echo isset($boletim) ? count($boletim) : '0'; ?></h6>
                                    <small class="text-white-50">Disciplinas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-white bg-opacity-25 p-2 rounded-circle me-3 stats-icon-pulse">
                                    <i class="fas fa-calendar-check text-white"></i>
                                </div>
                                <div class="stats-text-container">
                                    <h6 class="mb-0 fw-bold">
                                        <?php
                                        // Agora usamos nossa variável específica para o card de status
                                        echo $totalAulas;
                                        ?>
                                    </h6>
                                    <small class="text-white-50">Aulas Hoje</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-white bg-opacity-25 p-2 rounded-circle me-3 stats-icon-pulse">
                                    <i class="fas fa-clock text-white"></i>
                                </div>
                                <div class="stats-text-container">
                                    <h6 class="mb-0 fw-bold"><?php echo date('H:i'); ?></h6>
                                    <small class="text-white-50">Atualização</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container mt-4"> <!-- Seção de Aulas -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-calendar-day me-2"></i>
                                    Aulas por Dia - <?php echo $nomeDia; ?>
                                </h5>
                                <div class="btn-group" role="group">
                                    <a href="?dia=hoje" class="btn btn-sm <?php echo $mostrarDia === 'hoje' ? 'btn-light' : 'btn-outline-light'; ?>">
                                        Hoje
                                    </a>
                                    <a href="?dia=amanha" class="btn btn-sm <?php echo $mostrarDia === 'amanha' ? 'btn-light' : 'btn-outline-light'; ?>">
                                        Amanhã
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <?php
                            // Seleciona os horários baseado no dia selecionado
                            $aulasAExibir = [];

                            if ($mostrarDia === 'hoje') {
                                $aulasAExibir = isset($horarios) ? getAulasHoje($horarios) : [];
                            } else if ($mostrarDia === 'data' && $dataExibicao) {
                                $aulasAExibir = isset($horarios) ? getAulasDeData($horarios, $dataExibicao) : [];
                            } else {
                                // Padrão: amanhã
                                $aulasAExibir = isset($horarios) ? getAulasAmanha($horarios) : [];
                            }

                            // Ordena as aulas por horário
                            $aulasOrdenadas = ordenarAulasPorHorario($aulasAExibir);

                            // Se não há aulas no dia selecionado
                            if (empty($aulasOrdenadas)):
                            ?>
                                <div class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="fas fa-calendar-times fa-4x text-muted"></i>
                                    </div>
                                    <h4>Nenhuma aula encontrada</h4>
                                    <p class="text-muted">
                                        <?php if ($mostrarDia === 'hoje' && (int)date('N') > 5): ?>
                                            Hoje é final de semana. Aproveite para descansar!
                                        <?php elseif ($mostrarDia === 'amanha' && $amanha == 1): ?>
                                            Amanhã é segunda-feira. Prepare-se para a semana! <?php elseif ($mostrarDia === 'data' && isset($dataExibicao)): ?>
                                            <?php $feriadoNome = verificarFeriado($dataExibicao); ?>
                                            <?php if ($feriadoNome): ?>
                                                É feriado de <?php echo htmlspecialchars($feriadoNome); ?>. Aproveite!
                                            <?php else: ?>
                                                Não há aulas programadas para este dia.
                                            <?php endif; ?>
                                        <?php else: ?>
                                            Não há aulas programadas para este dia.
                                        <?php endif; ?>
                                    </p>
                                </div> <?php else: ?> <!-- Lista de aulas do dia com indicadores visuais unificados -->
                                <div class="aulas-list">
                                    <?php
                                        // Usar a variável de status do dia que já definimos no hero section
                                        foreach ($aulasOrdenadas as $aula):
                                            // Encontra a disciplina correspondente no boletim para calcular frequência
                                            $disciplinaBoletim = null;
                                            $impactoFalta = null;
                                            $podeFaltar = 'danger';

                                            if (isset($boletim)) {
                                                foreach ($boletim as $item) {
                                                    if (isset($aula['sigla']) && strpos($item['disciplina'], $aula['sigla']) !== false) {
                                                        $disciplinaBoletim = $item;
                                                        $impactoFalta = calcularImpactoFalta($item);
                                                        $podeFaltar = podeFaltarAmanha($item);
                                                        break;
                                                    }
                                                }
                                            }
                                    ?> <?php
                                            // Obter informações da imagem de status para a aula
                                            $statusInfo = getStatusFaltaImagem($podeFaltar);
                                        ?> <div class="aula-item mb-3 border rounded border-start border-4 border-<?php echo $podeFaltar; ?> bg-white">
                                            <div class="p-3">
                                                <div class="d-flex align-items-center">
                                                    <!-- Status e imagem -->
                                                    <div class="me-3">
                                                        <img src="<?php echo $statusInfo['imagem']; ?>" alt="Status de falta"
                                                            class="rounded-circle" style="width: 45px; height: 45px; object-fit: cover;">
                                                    </div>

                                                    <!-- Informações principais -->
                                                    <div class="flex-grow-1 me-3">
                                                        <h6 class="mb-1 fw-bold"><?php echo isset($aula['descricao']) ? htmlspecialchars($aula['descricao']) : htmlspecialchars($aula['disciplina'] ?? 'Aula'); ?></h6>
                                                        <div class="d-flex flex-wrap gap-2 align-items-center text-muted">
                                                            <small class="me-2">
                                                                <i class="fas fa-clock me-1"></i>
                                                                <?php echo $aula['horario']['turno']; ?><?php echo implode(',', $aula['horario']['aulas']); ?>
                                                            </small>
                                                            <small>
                                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                                <?php
                                                                if (isset($aula['locais']) && is_array($aula['locais']) && !empty($aula['locais'])) {
                                                                    echo htmlspecialchars(implode(', ', $aula['locais']));
                                                                } else if (isset($aula['local']) && !empty($aula['local'])) {
                                                                    echo htmlspecialchars($aula['local']);
                                                                } else {
                                                                    echo 'Local não definido';
                                                                }
                                                                ?>
                                                            </small>
                                                        </div>
                                                    </div>

                                                    <!-- Status badge -->
                                                    <div>
                                                        <span class="badge bg-<?php echo $podeFaltar; ?> text-white">
                                                            <?php if ($podeFaltar === 'success'): ?>
                                                                <i class="fas fa-check me-1"></i> Pode faltar
                                                            <?php elseif ($podeFaltar === 'warning'): ?>
                                                                <i class="fas fa-exclamation me-1"></i> Cuidado
                                                            <?php else: ?>
                                                                <i class="fas fa-times me-1"></i> Evite faltar
                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                </div>

                                                <!-- Informações de faltas -->
                                                <?php if ($impactoFalta && isset($impactoFalta['faltas_restantes'])): ?>
                                                    <div class="mt-3 pt-3 border-top border-light">
                                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                            <small class="text-muted">
                                                                <i class="fas fa-user-times me-1"></i>
                                                                Faltas: <strong><?php echo $impactoFalta['faltas_atuais']; ?></strong> de <strong><?php echo $impactoFalta['maximo_faltas']; ?></strong>
                                                            </small>
                                                            <small class="badge bg-<?php echo $podeFaltar; ?> bg-opacity-10 text-<?php echo $podeFaltar; ?>">
                                                                <strong><?php echo $impactoFalta['faltas_restantes']; ?></strong> restantes
                                                            </small>
                                                        </div>
                                                        <div class="progress mt-2" style="height: 6px;">
                                                            <div class="progress-bar bg-<?php echo $podeFaltar; ?>"
                                                                role="progressbar"
                                                                style="width: <?php echo $impactoFalta['proporcao_faltas']; ?>%"
                                                                title="<?php echo $impactoFalta['proporcao_faltas']; ?>% das faltas utilizadas">
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div><?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Coluna lateral -->
                <div class="col-md-4 mt-4 mt-md-0">
                    <!-- Widget de disciplinas críticas -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-danger text-white">
                            <h5 class="m-0"><i class="fas fa-exclamation-triangle me-2"></i> Disciplinas críticas</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $disciplinasCriticas = [];
                            if (isset($boletim)) {
                                foreach ($boletim as $disciplina) {
                                    if (
                                        $disciplina['percentual_carga_horaria_frequentada'] < 85 ||
                                        ($disciplina['media_disciplina'] !== null && $disciplina['media_disciplina'] < 60)
                                    ) {
                                        $disciplinasCriticas[] = $disciplina;
                                    }
                                }
                            }

                            if (empty($disciplinasCriticas)):
                            ?>
                                <div class="text-center py-3">
                                    <div class="mb-2">
                                        <i class="fas fa-check-circle fa-3x text-success"></i>
                                    </div>
                                    <p class="mb-0">Nenhuma disciplina em situação crítica.</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($disciplinasCriticas as $disciplina): ?>
                                        <div class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?php echo htmlspecialchars(preg_replace('/^.*? - /', '', $disciplina['disciplina'])); ?></h6>
                                                <small>
                                                    <?php if ($disciplina['percentual_carga_horaria_frequentada'] < 85): ?>
                                                        <span class="badge bg-danger">Frequência baixa</span>
                                                    <?php endif; ?>
                                                    <?php if ($disciplina['media_disciplina'] !== null && $disciplina['media_disciplina'] < 60): ?>
                                                        <span class="badge bg-warning text-dark">Nota baixa</span>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                            <div class="small mt-1">
                                                <?php if ($disciplina['percentual_carga_horaria_frequentada'] < 85): ?>
                                                    <div class="mb-1">
                                                        <span class="text-muted">Frequência: </span>
                                                        <span class="text-danger"><?php echo number_format($disciplina['percentual_carga_horaria_frequentada'], 1); ?>%</span>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($disciplina['media_disciplina'] !== null): ?>
                                                    <div>
                                                        <span class="text-muted">Média atual: </span>
                                                        <span class="<?php echo $disciplina['media_disciplina'] < 60 ? 'text-danger' : 'text-success'; ?>">
                                                            <?php echo number_format($disciplina['media_disciplina'], 1); ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Widget de próximos feriados -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h5 class="m-0"><i class="fas fa-calendar-alt me-2"></i> Próximos feriados</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $proximosFeriados = [];                            // Implementação para mostrar os próximos feriados
                            $dataAtual = new DateTime();
                            $anoAtual = (int)$dataAtual->format('Y');

                            // Obter feriados do ano atual e do próximo
                            $feriadosAnoAtual = listarTodosFeriados($anoAtual);
                            $feriadosProximoAno = listarTodosFeriados($anoAtual + 1);
                            $todosFeriados = array_merge($feriadosAnoAtual, $feriadosProximoAno);

                            $proximosFeriados = [];

                            // Filtra apenas os feriados futuros
                            foreach ($todosFeriados as $dataStr => $nome) {
                                $dataFeriado = new DateTime($dataStr);
                                if ($dataFeriado >= $dataAtual) {
                                    $proximosFeriados[] = [
                                        'nome' => $nome,
                                        'data' => $dataFeriado
                                    ];
                                }
                            }

                            // Ordenar por data (mais próximo primeiro)
                            usort($proximosFeriados, function ($a, $b) {
                                return $a['data'] <=> $b['data'];
                            });

                            // Limitar a 3 feriados
                            $proximosFeriados = array_slice($proximosFeriados, 0, 3);

                            if (empty($proximosFeriados)):
                            ?>
                                <div class="text-center py-3">
                                    <p class="mb-0">Nenhum feriado próximo encontrado.</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($proximosFeriados as $feriado): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($feriado['nome']); ?></div>
                                                <small class="text-muted">
                                                    <?php echo $feriado['data']->format('d/m/Y'); ?> (<?php echo $diasSemana[(int)$feriado['data']->format('N')]; ?>)
                                                </small>
                                            </div>
                                            <span class="badge bg-primary rounded-pill">
                                                <?php
                                                $hoje = new DateTime();
                                                $diff = $hoje->diff($feriado['data'])->days;
                                                echo $diff == 0 ? 'Hoje' : ($diff == 1 ? 'Amanhã' : "Em $diff dias");
                                                ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção Resumo de Disciplinas -->
            <div class="row mb-4 animate-fade-in-up" style="animation-delay: 0.4s">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-graduation-cap me-2"></i>
                                    Resumo de Disciplinas
                                </h5>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-light" data-bs-toggle="tooltip" title="Exportar para PDF">
                                        <i class="fas fa-file-pdf me-1"></i> Exportar
                                    </button>
                                    <button class="btn btn-sm btn-outline-light" id="btnGraficoDesempenho" data-bs-toggle="tooltip" title="Visualizar gráficos">
                                        <i class="fas fa-chart-bar me-1"></i> Gráficos
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (isset($boletim) && is_array($boletim) && !empty($boletim)): ?>
                                <div class="table-responsive">
                                    <table class="table table-boletim mb-0">
                                        <thead>
                                            <tr>
                                                <th style="min-width: 200px">Disciplina</th>
                                                <th class="text-center">Nota 1</th>
                                                <th class="text-center">Nota 2</th>
                                                <th class="text-center">Nota 3</th>
                                                <th class="text-center">Nota 4</th>
                                                <th class="text-center">Média</th>
                                                <th class="text-center">Freq. (%)</th>
                                                <th class="text-center">Situação</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($boletim as $disciplina): ?>
                                                <?php
                                                // Extraindo o nome da disciplina
                                                $disciplinaTexto = $disciplina['disciplina'];
                                                $partes = explode(' - ', $disciplinaTexto, 2);
                                                $codigoDisciplina = isset($partes[0]) ? $partes[0] : $disciplinaTexto;
                                                $nomeDisciplina = isset($partes[1]) ? $partes[1] : '';
                                                ?>
                                                <tr class="disciplina-item">
                                                    <td>
                                                        <span class="disciplina-codigo"><?php echo htmlspecialchars($codigoDisciplina); ?></span>
                                                        <div class="disciplina-nome"><?php echo htmlspecialchars($nomeDisciplina); ?></div>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if (isset($disciplina['nota_etapa_1']['nota'])): ?>
                                                            <span class="nota-valor <?php echo $disciplina['nota_etapa_1']['nota'] >= 60 ? 'text-success' : 'text-danger'; ?>">
                                                                <?php echo $disciplina['nota_etapa_1']['nota']; ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if (isset($disciplina['nota_etapa_2']['nota'])): ?>
                                                            <span class="nota-valor <?php echo $disciplina['nota_etapa_2']['nota'] >= 60 ? 'text-success' : 'text-danger'; ?>">
                                                                <?php echo $disciplina['nota_etapa_2']['nota']; ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if (isset($disciplina['nota_etapa_3']['nota'])): ?>
                                                            <span class="nota-valor <?php echo $disciplina['nota_etapa_3']['nota'] >= 60 ? 'text-success' : 'text-danger'; ?>">
                                                                <?php echo $disciplina['nota_etapa_3']['nota']; ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if (isset($disciplina['nota_etapa_4']['nota'])): ?>
                                                            <span class="nota-valor <?php echo $disciplina['nota_etapa_4']['nota'] >= 60 ? 'text-success' : 'text-danger'; ?>">
                                                                <?php echo $disciplina['nota_etapa_4']['nota']; ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if (isset($disciplina['media_final_disciplina'])): ?>
                                                            <span class="nota-valor <?php echo $disciplina['media_final_disciplina'] >= 60 ? 'text-success' : 'text-danger'; ?>">
                                                                <?php echo $disciplina['media_final_disciplina']; ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php
                                                        $frequencia = isset($disciplina['percentual_carga_horaria_frequentada'])
                                                            ? number_format($disciplina['percentual_carga_horaria_frequentada'], 1)
                                                            : '-';

                                                        if ($frequencia != '-') {
                                                            $freqClass = 'text-success';
                                                            $freqIcon = 'check-circle';

                                                            if ($frequencia < 75) {
                                                                $freqClass = 'text-danger';
                                                                $freqIcon = 'exclamation-circle';
                                                            } elseif ($frequencia < 80) {
                                                                $freqClass = 'text-warning';
                                                                $freqIcon = 'exclamation-triangle';
                                                            }

                                                            echo "<span class='{$freqClass}'>{$frequencia}% <i class='fas fa-{$freqIcon} ms-1'></i></span>";
                                                        } else {
                                                            echo "<span class='text-muted'>-</span>";
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php
                                                        $situacao = $disciplina['situacao'] ?? '';
                                                        $situacaoClass = '';
                                                        $situacaoIcon = '';

                                                        if (stripos($situacao, 'APROVADO') !== false) {
                                                            $situacaoClass = 'situacao-aprovado';
                                                            $situacaoIcon = 'check-circle';
                                                        } elseif (stripos($situacao, 'REPROVADO') !== false) {
                                                            $situacaoClass = 'situacao-reprovado';
                                                            $situacaoIcon = 'times-circle';
                                                        } else {
                                                            $situacaoClass = 'situacao-cursando';
                                                            $situacaoIcon = 'clock';
                                                        }
                                                        ?>
                                                        <div class="<?php echo $situacaoClass; ?>">
                                                            <span class="status-indicador <?php
                                                                                            if ($situacaoClass == 'situacao-aprovado') echo 'status-verde';
                                                                                            else if ($situacaoClass == 'situacao-reprovado') echo 'status-vermelho';
                                                                                            else echo 'status-amarelo';
                                                                                            ?>"></span>
                                                            <span><?php echo htmlspecialchars($situacao); ?></span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    Não foi possível carregar o boletim.
                                    <?php if (isset($meusDados['tipo_vinculo'])): ?>
                                        <br>Tipo de vínculo: <?php echo htmlspecialchars($meusDados['tipo_vinculo']); ?>
                                    <?php endif; ?>
                                    <br>Por favor, verifique se você está matriculado no período atual.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção do Horário -->
            <div class="animate-fade-in-up" style="animation-delay: 0.6s">
                <h3 class="mt-5 mb-4">Horário de Aulas</h3>

                <?php if (isset($horarios) && is_array($horarios)): ?>
                    <?php mostrarHorarios($horarios); ?>
                <?php else: ?>
                    <div class="alert alert-warning mt-4">
                        Não foi possível carregar o horário das aulas.
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Adicione este script antes do fechamento do body --> <!-- Script simplificado -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Calculadora de notas simplificada
                document.querySelectorAll('.nota-input').forEach(input => {
                    input.addEventListener('input', function() {
                        const disciplina = this.dataset.disciplina;
                        const valor = parseFloat(this.value) || 0;

                        // Calcular nota necessária para aprovação
                        const notasSimuladas = Array.from(document.querySelectorAll(`.nota-input[data-disciplina="${disciplina}"]`))
                            .map(input => ({
                                etapa: parseInt(input.dataset.etapa),
                                valor: parseFloat(input.value) || 0,
                                peso: input.dataset.etapa <= 2 ? 2 : 3
                            }));

                        let somaNotas = 0;
                        let somaPesos = 0;

                        notasSimuladas.forEach(nota => {
                            if (nota.valor > 0) {
                                somaNotas += nota.valor * nota.peso;
                                somaPesos += nota.peso;
                            }
                        });

                        if (somaPesos > 0) {
                            const pontosNecessarios = (60 * 10) - somaNotas;
                            const pesosRestantes = 10 - somaPesos;

                            if (pesosRestantes > 0) {
                                const notaNecessaria = Math.min(Math.max(pontosNecessarios / pesosRestantes, 0), 100);

                                document.querySelectorAll(`.nota-input[data-disciplina="${disciplina}"]`).forEach(input => {
                                    if (!input.value) {
                                        input.placeholder = notaNecessaria.toFixed(1);
                                    }
                                });
                            }
                        }
                    });
                });

                // Botões do dashboard
                document.getElementById('refreshDashboardBtn')?.addEventListener('click', function() {
                    const loadingBar = document.getElementById('loadingBar');
                    if (loadingBar) loadingBar.style.display = 'block';
                    setTimeout(() => window.location.reload(), 500);
                });

                document.getElementById('filterDashboardBtn')?.addEventListener('click', function() {
                    alert('Função de filtro será disponibilizada em breve!');
                });

                document.getElementById('customizeDashboardBtn')?.addEventListener('click', function() {
                    alert('Personalização será disponibilizada em breve!');
                });
            });
        </script><!-- Estilos simplificados -->
        <style>
            /* Inputs de notas */
            .nota-input {
                text-align: center;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                font-weight: 600;
                padding: 0.4rem;
                width: 60px;
                transition: border-color 0.2s;
            }

            .nota-input:focus {
                border-color: var(--primary-color);
                outline: none;
            }

            .nota-input::placeholder {
                color: var(--warning-color);
            }

            /* Loading bar */
            .loading-bar {
                height: 3px;
                background: var(--primary-color);
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                z-index: 9999;
                display: none;
            }

            /* Cards de aulas simples */
            .modern-card {
                transition: transform 0.2s;
                border: 1px solid #e5e7eb;
            }

            .modern-card:hover {
                transform: translateY(-1px);
            }

            /* Progress bar simples */
            .progress {
                border-radius: 8px;
                background-color: #f3f4f6;
            }

            .progress-bar {
                border-radius: 8px;
                transition: width 0.3s;
            }
        </style>

        <!-- Adicionar modal para gráficos de desempenho -->
        <div class="modal fade" id="graficoDesempenhoModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-chart-line me-2"></i>
                            Análise de Desempenho
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-tabs mb-3" id="chartTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="notas-tab" data-bs-toggle="tab" data-bs-target="#notas-chart" type="button">
                                    <i class="fas fa-star me-1"></i> Notas
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="frequencia-tab" data-bs-toggle="tab" data-bs-target="#frequencia-chart" type="button">
                                    <i class="fas fa-calendar-check me-1"></i> Frequência
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="notas-chart">
                                <div class="chart-container">
                                    <canvas id="notasChart"></canvas>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="frequencia-chart">
                                <div class="chart-container">
                                    <canvas id="frequenciaChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Inicializar gráficos quando o botão for clicado
            document.getElementById('btnGraficoDesempenho')?.addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('graficoDesempenhoModal'));
                modal.show();

                // Timeout para garantir que o modal esteja visível antes de renderizar os gráficos
                setTimeout(() => {
                    // Dados das disciplinas para os gráficos
                    const disciplinas = <?php echo json_encode(array_map(function ($d) {
                                            $partes = explode(' - ', $d['disciplina'], 2);
                                            $nome = isset($partes[1]) ? $partes[1] : $d['disciplina'];

                                            return [
                                                'nome' => $nome,
                                                'notas' => [
                                                    isset($d['nota_etapa_1']['nota']) ? $d['nota_etapa_1']['nota'] : null,
                                                    isset($d['nota_etapa_2']['nota']) ? $d['nota_etapa_2']['nota'] : null,
                                                    isset($d['nota_etapa_3']['nota']) ? $d['nota_etapa_3']['nota'] : null,
                                                    isset($d['nota_etapa_4']['nota']) ? $d['nota_etapa_4']['nota'] : null
                                                ],
                                                'media' => isset($d['media_final_disciplina']) ? $d['media_final_disciplina'] : null,
                                                'frequencia' => isset($d['percentual_carga_horaria_frequentada']) ? $d['percentual_carga_horaria_frequentada'] : null,
                                            ];
                                        }, $boletim ?? [])); ?>;

                    // Renderizar gráfico de notas
                    const ctxNotas = document.getElementById('notasChart');
                    if (ctxNotas) {
                        const notasChart = new Chart(ctxNotas, {
                            type: 'bar',
                            data: {
                                labels: disciplinas.map(d => d.nome),
                                datasets: [{
                                        label: 'Nota 1',
                                        data: disciplinas.map(d => d.notas[0]),
                                        backgroundColor: 'rgba(67, 97, 238, 0.7)',
                                        borderColor: 'rgba(67, 97, 238, 1)',
                                        borderWidth: 1
                                    },
                                    {
                                        label: 'Nota 2',
                                        data: disciplinas.map(d => d.notas[1]),
                                        backgroundColor: 'rgba(46, 196, 182, 0.7)',
                                        borderColor: 'rgba(46, 196, 182, 1)',
                                        borderWidth: 1
                                    },
                                    {
                                        label: 'Nota 3',
                                        data: disciplinas.map(d => d.notas[2]),
                                        backgroundColor: 'rgba(255, 159, 28, 0.7)',
                                        borderColor: 'rgba(255, 159, 28, 1)',
                                        borderWidth: 1
                                    },
                                    {
                                        label: 'Nota 4',
                                        data: disciplinas.map(d => d.notas[3]),
                                        backgroundColor: 'rgba(230, 57, 70, 0.7)',
                                        borderColor: 'rgba(230, 57, 70, 1)',
                                        borderWidth: 1
                                    },
                                    {
                                        label: 'Média',
                                        data: disciplinas.map(d => d.media),
                                        type: 'line',
                                        borderColor: 'rgba(130, 57, 230, 1)',
                                        backgroundColor: 'rgba(130, 57, 230, 0.1)',
                                        fill: false,
                                        borderWidth: 2,
                                        pointRadius: 4
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'top',
                                    },
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        max: 100,
                                        title: {
                                            display: true,
                                            text: 'Nota'
                                        }
                                    },
                                    x: {
                                        ticks: {
                                            maxRotation: 45,
                                            minRotation: 45
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // Renderizar gráfico de frequência
                    const ctxFreq = document.getElementById('frequenciaChart');
                    if (ctxFreq) {
                        const freqChart = new Chart(ctxFreq, {
                            type: 'bar',
                            data: {
                                labels: disciplinas.map(d => d.nome),
                                datasets: [{
                                    label: 'Frequência',
                                    data: disciplinas.map(d => d.frequencia),
                                    backgroundColor: disciplinas.map(d => {
                                        const freq = d.frequencia;
                                        if (freq === null) return 'rgba(173, 181, 189, 0.7)';
                                        if (freq < 75) return 'rgba(230, 57, 70, 0.7)';
                                        if (freq < 85) return 'rgba(255, 159, 28, 0.7)';
                                        return 'rgba(46, 196, 182, 0.7)';
                                    }),
                                    borderColor: disciplinas.map(d => {
                                        const freq = d.frequencia;
                                        if (freq === null) return 'rgba(173, 181, 189, 1)';
                                        if (freq < 75) return 'rgba(230, 57, 70, 1)';
                                        if (freq < 85) return 'rgba(255, 159, 28, 1)';
                                        return 'rgba(46, 196, 182, 1)';
                                    }),
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'top'
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        max: 100,
                                        title: {
                                            display: true,
                                            text: 'Frequência (%)'
                                        }
                                    },
                                    x: {
                                        ticks: {
                                            maxRotation: 45,
                                            minRotation: 45
                                        }
                                    }
                                }
                            }
                        });
                    }
                }, 300);
            });
        </script>

        <?php
        $pageContent = ob_get_clean(); // Captura o conteúdo do buffer
        require_once 'base.php'; // Inclui o template base
        ?>