<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'SUPACO'; ?></title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 (para utilitários apenas) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome Pro para ícones modernos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <!-- Dark Theme CSS -->
    <link href="assets/css/dark-theme.css" rel="stylesheet">
    
    <!-- Aulas Section CSS -->
    <link href="assets/css/aulas-section.css" rel="stylesheet">
    
    <!-- Boletim Modern CSS -->
    <link href="assets/css/boletim-modern.css" rel="stylesheet">
    
    <!-- Horários Styles CSS -->
    <link href="assets/css/horarios-styles.css" rel="stylesheet">
    
    <!-- Dashboard CSS -->
    <link href="assets/css/dashboard.css" rel="stylesheet">

    <style>
        /* Tema escuro global override Bootstrap */
        :root {
            --bg-black: #0F1116;
            --bg-zinc-900: #1A1D24;
            --bg-zinc-800: #2A2D36;
            --bg-zinc-700: #3f3f46;
            --text-white: #FFFFFF;
            --text-zinc-300: #D1D5E0;
            --text-zinc-400: #A0A3B1;
            --text-zinc-500: #71717a;
            --emerald-400: #22C55E;
            --emerald-500: #22C55E;
            --red-400: #EF4444;
            --red-500: #EF4444;
            --blue-400: #3B82F6;
            --border-zinc-800: #2A2D36;
        }

        body {
            background-color: var(--bg-black) !important;
            color: var(--text-white) !important;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif !important;
            overflow-x: hidden;
        }

        /* Override Bootstrap dark theme */
        .card {
            background-color: rgba(39, 39, 42, 0.5) !important;
            border: 1px solid var(--border-zinc-800) !important;
            border-radius: 1rem !important;
            color: var(--text-white) !important;
        }

        .table {
            background-color: rgba(39, 39, 42, 0.5) !important;
            color: var(--text-white) !important;
        }

        .table thead th {
            background-color: var(--bg-zinc-700) !important;
            border-color: var(--border-zinc-800) !important;
            color: var(--text-white) !important;
        }

        .table td,
        .table th {
            border-color: var(--border-zinc-800) !important;
        }

        .table tbody tr:hover {
            background-color: rgba(39, 39, 42, 0.8) !important;
        }

        .btn-primary {
            background-color: var(--blue-400) !important;
            border-color: var(--blue-400) !important;
        }

        .btn-outline-primary {
            color: var(--blue-400) !important;
            border-color: var(--blue-400) !important;
        }

        .btn-outline-primary:hover {
            background-color: var(--blue-400) !important;
            border-color: var(--blue-400) !important;
        }

        .text-primary {
            color: var(--blue-400) !important;
        }

        .bg-primary {
            background-color: var(--blue-400) !important;
        }

        .text-success {
            color: var(--emerald-400) !important;
        }

        .bg-success {
            background-color: var(--emerald-500) !important;
        }

        .text-danger {
            color: var(--red-400) !important;
        }

        .bg-danger {
            background-color: var(--red-500) !important;
        }

        .alert-warning {
            background-color: rgba(251, 191, 36, 0.1) !important;
            border-color: #fbbf24 !important;
            color: #fbbf24 !important;
        }

        /* Scrollbar customization */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-zinc-800);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--bg-zinc-700);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-zinc-500);
        }

        /* Navbar dark theme */
        .navbar {
            background-color: rgba(0, 0, 0, 0.9) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-zinc-800);
        }

        .navbar-brand,
        .nav-link {
            color: var(--text-white) !important;
        }

        .nav-link:hover {
            color: var(--blue-400) !important;
        }

        /* Modal dark theme */
        .modal-content {
            background-color: var(--bg-zinc-900) !important;
            border: 1px solid var(--border-zinc-800) !important;
        }

        .modal-header {
            border-bottom: 1px solid var(--border-zinc-800) !important;
        }

        .modal-footer {
            border-top: 1px solid var(--border-zinc-800) !important;
        }

        /* Form controls dark theme */
        .form-control {
            background-color: var(--bg-zinc-800) !important;
            border-color: var(--border-zinc-800) !important;
            color: var(--text-white) !important;
        }

        .form-control:focus {
            background-color: var(--bg-zinc-700) !important;
            border-color: var(--blue-400) !important;
            color: var(--text-white) !important;
            box-shadow: 0 0 0 0.2rem rgba(96, 165, 250, 0.25) !important;
        }

        /* Badge dark theme */
        .badge {
            background-color: var(--bg-zinc-700) !important;
            color: var(--text-white) !important;
        }

        .badge.bg-light {
            background-color: var(--bg-zinc-600) !important;
            color: var(--text-white) !important;
        }

        /* API Debug Modal Styles */
        .api-data-container {
            background-color: var(--bg-zinc-800);
            border: 1px solid var(--border-zinc-800);
            border-radius: 0.5rem;
            max-height: 500px;
            overflow: auto;
        }

        .api-data-content {
            padding: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .api-data-content pre {
            margin: 0;
            background: transparent;
            color: var(--text-white);
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .api-data-content .json-key {
            color: #79C0FF;
            font-weight: bold;
        }

        .api-data-content .json-string {
            color: #A5D6FF;
        }

        .api-data-content .json-number {
            color: #FFA657;
        }

        .api-data-content .json-boolean {
            color: #FF7B72;
        }

        .api-data-content .json-null {
            color: #8B949E;
        }

        .api-data-content .json-punctuation {
            color: var(--text-zinc-400);
        }

        .api-tree-item {
            margin-left: 1rem;
            border-left: 1px solid var(--border-zinc-800);
            padding-left: 0.5rem;
        }

        .api-tree-toggle {
            cursor: pointer;
            color: var(--blue-400);
            user-select: none;
        }

        .api-tree-toggle:hover {
            color: var(--blue-300);
        }

        .api-table {
            width: 100%;
            font-size: 0.875rem;
        }

        .api-table th {
            background-color: var(--bg-zinc-700);
            color: var(--text-white);
            padding: 0.5rem;
            border: 1px solid var(--border-zinc-800);
        }

        .api-table td {
            padding: 0.5rem;
            border: 1px solid var(--border-zinc-800);
            color: var(--text-zinc-300);
        }

        .api-table tr:nth-child(even) {
            background-color: rgba(39, 39, 42, 0.3);
        }

        /* API Calls Info Styles */
        .api-calls-info {
            background-color: var(--bg-zinc-800);
            border: 1px solid var(--border-zinc-800);
            border-radius: 0.5rem;
            padding: 1rem;
        }

        .api-calls-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .api-call-item {
            background-color: var(--bg-zinc-700);
            border: 1px solid var(--border-zinc-800);
            border-radius: 0.375rem;
            padding: 0.75rem;
        }

        .api-call-method {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
        }

        .api-call-method code {
            background-color: var(--bg-zinc-900);
            color: var(--text-white);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            border: 1px solid var(--border-zinc-800);
        }

        .api-call-description {
            margin-left: 0.25rem;
        }

        .badge.bg-success {
            background-color: var(--emerald-500) !important;
        }

        .badge.bg-info {
            background-color: var(--blue-400) !important;
        }

        .badge.bg-warning {
            background-color: #f59e0b !important;
            color: #000 !important;
        }
    </style>
</head>

<body>
    <!-- Navbar simples (opcional) -->
    <?php if (isset($_SESSION['access_token'])): ?>
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand fw-bold" href="index.php">
                    <i class="fas fa-graduation-cap me-2"></i>
                    SUPACO
                </a>
                <div class="navbar-nav ms-auto">
                    <button class="nav-link btn btn-link" onclick="abrirModalAPI()" style="border: none; background: none; color: inherit;">
                        <i class="fas fa-code me-1"></i> Debug API
                    </button>
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-home me-1"></i> Dashboard
                    </a>
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt me-1"></i> Sair
                    </a>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    <!-- Conteúdo principal -->
    <main>
        <?php if (isset($pageContent)) echo $pageContent; ?>
    </main>

    <!-- Modal Debug API -->
    <div class="modal fade" id="modalDebugAPI" tabindex="-1" aria-labelledby="modalDebugAPILabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="modalDebugAPILabel">
                            <i class="fas fa-code me-2"></i>
                            Debug API - Dados do SUAP
                        </h5>
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i>
                            Modal de transparência - Não guardamos nenhum dado seu, tudo está apenas em seu computador
                        </small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <div class="btn-group" role="group" aria-label="Selecionar dados">
                                    <input type="radio" class="btn-check" name="apiDataType" id="meusDados" value="meusDados" checked>
                                    <label class="btn btn-outline-primary" for="meusDados">
                                        <i class="fas fa-user me-1"></i> Meus Dados
                                    </label>

                                    <input type="radio" class="btn-check" name="apiDataType" id="boletim" value="boletim">
                                    <label class="btn btn-outline-primary" for="boletim">
                                        <i class="fas fa-chart-line me-1"></i> Boletim
                                    </label>

                                    <input type="radio" class="btn-check" name="apiDataType" id="horarios" value="horarios">
                                    <label class="btn btn-outline-primary" for="horarios">
                                        <i class="fas fa-clock me-1"></i> Horários
                                    </label>

                                    <input type="radio" class="btn-check" name="apiDataType" id="todos" value="todos">
                                    <label class="btn btn-outline-primary" for="todos">
                                        <i class="fas fa-list me-1"></i> Todos
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Formato de exibição:</span>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <input type="radio" class="btn-check" name="displayFormat" id="jsonFormat" value="json" checked>
                                        <label class="btn btn-outline-secondary" for="jsonFormat">JSON</label>

                                        <input type="radio" class="btn-check" name="displayFormat" id="tableFormat" value="table">
                                        <label class="btn btn-outline-secondary" for="tableFormat">Tabela</label>

                                        <input type="radio" class="btn-check" name="displayFormat" id="treeFormat" value="tree">
                                        <label class="btn btn-outline-secondary" for="treeFormat">Árvore</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="api-calls-info">
                                    <h6 class="text-primary mb-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Chamadas da API Realizadas:
                                    </h6>
                                    <div class="api-calls-list">
                                        <div class="api-call-item">
                                            <div class="api-call-method">
                                                <span class="badge bg-success">GET</span>
                                                <code><?php echo SUAP_URL; ?>/api/v2/minhas-informacoes/meus-dados/</code>
                                            </div>
                                            <div class="api-call-description">
                                                <small class="text-muted">Dados pessoais e acadêmicos do usuário</small>
                                            </div>
                                        </div>
                                        
                                        <div class="api-call-item">
                                            <div class="api-call-method">
                                                <span class="badge bg-info">GET</span>
                                                <code><?php echo SUAP_URL; ?>/api/v2/minhas-informacoes/boletim/<?php echo $anoLetivo; ?>/<?php echo $periodoLetivo; ?>/</code>
                                            </div>
                                            <div class="api-call-description">
                                                <small class="text-muted">Boletim acadêmico do período <?php echo $anoLetivo; ?>.<?php echo $periodoLetivo; ?></small>
                                            </div>
                                        </div>
                                        
                                        <div class="api-call-item">
                                            <div class="api-call-method">
                                                <span class="badge bg-warning">GET</span>
                                                <code><?php echo SUAP_URL; ?>/api/v2/minhas-informacoes/turmas-virtuais/<?php echo $anoLetivo; ?>/<?php echo $periodoLetivo; ?>/</code>
                                            </div>
                                            <div class="api-call-description">
                                                <small class="text-muted">Horários e turmas do período <?php echo $anoLetivo; ?>.<?php echo $periodoLetivo; ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="api-data-container">
                                <div id="apiDataContent" class="api-data-content">
                                    <div class="text-center text-muted py-5">
                                        <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                                        <p>Carregando dados da API...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="me-auto">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Dados carregados diretamente do SUAP via API oficial
                        </small>
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="copiarDados()">
                        <i class="fas fa-copy me-1"></i> Copiar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="downloadDados()">
                        <i class="fas fa-download me-1"></i> Download
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        // Inicialização básica
        document.addEventListener('DOMContentLoaded', function() {
            // Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Animações simples para os cards
            const cards = document.querySelectorAll('.aula-card, .stat-card, .main-status-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Dados das disciplinas para o simulador
        const disciplinasData = <?php
                                if (isset($boletim) && is_array($boletim)) {
                                    echo json_encode(array_map(function ($disciplina, $index) {
                                        return [
                                            'index' => $index,
                                            'disciplina' => $disciplina,
                                            'calculo' => calcularNotaNecessariaIF($disciplina)
                                        ];
                                    }, $boletim, array_keys($boletim)));
                                } else {
                                    echo '[]';
                                }
                                ?>;

        let disciplinaAtual = null;

        function abrirSimulador(index, nome) {
            disciplinaAtual = disciplinasData.find(d => d.index === index);
            if (!disciplinaAtual) return;

            document.getElementById('disciplinaNome').textContent = nome;

            // Preenche valores atuais
            const n1Input = document.getElementById('simN1');
            const n2Input = document.getElementById('simN2');

            if (disciplinaAtual.calculo.n1 !== null) {
                n1Input.value = disciplinaAtual.calculo.n1;
            } else {
                n1Input.value = '';
            }

            if (disciplinaAtual.calculo.n2 !== null) {
                n2Input.value = disciplinaAtual.calculo.n2;
            } else {
                n2Input.value = '';
            }

            // Limpa resultado anterior
            document.getElementById('resultadoSimulacao').innerHTML = '';

            // Abre o modal
            new bootstrap.Modal(document.getElementById('simuladorModal')).show();
        }

        function calcularSimulacao() {
            const n1 = parseFloat(document.getElementById('simN1').value) || null;
            const n2 = parseFloat(document.getElementById('simN2').value) || null;

            if (n1 === null && n2 === null) {
                alert('Preencha pelo menos uma nota para simular!');
                return;
            }

            // Calcula a simulação
            const resultado = calcularMediaIF(n1, n2);
            exibirResultadoSimulacao(resultado, n1, n2);
        }

        function calcularMediaIF(n1, n2) {
            const resultado = {
                n1: n1,
                n2: n2,
                media_atual: null,
                nota_necessaria: null,
                situacao: 'indefinida',
                pode_passar_direto: false,
                precisa_af: false,
                ja_aprovado: false,
                ja_reprovado: false
            };

            // Se tem as duas notas, calcula a média final
            if (n1 !== null && n2 !== null) {
                const md = (2 * n1 + 3 * n2) / 5;
                resultado.media_atual = md;

                if (md >= 60) {
                    resultado.situacao = 'aprovado_direto';
                    resultado.ja_aprovado = true;
                    resultado.pode_passar_direto = true;
                } else if (md >= 20) {
                    resultado.situacao = 'avaliacao_final';
                    resultado.precisa_af = true;
                } else {
                    resultado.situacao = 'reprovado_nota';
                    resultado.ja_reprovado = true;
                }
                return resultado;
            }

            // Se só tem N1, calcula o que precisa no N2
            if (n1 !== null && n2 === null) {
                const nota_necessaria = Math.max(0, Math.min(100, (300 - 2 * n1) / 3));
                resultado.nota_necessaria = nota_necessaria;
                resultado.situacao = 'aguardando_n2';

                if (nota_necessaria <= 100) {
                    resultado.pode_passar_direto = true;
                }

                return resultado;
            }

            // Se só tem N2, calcula o que precisaria no N1
            if (n1 === null && n2 !== null) {
                const nota_necessaria = Math.max(0, Math.min(100, (300 - 3 * n2) / 2));
                resultado.nota_necessaria = nota_necessaria;
                resultado.situacao = 'aguardando_n1';

                return resultado;
            }

            resultado.situacao = 'aguardando_notas';
            return resultado;
        }

        function calcularAvaliacaoFinalJS(n1, n2) {
            const md = (2 * n1 + 3 * n2) / 5;

            // As 3 fórmulas para MFD >= 60
            const naf1 = 120 - md;
            const naf2 = (300 - 3 * n2) / 2;
            const naf3 = (300 - 2 * n1) / 3;

            const naf_necessaria = Math.max(0, Math.min(100, Math.min(naf1, naf2, naf3)));

            return {
                md: md,
                naf_necessaria: naf_necessaria,
                formula_1: Math.max(0, Math.min(100, naf1)),
                formula_2: Math.max(0, Math.min(100, naf2)),
                formula_3: Math.max(0, Math.min(100, naf3)),
                melhor_opcao: naf_necessaria,
                pode_passar: naf_necessaria <= 100
            };
        }

        function exibirResultadoSimulacao(resultado, n1, n2) {
            let html = '<div class="alert alert-info">';

            html += '<h6><i class="fas fa-calculator"></i> Resultado da Simulação:</h6>';

            if (resultado.media_atual !== null) {
                const corMedia = resultado.media_atual >= 60 ? 'success' : (resultado.media_atual >= 20 ? 'warning' : 'danger');
                html += `<p><strong>Média Direta (MD):</strong> <span class="text-${corMedia}">${resultado.media_atual.toFixed(1)}</span></p>`;

                if (resultado.ja_aprovado) {
                    html += '<p class="text-success"><i class="fas fa-check-circle"></i> <strong>APROVADO DIRETO!</strong></p>';
                } else if (resultado.precisa_af) {
                    const af = calcularAvaliacaoFinalJS(n1, n2);
                    html += `<p class="text-warning"><i class="fas fa-exclamation-triangle"></i> <strong>AVALIAÇÃO FINAL</strong></p>`;
                    html += `<p>Nota necessária na AF: <strong>${af.naf_necessaria.toFixed(1)}</strong></p>`;
                    html += '<small class="text-muted">Calculado pela melhor das 3 fórmulas do IF</small>';
                } else {
                    html += '<p class="text-danger"><i class="fas fa-times-circle"></i> <strong>REPROVADO</strong></p>';
                }
            } else if (resultado.nota_necessaria !== null) {
                const bimestre = n1 === null ? 'N1' : 'N2';
                const cor = resultado.nota_necessaria <= 100 ? 'info' : 'danger';
                html += `<p>Para ser aprovado direto, você precisa tirar <strong class="text-${cor}">${resultado.nota_necessaria.toFixed(1)}</strong> no <strong>${bimestre}</strong></p>`;

                if (resultado.nota_necessaria > 100) {
                    html += '<p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Nota impossível! Você precisará da Avaliação Final.</p>';
                }
            }

            html += '</div>';

            // Explicação do sistema
            html += '<div class="alert alert-secondary mt-2">';
            html += '<small><strong>Sistema IF:</strong> MD = (2×N1 + 3×N2) ÷ 5<br>';
            html += 'Aprovação direta: MD ≥ 60 | Avaliação Final: 20 ≤ MD < 60 | Reprovação: MD < 20</small>';
            html += '</div>';

            document.getElementById('resultadoSimulacao').innerHTML = html;
        }

        // Auto-cálculo quando valores mudam
        document.addEventListener('DOMContentLoaded', function() {
            const simN1 = document.getElementById('simN1');
            const simN2 = document.getElementById('simN2');

            if (simN1 && simN2) {
                simN1.addEventListener('input', calcularSimulacao);
                simN2.addEventListener('input', calcularSimulacao);
            }
        });

        // Função para mostrar/ocultar detalhes das aulas
        function toggleAulasDetails() {
            const detailsElement = document.getElementById('aulasDetails');
            const button = document.querySelector('.btn-details-toggle');
            
            if (detailsElement.style.display === 'none') {
                detailsElement.style.display = 'block';
                button.innerHTML = '<i class="fas fa-eye-slash"></i><span>Ocultar</span>';
            } else {
                detailsElement.style.display = 'none';
                button.innerHTML = '<i class="fas fa-info-circle"></i><span>Detalhes</span>';
            }
        }

        // Dados da API para debug
        const apiDebugData = <?php
            if (isset($apiResponses)) {
                echo json_encode($apiResponses, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            } else {
                echo '{}';
            }
        ?>;

        // Informações das chamadas da API
        const apiCallsInfo = {
            meusDados: {
                method: 'GET',
                endpoint: '<?php echo SUAP_URL; ?>/api/v2/minhas-informacoes/meus-dados/',
                description: 'Dados pessoais e acadêmicos do usuário',
                headers: {
                    'Authorization': 'Bearer [TOKEN_OCULTO]',
                    'Content-Type': 'application/json'
                },
                parameters: {}
            },
            boletim: {
                method: 'GET',
                endpoint: '<?php echo SUAP_URL; ?>/api/v2/minhas-informacoes/boletim/<?php echo $anoLetivo; ?>/<?php echo $periodoLetivo; ?>/',
                description: 'Boletim acadêmico do período <?php echo $anoLetivo; ?>.<?php echo $periodoLetivo; ?>',
                headers: {
                    'Authorization': 'Bearer [TOKEN_OCULTO]',
                    'Content-Type': 'application/json'
                },
                parameters: {
                    'ano': '<?php echo $anoLetivo; ?>',
                    'periodo': '<?php echo $periodoLetivo; ?>'
                }
            },
            horarios: {
                method: 'GET',
                endpoint: '<?php echo SUAP_URL; ?>/api/v2/minhas-informacoes/turmas-virtuais/<?php echo $anoLetivo; ?>/<?php echo $periodoLetivo; ?>/',
                description: 'Horários e turmas do período <?php echo $anoLetivo; ?>.<?php echo $periodoLetivo; ?>',
                headers: {
                    'Authorization': 'Bearer [TOKEN_OCULTO]',
                    'Content-Type': 'application/json'
                },
                parameters: {
                    'ano': '<?php echo $anoLetivo; ?>',
                    'periodo': '<?php echo $periodoLetivo; ?>'
                }
            }
        };

        // Função para abrir o modal de debug da API
        function abrirModalAPI() {
            const modal = new bootstrap.Modal(document.getElementById('modalDebugAPI'));
            modal.show();
            
            // Carrega os dados iniciais
            carregarDadosAPI();
            
            // Adiciona event listeners para mudanças
            document.querySelectorAll('input[name="apiDataType"]').forEach(input => {
                input.addEventListener('change', carregarDadosAPI);
            });
            
            document.querySelectorAll('input[name="displayFormat"]').forEach(input => {
                input.addEventListener('change', carregarDadosAPI);
            });
        }

        // Função para carregar e exibir os dados da API
        function carregarDadosAPI() {
            const dataType = document.querySelector('input[name="apiDataType"]:checked').value;
            const displayFormat = document.querySelector('input[name="displayFormat"]:checked').value;
            const contentDiv = document.getElementById('apiDataContent');
            
            let dataToShow = {};
            
            if (dataType === 'todos') {
                dataToShow = apiDebugData;
            } else if (apiDebugData[dataType]) {
                dataToShow = apiDebugData[dataType];
            } else {
                dataToShow = { erro: 'Dados não encontrados para este tipo' };
            }
            
            // Adiciona informações da chamada da API se disponível
            if (dataType !== 'todos' && apiCallsInfo[dataType]) {
                const callInfo = apiCallsInfo[dataType];
                dataToShow = {
                    '_api_call_info': {
                        method: callInfo.method,
                        endpoint: callInfo.endpoint,
                        description: callInfo.description,
                        headers: callInfo.headers,
                        parameters: callInfo.parameters,
                        timestamp: new Date().toISOString()
                    },
                    ...dataToShow
                };
            }
            
            switch (displayFormat) {
                case 'json':
                    contentDiv.innerHTML = formatarJSON(dataToShow);
                    break;
                case 'table':
                    contentDiv.innerHTML = formatarTabela(dataToShow);
                    break;
                case 'tree':
                    contentDiv.innerHTML = formatarArvore(dataToShow);
                    break;
            }
        }

        // Função para formatar JSON com syntax highlighting
        function formatarJSON(obj) {
            const jsonString = JSON.stringify(obj, null, 2);
            const highlighted = syntaxHighlight(jsonString);
            return `<pre>${highlighted}</pre>`;
        }

        // Função para syntax highlighting do JSON
        function syntaxHighlight(json) {
            return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
                let cls = 'json-number';
                if (/^"/.test(match)) {
                    if (/:$/.test(match)) {
                        cls = 'json-key';
                    } else {
                        cls = 'json-string';
                    }
                } else if (/true|false/.test(match)) {
                    cls = 'json-boolean';
                } else if (/null/.test(match)) {
                    cls = 'json-null';
                }
                return '<span class="' + cls + '">' + match + '</span>';
            }).replace(/([{}[\]])/g, '<span class="json-punctuation">$1</span>');
        }

        // Função para formatar como tabela
        function formatarTabela(obj) {
            if (typeof obj !== 'object' || obj === null) {
                return `<p>Dados não podem ser exibidos em formato de tabela</p>`;
            }
            
            let html = '<table class="api-table"><thead><tr><th>Chave</th><th>Valor</th><th>Tipo</th></tr></thead><tbody>';
            
            function processarObjeto(obj, prefix = '') {
                for (const [key, value] of Object.entries(obj)) {
                    const fullKey = prefix ? `${prefix}.${key}` : key;
                    let displayValue = value;
                    let valueType = typeof value;
                    
                    if (value === null) {
                        displayValue = 'null';
                        valueType = 'null';
                    } else if (Array.isArray(value)) {
                        displayValue = `Array(${value.length})`;
                        valueType = 'array';
                    } else if (typeof value === 'object') {
                        displayValue = 'Object';
                        valueType = 'object';
                    } else if (typeof value === 'string' && value.length > 100) {
                        displayValue = value.substring(0, 100) + '...';
                    }
                    
                    html += `<tr><td>${fullKey}</td><td>${displayValue}</td><td>${valueType}</td></tr>`;
                    
                    if (typeof value === 'object' && value !== null && !Array.isArray(value)) {
                        processarObjeto(value, fullKey);
                    }
                }
            }
            
            processarObjeto(obj);
            html += '</tbody></table>';
            return html;
        }

        // Função para formatar como árvore
        function formatarArvore(obj, level = 0) {
            if (typeof obj !== 'object' || obj === null) {
                return `<span class="json-${typeof obj}">${obj}</span>`;
            }
            
            let html = '';
            const entries = Object.entries(obj);
            
            entries.forEach(([key, value], index) => {
                const isLast = index === entries.length - 1;
                const indent = '  '.repeat(level);
                
                html += `<div class="api-tree-item" style="margin-left: ${level * 20}px;">`;
                html += `<span class="api-tree-toggle" onclick="toggleTreeItem(this)">`;
                html += `<i class="fas fa-chevron-right"></i> `;
                html += `<span class="json-key">"${key}"</span>: `;
                html += `</span>`;
                
                if (typeof value === 'object' && value !== null) {
                    html += `<div class="tree-content" style="display: none;">`;
                    html += formatarArvore(value, level + 1);
                    html += `</div>`;
                } else {
                    html += `<span class="json-${typeof value}">${JSON.stringify(value)}</span>`;
                }
                
                html += `</div>`;
            });
            
            return html;
        }

        // Função para alternar itens da árvore
        function toggleTreeItem(element) {
            const content = element.nextElementSibling;
            const icon = element.querySelector('i');
            
            if (content.style.display === 'none') {
                content.style.display = 'block';
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-down');
            } else {
                content.style.display = 'none';
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-right');
            }
        }

        // Função para copiar dados
        function copiarDados() {
            const dataType = document.querySelector('input[name="apiDataType"]:checked').value;
            let dataToCopy = {};
            
            if (dataType === 'todos') {
                dataToCopy = apiDebugData;
            } else if (apiDebugData[dataType]) {
                dataToCopy = apiDebugData[dataType];
            }
            
            navigator.clipboard.writeText(JSON.stringify(dataToCopy, null, 2)).then(() => {
                // Feedback visual
                const button = event.target.closest('button');
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check me-1"></i> Copiado!';
                button.classList.add('btn-success');
                button.classList.remove('btn-secondary');
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-secondary');
                }, 2000);
            });
        }

        // Função para download dos dados
        function downloadDados() {
            const dataType = document.querySelector('input[name="apiDataType"]:checked').value;
            let dataToDownload = {};
            
            if (dataType === 'todos') {
                dataToDownload = apiDebugData;
            } else if (apiDebugData[dataType]) {
                dataToDownload = apiDebugData[dataType];
            }
            
            const dataStr = JSON.stringify(dataToDownload, null, 2);
            const dataBlob = new Blob([dataStr], { type: 'application/json' });
            const url = URL.createObjectURL(dataBlob);
            
            const link = document.createElement('a');
            link.href = url;
            link.download = `suap-api-${dataType}-${new Date().toISOString().split('T')[0]}.json`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }
    </script>

</body>

</html>
</script>

</body>

</html>