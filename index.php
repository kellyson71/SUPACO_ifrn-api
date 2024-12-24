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
session_start();

// Autenticação SUAP
if (!isset($_SESSION['access_token'])) {
    header('Location: login.php');
    exit;
}

/**
 * Realiza requisições autenticadas à API do SUAP
 * 
 * @param string $endpoint Endpoint da API a ser consultado
 * @return array|null Dados retornados pela API ou null em caso de erro
 */
function getSuapData($endpoint) {
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
    
    if(curl_errno($ch)) {
        error_log("Erro CURL: " . curl_error($ch));
    }
    
    curl_close($ch);
    return json_decode($response, true);
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
function calcularNotaNecessaria($notas, $pesos = [2, 2, 3, 3]) {
    $media_minima = 60;
    $soma_pesos = array_sum($pesos);
    $soma_atual = 0;
    $peso_restante = 0;
    
    // Calcula a soma das notas existentes com seus respectivos pesos
    for ($i = 0; $i < 4; $i++) {
        $nota = isset($notas["nota_etapa_" . ($i+1)]['nota']) ? $notas["nota_etapa_" . ($i+1)]['nota'] : null;
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
 * Processa o horário das aulas do dia seguinte
 * 
 * Lida com casos especiais como sexta-feira e fim de semana,
 * ajustando para mostrar as aulas do próximo dia útil.
 */
function getAulasAmanha($horarios) {
    // Pega o dia atual (1-7, onde 1 é segunda)
    $hoje = date('N');
    $amanha = $hoje + 1;
    
    // Se for sexta (5), ajusta para segunda (2)
    // Se for final de semana (6 ou 7), ajusta para segunda (2)
    if ($hoje >= 5) {
        $amanha = 2;
    }
    
    // Converte para o formato do SUAP (2-6)
    // No SUAP: 2=Segunda, 3=Terça, 4=Quarta, 5=Quinta, 6=Sexta
    $amanha = $amanha + 1;
    
    $aulasAmanha = [];
    
    foreach ($horarios as $disciplina) {
        if (!empty($disciplina['horarios_de_aula'])) {
            $horariosArray = parseHorario($disciplina['horarios_de_aula']);
            foreach ($horariosArray as $h) {
                if ($h['dia'] == $amanha) {
                    $aulasAmanha[] = [
                        'disciplina' => $disciplina['sigla'],
                        'nome' => $disciplina['descricao'],
                        'local' => $disciplina['locais_de_aula'][0] ?? '',
                        'horario' => $h
                    ];
                }
            }
        }
    }
    
    return $aulasAmanha;
}

/**
 * Ordena as aulas cronologicamente e adiciona detalhes dos horários
 * 
 * @param array $aulasAmanha Array de aulas a serem ordenadas
 * @return array Aulas ordenadas com horários detalhados
 */
function ordenarAulasPorHorario($aulasAmanha) {
    $horarios = [
        'M1' => ['turno' => 'M', 'aula' => '1', 'hora' => '07:00 - 07:45'],
        'M2' => ['turno' => 'M', 'aula' => '2', 'hora' => '07:45 - 08:30'],
        'M3' => ['turno' => 'M', 'aula' => '3', 'hora' => '08:50 - 09:35'],
        'M4' => ['turno' => 'M', 'aula' => '4', 'hora' => '09:35 - 10:20'],
        'M5' => ['turno' => 'M', 'aula' => '5', 'hora' => '10:30 - 11:15'],
        'M6' => ['turno' => 'M', 'aula' => '6', 'hora' => '11:15 - 12:00'],
        'T1' => ['turno' => 'T', 'aula' => '1', 'hora' => '13:00 - 13:45'],
        'T2' => ['turno' => 'T', 'aula' => '2', 'hora' => '13:45 - 14:30'],
        'T3' => ['turno' => 'T', 'aula' => '3', 'hora' => '14:50 - 15:35'],
        'T4' => ['turno' => 'T', 'aula' => '4', 'hora' => '15:35 - 16:20'],
        'T5' => ['turno' => 'T', 'aula' => '5', 'hora' => '16:30 - 17:15'],
        'T6' => ['turno' => 'T', 'aula' => '6', 'hora' => '17:15 - 18:00']
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

    usort($aulasOrdenadas, function($a, $b) {
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
function podeFaltarAmanha($disciplina) {
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
function calcularImpactoFalta($disciplina) {
    if (!isset($disciplina['percentual_carga_horaria_frequentada'])) {
        return null;
    }
    
    $frequenciaAtual = $disciplina['percentual_carga_horaria_frequentada'];
    $totalAulas = $disciplina['carga_horaria_cumprida'];
    $totalFaltas = $disciplina['numero_faltas'];
    
    if ($totalAulas == 0) return null;
    
    // Calcula a nova frequência considerando mais uma falta
    $novaFrequencia = (($totalAulas - $totalFaltas - 1) / $totalAulas) * 100;
    
    return [
        'atual' => $frequenciaAtual,
        'nova' => max(0, $novaFrequencia),
        'impacto' => $frequenciaAtual - $novaFrequencia,
        'faltas_atuais' => $totalFaltas,
        'maximo_faltas' => ceil($disciplina['carga_horaria'] * 0.25)
    ];
}

// Carregamento inicial dos dados
$meusDados = getSuapData("minhas-informacoes/meus-dados/");
$anoLetivo = date('Y');
$periodoLetivo = '1'; // TODO: Implementar detecção automática do período

if ($meusDados && isset($meusDados['matricula'])) {
    $boletim = getSuapData("minhas-informacoes/boletim/{$anoLetivo}/{$periodoLetivo}/");
    $horarios = getSuapData("minhas-informacoes/turmas-virtuais/{$anoLetivo}/{$periodoLetivo}/");
}

// Preparação dos dados para a view
$diasSemana = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
$hoje = date('N');
$amanha = ($hoje >= 5) ? 2 : $hoje + 1;
$diaAmanha = $diasSemana[$amanha];

// Configuração da página
$pageTitle = 'Dashboard - IF calc';
ob_start(); // Inicia o buffer de saída
?>

<div class="container mt-4">
    <!-- Hero Section Moderno -->
    <div class="card border-0 mb-4 overflow-hidden bg-primary">
        <div class="hero-wrapper position-relative">
            <!-- Background com gradiente e padrão -->
            <div class="hero-bg position-absolute w-100 h-100" 
                 style="background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
                        background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.15) 1px, transparent 0);
                        background-size: 20px 20px;">
            </div>
            
            <!-- Conteúdo do Hero -->
            <div class="card-body position-relative py-5 text-white">
                <div class="row align-items-center">
                    <!-- Coluna da Logo -->
                    <div class="col-lg-3 text-center mb-4 mb-lg-0">
                        <div class="logo-container bg-white rounded-4 p-3 d-inline-block shadow-lg">
                            <img src="assets/logo.png" 
                                 alt="SUPACO Logo" 
                                 class="img-fluid rounded-4" 
                                 style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                    </div>
                    
                    <!-- Coluna do Conteúdo -->
                    <div class="col-lg-9 text-white">
                        <div class="text-shadow">
                            <h1 class="display-4 fw-bold mb-2">
                                SUPACO
                                <span class="badge bg-white text-primary fs-6 align-middle ms-2">
                                    Beta
                                </span>
                            </h1>
                            <h2 class="h3 mb-3">
                                Sistema Útil Pra Aluno Cansado e Ocupado
                            </h2>
                            <p class="lead mb-4">
                                <i class="fas fa-quote-left fa-sm me-2"></i>
                                Porque até super-heróis precisam de uma ajudinha para sobreviver ao semestre!
                                <i class="fas fa-quote-right fa-sm ms-2"></i>
                            </p>
                            
                            <!-- Status do Usuário -->
                            <?php if (isset($_SESSION['access_token']) && isset($meusDados)): ?>
                                <div class="d-flex align-items-center bg-white bg-opacity-25 backdrop-blur rounded-3 p-3 hero-user-info">
                                    <?php if (isset($meusDados['url_foto_150x200'])): ?>
                                        <img src="<?php echo htmlspecialchars($meusDados['url_foto_150x200']); ?>" 
                                             class="rounded-circle border border-3 border-white shadow" 
                                             style="width: 60px; height: 60px; object-fit: cover;">
                                    <?php endif; ?>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-2">
                                            <?php echo htmlspecialchars($meusDados['nome_usual']); ?>
                                        </h6>
                                        <div class="badges-wrapper">
                                            <span class="badge bg-white bg-opacity-25">
                                                <i class="fas fa-id-card me-1"></i>
                                                <?php echo htmlspecialchars($meusDados['matricula']); ?>
                                            </span>
                                            <span class="badge bg-white bg-opacity-25">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo $anoLetivo; ?>.<?php echo $periodoLetivo; ?>
                                            </span>
                                            <a href="logout.php" class="btn btn-sm btn-light">
                                                <i class="fas fa-sign-out-alt"></i> Sair
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert bg-white bg-opacity-25 backdrop-blur border-0 text-white d-inline-flex align-items-center" role="alert">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Faça login com suas credenciais do SUAP para começar
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Seção de Aulas de Amanhã -->
    <div class="row mb-4 animate-fade-in-up" style="animation-delay: 0.2s">
        <div class="col-md-8">
            <div class="card h-100 shadow-sm glass-effect">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-day me-2"></i>
                                Aulas de Amanhã
                            </h5>
                            <span class="badge bg-white text-primary ms-3">
                                <?php echo $diaAmanha; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    $aulasAmanha = getAulasAmanha($horarios);
                    if (!empty($aulasAmanha)):
                        $aulasOrdenadas = ordenarAulasPorHorario($aulasAmanha);
                    ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($aulasOrdenadas as $aula): ?>
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div>
                                            <div class="d-flex align-items-center">
                                                <h6 class="mb-1 text-primary me-2">
                                                    <?php echo htmlspecialchars($aula['disciplina']); ?>
                                                </h6>
                                                <span class="badge bg-light text-dark">
                                                    <?php echo $aula['horario_detalhado']; ?>
                                                </span>
                                            </div>
                                            <p class="mb-1"><?php echo htmlspecialchars($aula['nome']); ?></p>
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
                            Não há aulas programadas para amanhã
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4 impact-section">
            <div class="card h-100 shadow-sm glass-effect">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-percentage me-2"></i>
                        Impacto na Frequência
                    </h5>
                </div>
                <div class="card-body text-center">
                    <?php if (!empty($aulasAmanha)): ?>
                        <div class="text-center mb-3">
                            <?php
                            $statusGeral = 'success';
                            foreach ($aulasAmanha as $aula) {
                                foreach ($boletim as $disciplina) {
                                    if (strpos($disciplina['disciplina'], $aula['disciplina']) !== false) {
                                        $status = podeFaltarAmanha($disciplina);
                                        if ($status === 'danger') {
                                            $statusGeral = 'danger';
                                            break 2;
                                        } else if ($status === 'warning' && $statusGeral !== 'danger') {
                                            $statusGeral = 'warning';
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
                            ?>
                            
                            <img src="assets/<?php echo $imagemStatus[$statusGeral]; ?>" 
                                 alt="Status de frequência"
                                 class="impact-image mb-4"
                                 style="height: 100px; width: auto;">
                            <h5 class="impact-status mb-4 <?php echo $corStatus[$statusGeral]; ?>">
                                <?php echo $mensagemStatus[$statusGeral]; ?>
                            </h5>
                        </div>
                        
                        <div class="list-group">
                            <?php foreach ($aulasAmanha as $aula): ?>
                                <?php
                                foreach ($boletim as $disciplina):
                                    if (strpos($disciplina['disciplina'], $aula['disciplina']) !== false):
                                        $impacto = calcularImpactoFalta($disciplina);
                                        if ($impacto):
                                            // Extrai o nome da disciplina sem o código
                                            preg_match('/^[^-]+ - (.+?)(\(\d+H\))?$/', $disciplina['disciplina'], $matches);
                                            $nomeDisciplina = $matches[1] ?? $aula['disciplina'];
                                ?>
                                    <div class="list-group-item">
                                        <h6 class="mb-1"><?php echo htmlspecialchars(trim($nomeDisciplina)); ?></h6>
                                        <div class="small">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>Frequência atual: <?php echo number_format($impacto['atual'], 1); ?>%</div>
                                                <div>Faltas: <?php echo $impacto['faltas_atuais']; ?>/<?php echo $impacto['maximo_faltas']; ?></div>
                                            </div>
                                            <div class="mt-1">
                                                <div>Após falta: <?php echo number_format($impacto['nova'], 1); ?>%</div>
                                                <div class="text-<?php echo podeFaltarAmanha($disciplina); ?> small">
                                                    <?php if ($impacto['faltas_atuais'] >= $impacto['maximo_faltas']): ?>
                                                        Limite de faltas atingido!
                                                    <?php elseif (($impacto['maximo_faltas'] - $impacto['faltas_atuais']) <= 3): ?>
                                                        Restam apenas <?php echo $impacto['maximo_faltas'] - $impacto['faltas_atuais']; ?> faltas!
                                                    <?php else: ?>
                                                        Impacto: -<?php echo number_format($impacto['impacto'], 1); ?>%
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                        endif;
                                    endif;
                                endforeach;
                                ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">
                            Não há aulas amanhã para analisar o impacto na frequência.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Resto do conteúdo permanece igual -->
    <div class="animate-fade-in-up" style="animation-delay: 0.4s">
        <h3 class="mb-4">Boletim <?php echo $anoLetivo; ?>.<?php echo $periodoLetivo; ?></h3>
        
        <?php if (isset($boletim) && is_array($boletim)): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Disciplina</th>
                            <th>Nota 1</th>
                            <th>Nota 2</th>
                            <th>Nota 3</th>
                            <th>Nota 4</th>
                            <th>Média</th>
                            <th>Freq. (%)</th>
                            <th>Situação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($boletim as $disciplina): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($disciplina['disciplina']); ?></td>
                                <td>
                                    <?php if (isset($disciplina['nota_etapa_1']['nota'])): ?>
                                        <?php echo $disciplina['nota_etapa_1']['nota']; ?>
                                    <?php else: ?>
                                        <div class="nota-simulacao">
                                            <input type="number" 
                                                   class="form-control form-control-sm nota-input" 
                                                   style="width: 70px" 
                                                   min="0" 
                                                   max="100" 
                                                   step="0.1"
                                                   data-disciplina="<?php echo htmlspecialchars($disciplina['disciplina']); ?>"
                                                   data-etapa="1"
                                                   placeholder="<?php echo number_format(calcularNotaNecessaria($disciplina), 1); ?>">
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($disciplina['nota_etapa_2']['nota'])): ?>
                                        <?php echo $disciplina['nota_etapa_2']['nota']; ?>
                                    <?php else: ?>
                                        <div class="nota-simulacao">
                                            <input type="number" 
                                                   class="form-control form-control-sm nota-input" 
                                                   style="width: 70px" 
                                                   min="0" 
                                                   max="100" 
                                                   step="0.1"
                                                   data-disciplina="<?php echo htmlspecialchars($disciplina['disciplina']); ?>"
                                                   data-etapa="2"
                                                   placeholder="<?php echo number_format(calcularNotaNecessaria($disciplina), 1); ?>">
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($disciplina['nota_etapa_3']['nota'])): ?>
                                        <?php echo $disciplina['nota_etapa_3']['nota']; ?>
                                    <?php else: ?>
                                        <div class="nota-simulacao">
                                            <input type="number" 
                                                   class="form-control form-control-sm nota-input" 
                                                   style="width: 70px" 
                                                   min="0" 
                                                   max="100" 
                                                   step="0.1"
                                                   data-disciplina="<?php echo htmlspecialchars($disciplina['disciplina']); ?>"
                                                   data-etapa="3"
                                                   placeholder="<?php echo number_format(calcularNotaNecessaria($disciplina), 1); ?>">
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (isset($disciplina['nota_etapa_4']['nota'])): ?>
                                        <?php echo $disciplina['nota_etapa_4']['nota']; ?>
                                    <?php else: ?>
                                        <div class="nota-simulacao">
                                            <input type="number" 
                                                   class="form-control form-control-sm nota-input" 
                                                   style="width: 70px" 
                                                   min="0" 
                                                   max="100" 
                                                   step="0.1"
                                                   data-disciplina="<?php echo htmlspecialchars($disciplina['disciplina']); ?>"
                                                   data-etapa="4"
                                                   placeholder="<?php echo number_format(calcularNotaNecessaria($disciplina), 1); ?>">
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo isset($disciplina['media_final_disciplina']) ? $disciplina['media_final_disciplina'] : '-'; ?></td>
                                <td>
                                    <?php 
                                    $frequencia = isset($disciplina['percentual_carga_horaria_frequentada']) 
                                        ? number_format($disciplina['percentual_carga_horaria_frequentada'], 1) 
                                        : '-';
                                    $frequenciaClass = $frequencia < 75 && $frequencia != '-' ? 'text-danger' : '';
                                    echo "<span class='{$frequenciaClass}'>{$frequencia}</span>";
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($disciplina['situacao']); ?></td>
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

<!-- Adicione estes estilos ao arquivo base.php -->
<style>
.nota-simulacao {
    display: flex;
    align-items: center;
    justify-content: center;
}

.nota-input {
    text-align: center;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.nota-input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(26, 115, 232, 0.25);
}

.nota-input::placeholder {
    color: #ffc107;
    opacity: 1;
}

/* Ajustes de responsividade */
@media (max-width: 768px) {
    .user-status {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .user-status .badge {
        font-size: 0.8rem;
    }
    
    .user-status img {
        width: 45px;
        height: 45px;
    }

    .impact-section {
        margin-top: 1.5rem !important;
    }

    .impact-image {
        width: auto;
        height: 120px !important;
        margin: 1rem auto;
    }

    .impact-status {
        font-size: 1.1rem;
        margin: 1rem 0;
    }

    /* Ajuste para hero em mobile */
    .hero-user-info {
        padding: 0.8rem !important;
        gap: 0.5rem;
        flex-direction: column;
        align-items: flex-start !important;
    }

    .hero-user-info .badges-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        width: 100%;
    }
}
</style>

<?php
$pageContent = ob_get_clean(); // Captura o conteúdo do buffer
require_once 'base.php'; // Inclui o template base
?>
