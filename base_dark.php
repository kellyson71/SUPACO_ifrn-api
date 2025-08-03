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
    <link href="assets/dark-theme.css" rel="stylesheet">
    
    <!-- Aulas Section CSS -->
    <link href="assets/aulas-section.css" rel="stylesheet">
    
    <!-- Boletim Modern CSS -->
    <link href="assets/boletim-modern.css" rel="stylesheet">
    
    <!-- Horários Styles CSS -->
    <link href="assets/horarios-styles.css" rel="stylesheet">

    <style>
        /* Tema escuro global override Bootstrap */
        :root {
            --bg-black: #000000;
            --bg-zinc-900: #18181b;
            --bg-zinc-800: #27272a;
            --bg-zinc-700: #3f3f46;
            --text-white: #ffffff;
            --text-zinc-300: #d4d4d8;
            --text-zinc-400: #a1a1aa;
            --text-zinc-500: #71717a;
            --emerald-400: #34d399;
            --emerald-500: #10b981;
            --red-400: #f87171;
            --red-500: #ef4444;
            --blue-400: #60a5fa;
            --border-zinc-800: #27272a;
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
    </script>

</body>

</html>
</script>

</body>

</html>