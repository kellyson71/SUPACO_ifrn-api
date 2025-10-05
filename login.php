<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['access_token'])) {
    header('Location: index.php');
    exit;
}

$auth_url = SUAP_URL . "/o/authorize/?" . http_build_query([
    'response_type' => 'code',
    'client_id' => SUAP_CLIENT_ID,
    'redirect_uri' => REDIRECT_URI
]);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SUPACO</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
    <style>
        /* Dark Theme Modern Design - Mesmo padrão do index.php */
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
            --blue-500: #3b82f6;
            --purple-400: #a78bfa;
            --purple-500: #8b5cf6;
            --border-zinc-800: #27272a;
            --border-zinc-700: #3f3f46;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            background-color: var(--bg-black);
            color: var(--text-white);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Grid Background - Mesmo do index.php */
        .grid-background {
            position: relative;
            min-height: 100vh;
            width: 100%;
            background-color: var(--bg-black);
            background-image: linear-gradient(to right, #262626 1px, transparent 1px),
                linear-gradient(to bottom, #262626 1px, transparent 1px);
            background-size: 40px 40px;
        }

        .grid-overlay {
            position: absolute;
            inset: 0;
            background: radial-gradient(
                ellipse at center,
                transparent 20%,
                var(--bg-black)
            );
            pointer-events: none;
        }

        /* Container principal */
        .main-container {
            position: relative;
            z-index: 10;
            min-height: 100vh;
            padding: 2rem;
        }

        /* Seção de apresentação */
        .presentation-section {
            text-align: center;
            margin-bottom: 3rem;
        }

        .brand-header {
            margin-bottom: 2rem;
        }

        .brand-title {
            font-size: 4rem;
            font-weight: 700;
            color: var(--text-white);
            margin-bottom: 0.5rem;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .brand-subtitle {
            font-size: 1.25rem;
            color: var(--text-zinc-400);
            margin-bottom: 2rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 3rem;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .login-btn {
            background: linear-gradient(135deg, var(--emerald-400), var(--emerald-500));
            color: var(--text-white);
            box-shadow: 0 8px 25px rgba(52, 211, 153, 0.3);
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(52, 211, 153, 0.4);
            color: var(--text-white);
            text-decoration: none;
        }

        .calculator-btn {
            background: linear-gradient(135deg, var(--blue-400), var(--blue-500));
            color: var(--text-white);
            box-shadow: 0 8px 25px rgba(96, 165, 250, 0.3);
        }

        .calculator-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(96, 165, 250, 0.4);
            color: var(--text-white);
            text-decoration: none;
        }

        .dev-link {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50px;
            color: var(--text-white);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .dev-link:hover {
            background: rgba(255, 255, 255, 0.15);
            color: var(--text-white);
            transform: translateY(-2px);
            text-decoration: none;
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: rgba(39, 39, 42, 0.5);
            border: 1px solid var(--border-zinc-800);
            border-radius: 1rem;
            padding: 1.5rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .feature-card:hover {
            transform: translateY(-4px);
            background: rgba(39, 39, 42, 0.7);
            border-color: var(--emerald-400);
            box-shadow: 0 8px 25px rgba(52, 211, 153, 0.2);
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--emerald-400), var(--emerald-500));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 4px 12px rgba(52, 211, 153, 0.3);
        }

        .feature-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-white);
            margin-bottom: 0.5rem;
        }

        .feature-description {
            font-size: 0.9rem;
            color: var(--text-zinc-400);
            line-height: 1.5;
        }

        /* Modal da Calculadora */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .modal-overlay.show {
            display: flex;
            animation: fadeIn 0.3s ease-out;
        }

        .modal-content {
            background: rgba(39, 39, 42, 0.95);
            border: 1px solid var(--border-zinc-800);
            border-radius: 1.5rem;
            padding: 2.5rem;
            max-width: 500px;
            width: 100%;
            backdrop-filter: blur(20px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        .modal-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .modal-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-white);
            margin-bottom: 0.5rem;
        }

        .modal-subtitle {
            color: var(--text-zinc-400);
            font-size: 1rem;
        }

        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            color: var(--text-zinc-400);
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .modal-close:hover {
            color: var(--text-white);
        }

        /* Calculadora */
        .calculator-form {
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-white);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            background: rgba(39, 39, 42, 0.5);
            border: 1px solid var(--border-zinc-800);
            border-radius: 0.75rem;
            color: var(--text-white);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--emerald-400);
            box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.1);
        }

        .form-control::placeholder {
            color: var(--text-zinc-500);
        }

        .calculate-btn {
            width: 100%;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--blue-400), var(--blue-500));
            border: none;
            border-radius: 1rem;
            color: var(--text-white);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(96, 165, 250, 0.3);
        }

        .calculate-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(96, 165, 250, 0.4);
        }

        /* Resultado da calculadora */
        .calculator-result {
            background: rgba(39, 39, 42, 0.5);
            border: 1px solid var(--border-zinc-800);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-top: 1.5rem;
            display: none;
        }

        .calculator-result.show {
            display: block;
            animation: fadeIn 0.3s ease-out;
        }

        .result-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-white);
            margin-bottom: 1rem;
            text-align: center;
        }

        .result-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-zinc-800);
        }

        .result-item:last-child {
            border-bottom: none;
        }

        .result-label {
            color: var(--text-zinc-400);
            font-size: 0.9rem;
        }

        .result-value {
            color: var(--text-white);
            font-weight: 600;
            font-size: 1rem;
        }

        .result-value.success {
            color: var(--emerald-400);
        }

        .result-value.warning {
            color: var(--blue-400);
        }

        .result-value.danger {
            color: var(--red-400);
        }

        /* Alertas */
        .alert {
            border: none;
            border-radius: 1rem;
            padding: 1rem 1.5rem;
            margin: 1.5rem 0;
            backdrop-filter: blur(10px);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--red-400);
        }

        .alert-info {
            background: rgba(96, 165, 250, 0.1);
            border: 1px solid rgba(96, 165, 250, 0.3);
            color: var(--blue-400);
        }

        .alert-icon {
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }

        /* Footer */
        .main-footer {
            text-align: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-zinc-800);
        }

        .footer-text {
            color: var(--text-zinc-500);
            font-size: 0.9rem;
        }

        .heart-icon {
            color: var(--red-400);
            animation: heartbeat 2s infinite;
        }

        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        /* Header flutuante */
        .floating-header {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            background: rgba(39, 39, 42, 0.8);
            border: 1px solid var(--border-zinc-800);
            border-radius: 50px;
            backdrop-filter: blur(20px);
            color: var(--text-white);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .floating-header:hover {
            background: rgba(39, 39, 42, 0.9);
            color: var(--text-white);
            text-decoration: none;
            transform: translateY(-2px);
        }

        .version-badge {
            padding: 0.25rem 0.75rem;
            background: rgba(52, 211, 153, 0.2);
            border: 1px solid rgba(52, 211, 153, 0.3);
            border-radius: 20px;
            font-size: 0.8rem;
            color: var(--emerald-400);
        }

        .beta-badge {
            padding: 0.25rem 0.75rem;
            background: rgba(139, 92, 246, 0.2);
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 20px;
            font-size: 0.8rem;
            color: var(--purple-400);
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .main-container {
                padding: 1rem;
            }

            .brand-title {
                font-size: 2.5rem;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .action-btn {
                width: 100%;
                max-width: 300px;
            }

            .floating-header {
                top: 1rem;
                right: 1rem;
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .feature-card {
                padding: 1rem;
            }

            .modal-content {
                padding: 1.5rem;
                margin: 1rem;
            }
        }

        /* Animações */
        .fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide-in-up {
            animation: slideInUp 0.8s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <!-- Grid Background -->
    <div class="grid-background">
        <div class="grid-overlay"></div>

        <!-- Header Flutuante -->
        <a href="https://github.com/kellyson71/IF-calc" target="_blank" class="floating-header">
            <div class="version-badge">
                <i class="fas fa-code-branch"></i>
                <span>v2.6</span>
            </div>
            <div class="beta-badge">
                <i class="fas fa-star"></i>
                <span>Beta</span>
            </div>
            <i class="fab fa-github"></i>
        </a>

        <!-- Container Principal -->
        <div class="main-container">
            <!-- Seção de Apresentação -->
            <div class="presentation-section slide-in-up">
                <div class="brand-header">
                    <h1 class="brand-title">SUPACO</h1>
                    <p class="brand-subtitle">Sistema Útil Pra Aluno Cansado e Ocupado</p>
                </div>

                <!-- Botões de Ação -->
                <div class="action-buttons">
                    <a href="<?php echo $auth_url; ?>" class="action-btn login-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Entrar com SUAP</span>
                    </a>
                    <button class="action-btn calculator-btn" onclick="openCalculator()">
                        <i class="fas fa-calculator"></i>
                        <span>Calculadora de Notas</span>
                    </button>
                </div>

                <a href="https://github.com/Kellyson71" target="_blank" class="dev-link">
                    <i class="fab fa-github"></i>
                    <span>Desenvolvido por Kellyson</span>
                </a>
            </div>

            <!-- Features Grid -->
            <div class="features-grid">
                <div class="feature-card fade-in" style="animation-delay: 0.1s;">
                    <div class="feature-icon">
                        <i class="fas fa-check-circle fa-lg"></i>
                    </div>
                    <h3 class="feature-title">Controle de Frequência</h3>
                    <p class="feature-description">Monitore suas faltas e saiba quando pode faltar sem preocupações</p>
                </div>

                <div class="feature-card fade-in" style="animation-delay: 0.2s;">
                    <div class="feature-icon">
                        <i class="fas fa-calculator fa-lg"></i>
                    </div>
                    <h3 class="feature-title">Calculadora de Notas</h3>
                    <p class="feature-description">Simule suas notas e descubra quanto precisa para passar</p>
                </div>

                <div class="feature-card fade-in" style="animation-delay: 0.3s;">
                    <div class="feature-icon">
                        <i class="fas fa-calendar fa-lg"></i>
                    </div>
                    <h3 class="feature-title">Horários Inteligentes</h3>
                    <p class="feature-description">Visualize suas aulas de forma organizada e prática</p>
                </div>

                <div class="feature-card fade-in" style="animation-delay: 0.4s;">
                    <div class="feature-icon">
                        <i class="fas fa-sync fa-lg"></i>
                    </div>
                    <h3 class="feature-title">Sincronização SUAP</h3>
                    <p class="feature-description">Seus dados sempre atualizados com o sistema do IFRN</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="main-footer">
                <p class="footer-text">
                    Desenvolvido com <i class="fas fa-heart heart-icon"></i> por estudantes do IFRN
                </p>
            </div>
        </div>

        <!-- Modal da Calculadora -->
        <div class="modal-overlay" id="calculatorModal">
            <div class="modal-content">
                <button class="modal-close" onclick="closeCalculator()">
                    <i class="fas fa-times"></i>
                </button>

                <div class="modal-header">
                    <div class="section-logo" style="background: linear-gradient(135deg, var(--blue-400), var(--blue-500)); display: flex; align-items: center; justify-content: center; width: 80px; height: 80px; border-radius: 1rem; margin: 0 auto 1.5rem; box-shadow: 0 8px 25px rgba(96, 165, 250, 0.3);">
                        <i class="fas fa-calculator fa-2x text-white"></i>
                    </div>
                    <h2 class="modal-title">Calculadora de Notas</h2>
                    <p class="modal-subtitle">Sistema IF: MD = (2×N1 + 3×N2) ÷ 5</p>
                </div>

                <form class="calculator-form" id="calculatorForm">
                    <div class="form-group">
                        <label class="form-label">Período</label>
                        <select class="form-control" id="periodo" required>
                            <option value="">Selecione o período</option>
                            <option value="2">2 Bimestres</option>
                            <option value="4">4 Bimestres</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nota do 1º Bimestre (N1)</label>
                        <input type="number" class="form-control" id="n1" min="0" max="100" step="0.1" placeholder="Digite sua nota">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nota do 2º Bimestre (N2)</label>
                        <input type="number" class="form-control" id="n2" min="0" max="100" step="0.1" placeholder="Digite sua nota">
                    </div>

                    <button type="submit" class="calculate-btn">
                        <i class="fas fa-calculator"></i>
                        <span>Calcular</span>
                    </button>
                </form>

                <div class="calculator-result" id="calculatorResult">
                    <h3 class="result-title">Resultado do Cálculo</h3>
                    <div class="result-item">
                        <span class="result-label">Média Direta (MD):</span>
                        <span class="result-value" id="mediaDireta">-</span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Situação:</span>
                        <span class="result-value" id="situacao">-</span>
                    </div>
                    <div class="result-item" id="notaNecessariaItem" style="display: none;">
                        <span class="result-label">Nota necessária:</span>
                        <span class="result-value" id="notaNecessaria">-</span>
                    </div>
                    <div class="result-item" id="afItem" style="display: none;">
                        <span class="result-label">Nota AF necessária:</span>
                        <span class="result-value" id="notaAF">-</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Função para abrir a calculadora
        function openCalculator() {
            document.getElementById('calculatorModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        // Função para fechar a calculadora
        function closeCalculator() {
            document.getElementById('calculatorModal').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        // Fechar modal ao clicar fora
        document.getElementById('calculatorModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCalculator();
            }
        });

        // Função para calcular a média direta (sistema IF)
        function calcularMediaDireta(n1, n2) {
            if (n1 === null || n2 === null) {
                return null;
            }
            return (2 * n1 + 3 * n2) / 5;
        }

        // Função para calcular nota necessária
        function calcularNotaNecessariaIF(n1, n2) {
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

            if (n1 !== null && n2 !== null) {
                const md = calcularMediaDireta(n1, n2);
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
            } else if (n1 !== null && n2 === null) {
                const nota_necessaria = (300 - 2 * n1) / 3;
                resultado.nota_necessaria = Math.max(0, Math.min(100, nota_necessaria));
                resultado.situacao = 'aguardando_n2';
                if (nota_necessaria <= 100) {
                    resultado.pode_passar_direto = true;
                }
            } else if (n1 === null && n2 !== null) {
                const nota_necessaria = (300 - 3 * n2) / 2;
                resultado.nota_necessaria = Math.max(0, Math.min(100, nota_necessaria));
                resultado.situacao = 'aguardando_n1';
            } else {
                resultado.situacao = 'aguardando_notas';
            }

            return resultado;
        }

        // Função para calcular avaliação final
        function calcularAvaliacaoFinal(n1, n2) {
            const md = calcularMediaDireta(n1, n2);
            
            const naf1 = 120 - md;
            const naf2 = (300 - 3 * n2) / 2;
            const naf3 = (300 - 2 * n1) / 3;
            
            const naf_necessaria = Math.min(naf1, naf2, naf3);
            return Math.max(0, Math.min(100, naf_necessaria));
        }

        // Event listener para o formulário
        document.getElementById('calculatorForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const periodo = document.getElementById('periodo').value;
            const n1 = document.getElementById('n1').value ? parseFloat(document.getElementById('n1').value) : null;
            const n2 = document.getElementById('n2').value ? parseFloat(document.getElementById('n2').value) : null;

            if (!periodo) {
                alert('Por favor, selecione o período.');
                return;
            }

            if (n1 === null && n2 === null) {
                alert('Por favor, insira pelo menos uma nota.');
                return;
            }

            // Calcula baseado no período
            let resultado;
            if (periodo === '2') {
                // Sistema IF (2 bimestres)
                resultado = calcularNotaNecessariaIF(n1, n2);
            } else {
                // Sistema tradicional (4 bimestres) - implementação simplificada
                const notas = [n1, n2, null, null].filter(n => n !== null);
                const media = notas.reduce((sum, nota) => sum + nota, 0) / notas.length;
                resultado = {
                    media_atual: media,
                    situacao: media >= 60 ? 'aprovado_direto' : (media >= 20 ? 'avaliacao_final' : 'reprovado_nota'),
                    nota_necessaria: null
                };
            }

            // Exibe os resultados
            const resultDiv = document.getElementById('calculatorResult');
            const mediaDiretaSpan = document.getElementById('mediaDireta');
            const situacaoSpan = document.getElementById('situacao');
            const notaNecessariaItem = document.getElementById('notaNecessariaItem');
            const notaNecessariaSpan = document.getElementById('notaNecessaria');
            const afItem = document.getElementById('afItem');
            const notaAFSpan = document.getElementById('notaAF');

            // Limpa classes de cor anteriores
            mediaDiretaSpan.className = 'result-value';
            situacaoSpan.className = 'result-value';

            if (resultado.media_atual !== null) {
                mediaDiretaSpan.textContent = resultado.media_atual.toFixed(1);
                if (resultado.media_atual >= 60) {
                    mediaDiretaSpan.classList.add('success');
                } else if (resultado.media_atual >= 20) {
                    mediaDiretaSpan.classList.add('warning');
                } else {
                    mediaDiretaSpan.classList.add('danger');
                }
            } else {
                mediaDiretaSpan.textContent = '-';
            }

            // Define a situação
            let situacaoText = '';
            let situacaoClass = '';
            switch (resultado.situacao) {
                case 'aprovado_direto':
                    situacaoText = 'Aprovado Direto';
                    situacaoClass = 'success';
                    break;
                case 'avaliacao_final':
                    situacaoText = 'Avaliação Final';
                    situacaoClass = 'warning';
                    break;
                case 'reprovado_nota':
                    situacaoText = 'Reprovado por Nota';
                    situacaoClass = 'danger';
                    break;
                case 'aguardando_n2':
                    situacaoText = 'Aguardando N2';
                    situacaoClass = 'warning';
                    break;
                case 'aguardando_n1':
                    situacaoText = 'Aguardando N1';
                    situacaoClass = 'warning';
                    break;
                default:
                    situacaoText = 'Aguardando Notas';
                    situacaoClass = 'warning';
            }
            situacaoSpan.textContent = situacaoText;
            situacaoSpan.classList.add(situacaoClass);

            // Mostra nota necessária se aplicável
            if (resultado.nota_necessaria !== null) {
                notaNecessariaItem.style.display = 'flex';
                notaNecessariaSpan.textContent = resultado.nota_necessaria.toFixed(1);
            } else {
                notaNecessariaItem.style.display = 'none';
            }

            // Mostra nota AF se aplicável
            if (resultado.situacao === 'avaliacao_final' && n1 !== null && n2 !== null) {
                afItem.style.display = 'flex';
                const afNecessaria = calcularAvaliacaoFinal(n1, n2);
                notaAFSpan.textContent = afNecessaria.toFixed(1);
            } else {
                afItem.style.display = 'none';
            }

            // Mostra o resultado
            resultDiv.classList.add('show');
        });
    </script>
</body>

</html>