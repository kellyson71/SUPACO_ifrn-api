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
session_start();

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
    }
    curl_close($ch);

    // Verificar se ocorreu um erro de autenticação
    return verificarRespostaAPI(json_decode($response, true), $httpcode);
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
    // Converte para o formato do SUAP (2-6)
    // No SUAP: 2=Segunda, 3=Terça, 4=Quarta, 5=Quinta, 6=Sexta
    $diaSuap = $dia + 1;

    $aulas = [];

    foreach ($horarios as $disciplina) {
        if (!empty($disciplina['horarios_de_aula'])) {
            $horariosArray = parseHorario($disciplina['horarios_de_aula']);
            foreach ($horariosArray as $h) {
                if ($h['dia'] == $diaSuap) {
                    $aulas[] = [
                        'disciplina' => $disciplina['sigla'],
                        'nome' => $disciplina['descricao'],
                        'local' => $disciplina['locais_de_aula'][0] ?? '',
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
    // Se for final de semana (6=sábado, 7=domingo), retorna vazio
    $diaSemana = (int)$data->format('N');
    if ($diaSemana > 5) {
        return [];
    }

    // Converte para o formato do SUAP (2-6)
    // No SUAP: 2=Segunda, 3=Terça, 4=Quarta, 5=Quinta, 6=Sexta
    $diaSuap = $diaSemana + 1;

    // Verificar se é feriado
    if (function_exists('verificarFeriado')) {
        $feriado = verificarFeriado($data);
        if ($feriado) {
            // É feriado, não há aulas
            return [];
        }
    }

    // Usa a função existente para obter as aulas de um dia da semana específico
    return getAulasDoDia($horarios, $diaSemana);
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

    return getAulasDoDia($horarios, $amanha);
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

    // Se for final de semana (6-sábado ou 7-domingo), retorna vazio
    if ($hoje > 5) {
        return [];
    }

    return getAulasDoDia($horarios, $hoje);
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
        foreach ($aula['horario']['aulas'] as $numeroAula) {
            $chave = $aula['horario']['turno'] . $numeroAula;
            if (isset($horarios[$chave])) {
                $aulasOrdenadas[] = array_merge($aula, [
                    'horario_detalhado' => $horarios[$chave]['hora'],
                    'ordem' => $chave
                ]);
            }
        }
    }

    usort($aulasOrdenadas, function ($a, $b) {
        return strcmp($a['ordem'], $b['ordem']);
    });

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
$anoLetivo = date('Y');
$periodoLetivo = '1'; // TODO: Implementar detecção automática do período

// Armazena todas as respostas da API para depuração
$apiResponses = [
    'meusDados' => $meusDados
];

if ($meusDados && isset($meusDados['matricula'])) {
    $boletim = getSuapData("minhas-informacoes/boletim/{$anoLetivo}/{$periodoLetivo}/");
    $horarios = getSuapData("minhas-informacoes/turmas-virtuais/{$anoLetivo}/{$periodoLetivo}/");

    // Adiciona as respostas para depuração
    $apiResponses['boletim'] = $boletim;
    $apiResponses['horarios'] = $horarios;
}

// Preparação dos dados para a view
$diasSemana = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
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
                    <div class="dashboard-title">
                        <h2 class="mb-0">Dashboard Acadêmico</h2>
                        <p class="text-muted mb-0">Visão geral do seu desempenho acadêmico</p>
                    </div>
                    <div class="dashboard-actions">
                        <div class="btn-group shadow-sm" role="group">
                            <button type="button" class="btn btn-sm btn-light border" id="refreshDashboardBtn" title="Atualizar dados">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-light border" id="filterDashboardBtn" title="Filtrar dados">
                                <i class="fas fa-filter"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-light border" id="customizeDashboardBtn" title="Personalizar dashboard">
                                <i class="fas fa-columns"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hero Section -->
        <div class="card bg-primary text-white shadow-lg mb-4 rounded-3 border-0 animate-fade-in-up">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <?php if (isset($meusDados['url_foto_150x200'])): ?>
                            <img src="<?php echo htmlspecialchars($meusDados['url_foto_150x200']); ?>"
                                class="rounded-circle border border-3 border-white shadow"
                                style="width: 100px; height: 100px; object-fit: cover;">
                        <?php else: ?>
                            <i class="fas fa-user-circle" style="font-size: 5rem;"></i>
                        <?php endif; ?>
                    </div>
                    <div class="col">
                        <h2 class="display-6 mb-0">Olá, <?php echo htmlspecialchars($meusDados['nome_usual']); ?>!</h2>
                        <p class="lead mb-0">
                            <?php
                            $vinculo = $meusDados['vinculo'] ?? [];
                            $curso = '';

                            if (isset($vinculo['curso'])) {
                                $curso = $vinculo['curso'];
                            } elseif (isset($vinculo['curso_turma'])) {
                                $curso = $vinculo['curso_turma'];
                            } elseif (isset($meusDados['curso'])) {
                                $curso = $meusDados['curso'];
                            }

                            echo htmlspecialchars($meusDados['tipo_vinculo'] ?? 'Aluno');
                            if ($curso) {
                                echo ' • ' . htmlspecialchars($curso);
                            }
                            ?>
                        </p>
                        <div class="mt-2">
                            <small>
                                <i class="fas fa-calendar-alt me-1"></i>
                                <?php echo $anoLetivo; ?>.<?php echo $periodoLetivo; ?> •
                                Matrícula: <?php echo htmlspecialchars($meusDados['matricula']); ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-primary-dark py-2 border-top border-primary-dark">
                <div class="row text-center">
                    <div class="col">
                        <small>
                            <i class="fas fa-clock me-1"></i>
                            <?php echo date('d/m/Y H:i'); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção de Aulas -->
        <div class="row mb-4 animate-fade-in-up" style="animation-delay: 0.2s">
            <div class="col-md-8">
                <div class="card h-100 shadow-sm glass-effect">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-calendar-day me-2"></i>
                                    Aulas por Dia
                                </h5>
                                <span class="badge bg-white text-primary ms-3">
                                    <?php echo $nomeDia; ?>
                                </span>
                            </div>
                            <div class="btn-group" role="group">
                                <a href="?dia=hoje" class="btn btn-sm <?php echo $mostrarDia === 'hoje' ? 'btn-light' : 'btn-outline-light'; ?>">
                                    <i class="fas fa-calendar-day me-1"></i>Hoje
                                </a>
                                <a href="?dia=amanha" class="btn btn-sm <?php echo $mostrarDia === 'amanha' ? 'btn-light' : 'btn-outline-light'; ?>">
                                    <i class="fas fa-calendar-plus me-1"></i>Amanhã
                                </a>
                            </div>
                        </div>

                        <!-- Seletor de dias moderno -->
                        <div class="day-select-container mt-3">
                            <select id="daySelector" class="day-select form-select form-select-sm bg-transparent text-white border-light">
                                <option value="" disabled selected>Selecionar dia...</option>
                                <?php
                                // Gerar opções para os próximos 14 dias (2 semanas)
                                $dataInicio = new DateTime();
                                for ($i = 0; $i < 14; $i++) {
                                    $data = clone $dataInicio;
                                    $data->modify("+$i days");
                                    $valor = $data->format('Y-m-d');

                                    // Verificar se é final de semana (6=sábado, 7=domingo)
                                    $diaSemana = (int)$data->format('N');
                                    $classeData = '';

                                    // Formatar a data para exibição
                                    $dataFormatada = $data->format('d/m');
                                    $nomeDiaSemana = $diasSemana[$diaSemana];

                                    // Verificar se é feriado
                                    $feriado = verificarFeriado($data);
                                    $feriadoTag = $feriado ? ' <span class="holiday-badge"><i class="fas fa-star"></i>' . htmlspecialchars($feriado) . '</span>' : '';

                                    // Classe para destacar finais de semana e feriados
                                    if ($diaSemana > 5) {
                                        $classeData = 'text-warning'; // Final de semana
                                    } else if ($feriado) {
                                        $classeData = 'text-danger'; // Feriado
                                    }

                                    echo '<option value="' . $valor . '" class="' . $classeData . '">';
                                    echo $dataFormatada . ' - ' . $nomeDiaSemana . $feriadoTag;
                                    echo '</option>';
                                }
                                ?>
                            </select>
                            <i class="fas fa-chevron-down select-arrow"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php
                        // Obtém as aulas de acordo com o dia selecionado (hoje, amanhã, ou data específica)
                        if ($mostrarDia === 'hoje') {
                            $aulas = getAulasHoje($horarios);
                        } else if ($mostrarDia === 'data' && isset($dataExibicao)) {
                            $aulas = getAulasDeData($horarios, $dataExibicao);
                        } else {
                            $aulas = getAulasAmanha($horarios);
                        }

                        if (!empty($aulas)):
                            $aulasOrdenadas = ordenarAulasPorHorario($aulas);
                        ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($aulasOrdenadas as $aula): ?>
                                    <div class="list-group-item border-0 px-0">
                                        <div class="d-flex w-100 justify-content-between align-items-center">
                                            <div>
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="badge bg-primary-light text-primary me-2">
                                                        <?php echo $aula['horario_detalhado']; ?>
                                                    </span>
                                                    <small class="text-muted disciplina-codigo">
                                                        <?php echo htmlspecialchars($aula['disciplina']); ?>
                                                    </small>
                                                </div>
                                                <p class="mb-1 disciplina-nome"><?php echo htmlspecialchars($aula['nome']); ?></p>
                                                <?php if (!empty($aula['local'])): ?>
                                                    <small class="text-muted">
                                                        <i class="fas fa-map-marker-alt me-1"></i>
                                                        <?php echo htmlspecialchars($aula['local']); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                                <?php
                                                $turno = $aula['horario']['turno'] == 'M' ? 'Manhã' : 'Tarde';
                                                echo $turno;
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0 text-center py-3">
                                <i class="fas fa-coffee me-2"></i>
                                Não há aulas programadas para <?php echo strtolower($tituloDia); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm glass-effect">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-percentage me-2"></i>
                                Impacto na Frequência
                            </h5>
                            <button type="button" class="btn btn-sm btn-light bg-opacity-25"
                                data-bs-toggle="modal" data-bs-target="#frequenciaHelpModal"
                                title="Ajuda sobre frequência">
                                <i class="fas fa-question"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($aulas)): ?>
                            <?php
                            // Calcula estatísticas gerais para todas as disciplinas do dia
                            $statusGeral = 'success';
                            $disciplinasComRisco = 0;
                            $disciplinasComRiscoAlto = 0;
                            $totalDisciplinas = 0;
                            $dadosImpacto = [];

                            foreach ($aulas as $aula) {
                                foreach ($boletim as $disciplina) {
                                    if (strpos($disciplina['disciplina'], $aula['disciplina']) !== false) {
                                        $totalDisciplinas++;
                                        $impacto = calcularImpactoFalta($disciplina);
                                        if ($impacto) {
                                            $dadosImpacto[] = $impacto;
                                            $status = podeFaltarAmanha($disciplina);

                                            if ($status === 'danger') {
                                                $statusGeral = 'danger';
                                                $disciplinasComRiscoAlto++;
                                                $disciplinasComRisco++;
                                            } else if ($status === 'warning' && $statusGeral !== 'danger') {
                                                $statusGeral = 'warning';
                                                $disciplinasComRisco++;
                                            }
                                        }
                                    }
                                }
                            }

                            $imagemStatus = [
                                'success' => 'image.png',
                                'warning' => 'image3.png',
                                'danger' => 'image2.png'
                            ];

                            $mensagemStatus = [
                                'success' => 'Você pode faltar hoje',
                                'warning' => 'Cuidado com as faltas!',
                                'danger' => 'Melhor não faltar hoje'
                            ];

                            $corStatus = [
                                'success' => 'text-success',
                                'warning' => 'text-warning',
                                'danger' => 'text-danger'
                            ];

                            // Calcula informações do painel resumo
                            $temDisciplinaCritica = $disciplinasComRiscoAlto > 0;
                            $temDisciplinaAtencao = $disciplinasComRisco > $disciplinasComRiscoAlto;
                            ?>

                            <!-- Painel de Resumo Visual -->
                            <div class="row mb-4">
                                <div class="col-md-5 text-center">
                                    <img src="assets/<?php echo $imagemStatus[$statusGeral]; ?>"
                                        alt="Status de frequência"
                                        class="mb-2"
                                        style="height: 75px; width: auto;">
                                    <h5 class="mb-1 <?php echo $corStatus[$statusGeral]; ?>">
                                        <?php echo $mensagemStatus[$statusGeral]; ?>
                                    </h5>

                                    <div class="text-muted small mt-1">
                                        <?php if ($disciplinasComRisco > 0): ?>
                                            <strong><?php echo $disciplinasComRisco; ?></strong>
                                            <?php echo $disciplinasComRisco > 1 ? 'disciplinas' : 'disciplina'; ?>
                                            requerem atenção nas faltas
                                        <?php else: ?>
                                            Todas as disciplinas estão com frequência adequada
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="col-md-7">
                                    <div class="bg-light p-3 rounded-3">
                                        <h6 class="border-bottom pb-2 mb-2">Resumo da Frequência</h6>

                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="h5 mb-0 <?php echo $temDisciplinaCritica ? 'text-danger' : 'text-muted'; ?>">
                                                    <?php echo $disciplinasComRiscoAlto; ?>
                                                </div>
                                                <div class="small text-muted">Críticas</div>
                                            </div>
                                            <div class="col-4">
                                                <div class="h5 mb-0 <?php echo $temDisciplinaAtencao ? 'text-warning' : 'text-muted'; ?>">
                                                    <?php echo $disciplinasComRisco - $disciplinasComRiscoAlto; ?>
                                                </div>
                                                <div class="small text-muted">Atenção</div>
                                            </div>
                                            <div class="col-4">
                                                <div class="h5 mb-0 text-success">
                                                    <?php echo $totalDisciplinas - $disciplinasComRisco; ?>
                                                </div>
                                                <div class="small text-muted">Seguras</div>
                                            </div>
                                        </div>

                                        <div class="progress mt-3" style="height: 6px;">
                                            <?php if ($disciplinasComRiscoAlto > 0): ?>
                                                <div class="progress-bar bg-danger" style="width: <?php echo ($disciplinasComRiscoAlto / $totalDisciplinas) * 100; ?>%"></div>
                                            <?php endif; ?>

                                            <?php if ($disciplinasComRisco - $disciplinasComRiscoAlto > 0): ?>
                                                <div class="progress-bar bg-warning" style="width: <?php echo (($disciplinasComRisco - $disciplinasComRiscoAlto) / $totalDisciplinas) * 100; ?>%"></div>
                                            <?php endif; ?>

                                            <div class="progress-bar bg-success" style="width: <?php echo (($totalDisciplinas - $disciplinasComRisco) / $totalDisciplinas) * 100; ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Informações detalhadas por disciplina -->
                            <h6 class="border-bottom pb-2 mb-3">
                                <i class="fas fa-info-circle me-1"></i>
                                Detalhes por disciplina
                            </h6>
                            <div class="list-group">
                                <?php foreach ($aulas as $aula): ?>
                                    <?php
                                    foreach ($boletim as $disciplina):
                                        if (strpos($disciplina['disciplina'], $aula['disciplina']) !== false):
                                            $impacto = calcularImpactoFalta($disciplina);
                                            if ($impacto):                                            // Extrai o nome da disciplina sem o código
                                                preg_match('/^([^-]+) - (.+?)(\(\d+H\))?$/', $disciplina['disciplina'], $matches);
                                                $codigoDisciplina = $matches[1] ?? '';
                                                $nomeDisciplina = $matches[2] ?? $aula['disciplina'];
                                                // Remove possível especificação de carga horária entre parênteses
                                                $nomeDisciplina = preg_replace('/\s*\(\d+H\)\s*$/', '', $nomeDisciplina);

                                                // Define as classes de cores para os níveis de risco
                                                $corRisco = [
                                                    'baixo' => 'success',
                                                    'medio' => 'warning',
                                                    'alto' => 'danger'
                                                ];

                                                // Define as mensagens de recomendação baseadas no nível de risco
                                                $mensagemRisco = [
                                                    'baixo' => 'Pode faltar com segurança',
                                                    'medio' => 'Fique atento às suas faltas',
                                                    'alto' => 'Não deve faltar mais'
                                                ];

                                                // Obtém a cor do risco
                                                $corRiscoAtual = $corRisco[$impacto['nivel_risco']] ?? 'warning';
                                    ?> <div class="list-group-item">
                                                    <h6 class="mb-2 d-flex justify-content-between align-items-center">
                                                        <span>
                                                            <?php if (!empty($codigoDisciplina)): ?>
                                                                <span class="disciplina-codigo"><?php echo htmlspecialchars(trim($codigoDisciplina)); ?></span>
                                                            <?php endif; ?>
                                                            <span class="disciplina-nome-pequeno"><?php echo htmlspecialchars(trim($nomeDisciplina)); ?></span>
                                                        </span>
                                                        <span class="badge bg-<?php echo $corRiscoAtual; ?> bg-opacity-20 text-<?php echo $corRiscoAtual; ?> px-3 py-1">
                                                            <?php echo $mensagemRisco[$impacto['nivel_risco']] ?? 'Verificar frequência'; ?>
                                                        </span>
                                                    </h6>

                                                    <!-- Informações detalhadas de frequência -->
                                                    <div class="small">
                                                        <div class="row mb-2">
                                                            <div class="col-md-6">
                                                                <div><strong>Frequência atual:</strong> <?php echo number_format($impacto['atual'], 1); ?>%</div>
                                                                <div><strong>Após uma falta:</strong> <?php echo number_format($impacto['nova'], 1); ?>%</div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div><strong>Aulas dadas:</strong> <?php echo $impacto['aulas_dadas']; ?> de <?php echo $impacto['aulas_totais']; ?></div>
                                                                <div><strong>Faltas:</strong> <?php echo $impacto['faltas_atuais']; ?> de <?php echo $impacto['maximo_faltas']; ?> permitidas</div>
                                                            </div>
                                                        </div>

                                                        <!-- Barra de progresso para faltas -->
                                                        <div class="mb-1 small fw-bold d-flex justify-content-between align-items-center">
                                                            <span>Faltas utilizadas:</span>
                                                            <span><?php echo $impacto['faltas_atuais']; ?>/<?php echo $impacto['maximo_faltas']; ?></span>
                                                        </div>
                                                        <div class="progress mb-2" style="height: 8px;">
                                                            <div class="progress-bar bg-<?php echo $corRiscoAtual; ?>"
                                                                role="progressbar"
                                                                style="width: <?php echo $impacto['proporcao_faltas']; ?>%;"
                                                                aria-valuenow="<?php echo $impacto['faltas_atuais']; ?>"
                                                                aria-valuemin="0"
                                                                aria-valuemax="<?php echo $impacto['maximo_faltas']; ?>">
                                                            </div>
                                                        </div> <!-- Informações de impacto -->
                                                        <div class="mt-2 text-<?php echo $corRiscoAtual; ?> small fw-bold">
                                                            <?php if ($impacto['faltas_atuais'] >= $impacto['maximo_faltas']): ?>
                                                                <i class="fas fa-exclamation-triangle me-1"></i> Limite de faltas atingido!
                                                            <?php elseif ($impacto['faltas_restantes'] <= 3 && $impacto['faltas_restantes'] > 0): ?>
                                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                                Restam apenas <?php echo $impacto['faltas_restantes']; ?>
                                                                <?php echo $impacto['faltas_restantes'] == 1 ? 'falta' : 'faltas'; ?>!
                                                            <?php else: ?>
                                                                <div class="text-success">
                                                                    <i class="fas fa-check-circle me-1"></i>
                                                                    Você ainda tem <?php echo $impacto['faltas_restantes']; ?> faltas disponíveis
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- Seção de dicas e orientações -->
                                                        <?php if ($impacto['faltas_restantes'] <= 3): ?>
                                                            <div class="mt-2 pt-2 border-top">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-info-circle me-1"></i>
                                                                    Impacto da próxima falta: -<?php echo number_format($impacto['impacto'], 1); ?>%
                                                                    na sua frequência total.
                                                                </small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                <?php
                                            endif;
                                        endif;
                                    endforeach;
                                endforeach;
                                ?>
                            </div>
                            <!-- Recomendações personalizadas -->
                            <div class="mt-4 p-3 bg-light rounded-3 border-start border-4 border-primary">
                                <h6 class="mb-2 d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="fas fa-lightbulb text-primary me-1"></i>
                                        Recomendações para você
                                    </span>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#calculadoraFrequenciaModal">
                                        <i class="fas fa-calculator me-1"></i> Simular Faltas
                                    </button>
                                </h6>

                                <p class="small mb-0">
                                    <?php if ($disciplinasComRiscoAlto > 0): ?>
                                        <strong>Atenção!</strong> Você possui disciplinas com alto risco de reprovação por falta.
                                        Recomendamos que evite faltar nas próximas aulas e entre em contato com seus professores
                                        para verificar a possibilidade de abono de faltas anteriores ou atividades compensatórias.
                                    <?php elseif ($disciplinasComRisco > 0): ?>
                                        <strong>Alerta:</strong> Algumas disciplinas estão se aproximando do limite de faltas permitido.
                                        Você deve gerenciar cuidadosamente suas faltas daqui para frente, guardando-as para emergências reais.
                                    <?php else: ?>
                                        <strong>Tudo certo!</strong> Sua frequência está adequada em todas as disciplinas.
                                        Continue mantendo uma boa assiduidade para garantir seu sucesso acadêmico.
                                    <?php endif; ?>

                                    <br><br>
                                    <em>Lembre-se:</em> A frequência mínima exigida para aprovação é de 75% em cada disciplina.
                                    Use este painel para acompanhar sua situação e tomar decisões informadas sobre suas faltas.
                                </p>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">
                                Não há aulas para analisar o impacto na frequência.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resto do conteúdo permanece igual -->
        <div class="animate-fade-in-up" style="animation-delay: 0.4s">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0" data-aos="fade-right">
                    <i class="fas fa-graduation-cap me-2 text-primary"></i>
                    Boletim <?php echo $anoLetivo; ?>.<?php echo $periodoLetivo; ?>
                </h3>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Exportar para PDF">
                        <i class="fas fa-file-pdf me-1"></i> Exportar
                    </button>
                    <button class="btn btn-sm btn-outline-primary" id="btnGraficoDesempenho" data-bs-toggle="tooltip" title="Visualizar gráficos">
                        <i class="fas fa-chart-bar me-1"></i> Gráficos
                    </button>
                </div>
            </div>

            <?php if (isset($boletim) && is_array($boletim)): ?> <div class="card shadow-sm" data-aos="fade-up" data-aos-delay="100">
                    <div class="table-responsive">
                        <table class="table table-boletim mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width: 200px">Disciplina</th>
                                    <th>Nota 1</th>
                                    <th>Nota 2</th>
                                    <th>Nota 3</th>
                                    <th>Nota 4</th>
                                    <th>Média</th>
                                    <th>Freq. (%)</th>
                                    <th>Situação</th>
                                </tr>
                            </thead>
                            <tbody><?php foreach ($boletim as $disciplina): ?>
                                    <?php
                                        // Extraindo o nome da disciplina (assumindo que o formato é "CÓDIGO - NOME DA DISCIPLINA")
                                        $disciplinaTexto = $disciplina['disciplina'];
                                        $partes = explode(' - ', $disciplinaTexto, 2);
                                        $codigoDisciplina = isset($partes[0]) ? $partes[0] : $disciplinaTexto;
                                        $nomeDisciplina = isset($partes[1]) ? $partes[1] : '';
                                    ?> <tr class="disciplina-item">
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
                                                <div class="nota-simulacao">
                                                    <input type="number"
                                                        class="form-control form-control-sm nota-input"
                                                        style="width: 60px"
                                                        min="0"
                                                        max="100"
                                                        step="0.1"
                                                        data-disciplina="<?php echo htmlspecialchars($disciplina['disciplina']); ?>"
                                                        data-etapa="1"
                                                        placeholder="<?php echo number_format(calcularNotaNecessaria($disciplina), 1); ?>">
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (isset($disciplina['nota_etapa_2']['nota'])): ?>
                                                <span class="nota-valor <?php echo $disciplina['nota_etapa_2']['nota'] >= 60 ? 'text-success' : 'text-danger'; ?>">
                                                    <?php echo $disciplina['nota_etapa_2']['nota']; ?>
                                                </span>
                                            <?php else: ?>
                                                <div class="nota-simulacao">
                                                    <input type="number"
                                                        class="form-control form-control-sm nota-input"
                                                        style="width: 60px"
                                                        min="0"
                                                        max="100"
                                                        step="0.1"
                                                        data-disciplina="<?php echo htmlspecialchars($disciplina['disciplina']); ?>"
                                                        data-etapa="2"
                                                        placeholder="<?php echo number_format(calcularNotaNecessaria($disciplina), 1); ?>">
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (isset($disciplina['nota_etapa_3']['nota'])): ?>
                                                <span class="nota-valor <?php echo $disciplina['nota_etapa_3']['nota'] >= 60 ? 'text-success' : 'text-danger'; ?>">
                                                    <?php echo $disciplina['nota_etapa_3']['nota']; ?>
                                                </span>
                                            <?php else: ?>
                                                <div class="nota-simulacao">
                                                    <input type="number"
                                                        class="form-control form-control-sm nota-input"
                                                        style="width: 60px"
                                                        min="0"
                                                        max="100"
                                                        step="0.1"
                                                        data-disciplina="<?php echo htmlspecialchars($disciplina['disciplina']); ?>"
                                                        data-etapa="3"
                                                        placeholder="<?php echo number_format(calcularNotaNecessaria($disciplina), 1); ?>">
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (isset($disciplina['nota_etapa_4']['nota'])): ?>
                                                <span class="nota-valor <?php echo $disciplina['nota_etapa_4']['nota'] >= 60 ? 'text-success' : 'text-danger'; ?>">
                                                    <?php echo $disciplina['nota_etapa_4']['nota']; ?>
                                                </span>
                                            <?php else: ?>
                                                <div class="nota-simulacao">
                                                    <input type="number"
                                                        class="form-control form-control-sm nota-input"
                                                        style="width: 60px"
                                                        min="0"
                                                        max="100"
                                                        step="0.1"
                                                        data-disciplina="<?php echo htmlspecialchars($disciplina['disciplina']); ?>"
                                                        data-etapa="4"
                                                        placeholder="<?php echo number_format(calcularNotaNecessaria($disciplina), 1); ?>">
                                                </div>
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
                                        <td>
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
                                            ?> <div class="<?php echo $situacaoClass; ?>">
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

    <!-- Adicione este script antes do fechamento do body -->
    <script>
        document.querySelectorAll('.nota-input').forEach(input => {
            input.addEventListener('input', function() {
                const disciplina = this.dataset.disciplina;
                const etapa = parseInt(this.dataset.etapa);
                const valor = parseFloat(this.value) || 0;

                // Pega todas as notas simuladas da disciplina
                const notasSimuladas = Array.from(document.querySelectorAll(`.nota-input[data-disciplina="${disciplina}"]`))
                    .map(input => ({
                        etapa: parseInt(input.dataset.etapa),
                        valor: parseFloat(input.value) || 0,
                        peso: input.dataset.etapa <= 2 ? 2 : 3
                    }));

                // Calcula a média com as notas simuladas
                let somaNotas = 0;
                let somaPesos = 0;

                notasSimuladas.forEach(nota => {
                    if (nota.valor > 0) {
                        somaNotas += nota.valor * nota.peso;
                        somaPesos += nota.peso;
                    }
                });

                // Atualiza os placeholders das outras notas
                if (somaPesos > 0) {
                    const mediaDesejada = 60;
                    const pontosNecessarios = (mediaDesejada * 10) - somaNotas;
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
    </script>

    <!-- Script para as funcionalidades do dashboard -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Botão de atualização do dashboard
            document.getElementById('refreshDashboardBtn')?.addEventListener('click', function() {
                // Mostrar indicador de carregamento
                const loadingBar = document.getElementById('loadingBar');
                if (loadingBar) loadingBar.style.display = 'block';

                showNotification('Atualizando dados...', 'info');

                // Recarregar a página após breve delay
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            });

            // Botão de filtro do dashboard
            document.getElementById('filterDashboardBtn')?.addEventListener('click', function() {
                showNotification('Função de filtro será disponibilizada em breve!', 'info');

                // Futuramente aqui será implementado um modal de filtro
            });

            // Botão de personalização do dashboard
            document.getElementById('customizeDashboardBtn')?.addEventListener('click', function() {
                showNotification('Personalização do dashboard será disponibilizada em breve!', 'info');

                // Futuramente aqui será implementado um modal de personalização
            });
        });
    </script>

    <!-- Ativação do sistema de notificações toast -->
    <script>
        // Após o carregamento do DOM, exibir uma notificação de boas-vindas
        document.addEventListener('DOMContentLoaded', function() {
            // Animações para itens do boletim
            const disciplinaItems = document.querySelectorAll('.disciplina-item');
            disciplinaItems.forEach((item, index) => {
                item.classList.add('boletim-item');
                item.style.animationDelay = `${0.1 + index * 0.05}s`;
            });

            // Exibir notificação de boas-vindas após um pequeno atraso
            setTimeout(() => {
                // Verificar se a função Toastify está disponível
                if (typeof Toastify !== 'undefined') {
                    Toastify({
                        text: '<i class="fas fa-check-circle"></i> Bem-vindo(a) ao SUPACO! Dados acadêmicos carregados.',
                        duration: 5000,
                        gravity: "top",
                        position: "right",
                        className: "toast-custom",
                        escapeMarkup: false,
                        style: {
                            background: "linear-gradient(to right, #10b981, #059669)",
                            boxShadow: "0 3px 10px rgba(0,0,0,0.1)",
                            borderRadius: "8px",
                        }
                    }).showToast();
                }
            }, 1000);
        });

        // Função para exibir notificações toast para uso em todo o sistema
        function showNotification(message, type = 'success') {
            const bgColors = {
                success: "linear-gradient(to right, #10b981, #059669)",
                warning: "linear-gradient(to right, #f59e0b, #d97706)",
                danger: "linear-gradient(to right, #ef4444, #dc2626)",
                info: "linear-gradient(to right, #06b6d4, #0891b2)"
            };

            const icons = {
                success: '<i class="fas fa-check-circle"></i> ',
                warning: '<i class="fas fa-exclamation-circle"></i> ',
                danger: '<i class="fas fa-exclamation-triangle"></i> ',
                info: '<i class="fas fa-info-circle"></i> '
            };

            if (typeof Toastify !== 'undefined') {
                Toastify({
                    text: icons[type] + message,
                    duration: 5000,
                    gravity: "top",
                    position: "right",
                    className: "toast-custom",
                    escapeMarkup: false,
                    style: {
                        background: bgColors[type],
                        boxShadow: "0 3px 10px rgba(0,0,0,0.1)",
                        borderRadius: "8px",
                    }
                }).showToast();
            }
        }
    </script>

    <!-- Adicione estes estilos ao arquivo base.php -->
    <style>
        .nota-simulacao {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .nota-input {
            text-align: center;
            border: 1px solid var(--neutral-300);
            border-radius: 8px;
            transition: all 0.25s var(--transition-function);
            font-weight: 600;
            padding: 0.4rem;
            width: 60px !important;
        }

        .nota-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
            outline: none;
        }

        .nota-input::placeholder {
            color: var(--warning-color);
            opacity: 1;
            font-weight: 500;
        }

        .nota-valor {
            font-weight: 600;
            font-size: 1rem;
        }

        /* Modal para gráficos */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        /* Loading state */
        .loading-bar {
            height: 3px;
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            background: linear-gradient(to right, var(--primary-color), var(--info-color));
            z-index: 9999;
            display: none;
            animation: progress 2s ease-in-out infinite;
            background-size: 200% 100%;
        }

        @keyframes progress {
            0% {
                background-position: 100% 0;
            }

            100% {
                background-position: -100% 0;
            }
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