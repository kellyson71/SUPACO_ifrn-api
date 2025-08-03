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
        .login-container {
            position: relative;
            z-index: 10;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-content {
            width: 100%;
            max-width: 1200px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        @media (max-width: 768px) {
            .login-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
        }

        /* Seção de apresentação */
        .presentation-section {
            padding: 2rem;
        }

        .brand-header {
            margin-bottom: 3rem;
        }

        .brand-title {
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--text-white);
            margin-bottom: 0.5rem;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .brand-subtitle {
            font-size: 1.25rem;
            color: var(--text-zinc-400);
            margin-bottom: 1.5rem;
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
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .features-grid {
                grid-template-columns: 1fr;
            }
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

        /* Seção de login */
        .login-section {
            background: rgba(39, 39, 42, 0.3);
            border: 1px solid var(--border-zinc-800);
            border-radius: 1.5rem;
            padding: 3rem;
            backdrop-filter: blur(20px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .login-logo {
            width: 80px;
            height: 80px;
            border-radius: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 25px rgba(52, 211, 153, 0.3);
        }

        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-white);
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: var(--text-zinc-400);
            font-size: 1rem;
        }

        /* Botão de login */
        .login-btn {
            width: 100%;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--emerald-400), var(--emerald-500));
            border: none;
            border-radius: 1rem;
            color: var(--text-white);
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(52, 211, 153, 0.3);
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(52, 211, 153, 0.4);
            color: var(--text-white);
            text-decoration: none;
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
        .login-footer {
            text-align: center;
            margin-top: 2rem;
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
            .login-container {
                padding: 1rem;
            }

            .brand-title {
                font-size: 2.5rem;
            }

            .login-section {
                padding: 2rem;
            }

            .floating-header {
                top: 1rem;
                right: 1rem;
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }

            .features-grid {
                gap: 1rem;
            }

            .feature-card {
                padding: 1rem;
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

        .slide-in-left {
            animation: slideInLeft 0.8s ease-out;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .slide-in-right {
            animation: slideInRight 0.8s ease-out;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
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
                <span>v1.0</span>
            </div>
            <div class="beta-badge">
                <i class="fas fa-star"></i>
                <span>Beta</span>
            </div>
            <i class="fab fa-github"></i>
        </a>

        <!-- Container Principal -->
        <div class="login-container">
            <div class="login-content">
                <!-- Seção de Apresentação -->
                <div class="presentation-section slide-in-left">
                    <div class="brand-header">
                        <h1 class="brand-title">SUPACO</h1>
                        <p class="brand-subtitle">Sistema Útil Pra Aluno Cansado e Ocupado</p>
                        <a href="https://github.com/Kellyson71" target="_blank" class="dev-link">
                            <i class="fab fa-github"></i>
                            <span>Desenvolvido por Kellyson</span>
                        </a>
                    </div>

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
                </div>

                <!-- Seção de Login -->
                <div class="login-section slide-in-right">
                    <div class="login-header">
                        <img src="assets/logo.png" alt="SUPACO Logo" class="login-logo">
                        <h2 class="login-title">Bem-vindo ao SUPACO</h2>
                        <p class="login-subtitle">Faça login com suas credenciais do SUAP</p>
                    </div>

                    <a href="<?php echo $auth_url; ?>" class="login-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Entrar com SUAP</span>
                    </a>

                    <?php if (isset($_GET['erro'])): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle alert-icon"></i>
                            <span>
                                <?php
                                $mensagem = "Ocorreu um erro durante a autenticação.";

                                if ($_GET['erro'] === 'sessao_expirada') {
                                    $mensagem = "A sua sessão expirou. Por favor, faça login novamente.";
                                } elseif ($_GET['erro'] === 'usuario_nao_encontrado') {
                                    $mensagem = "Não foi possível obter seus dados do SUAP. Por favor, tente novamente.";
                                } elseif ($_GET['erro'] === 'token_invalido') {
                                    $mensagem = "Token de acesso inválido. Por favor, faça login novamente.";
                                }

                                echo $mensagem;
                                ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle alert-icon"></i>
                            <span>Suas credenciais são gerenciadas diretamente pelo SUAP. O login é seguro e criptografado.</span>
                        </div>
                    <?php endif; ?>

                    <div class="login-footer">
                        <p class="footer-text">
                            Desenvolvido com <i class="fas fa-heart heart-icon"></i> por estudantes do IFRN
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>