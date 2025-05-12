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
<<<<<<< HEAD

=======
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SUPACO</title>
<<<<<<< HEAD

=======
    
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
<<<<<<< HEAD

=======
    
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
    <style>
        :root {
            --primary-color: #1a73e8;
            --primary-dark: #0d47a1;
        }
<<<<<<< HEAD

=======
        
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
        body {
            min-height: 100vh;
            overflow-x: hidden;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .backdrop-blur-lg {
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .text-shadow {
<<<<<<< HEAD
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
=======
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
            box-shadow: 0 4px 12px rgba(26, 115, 232, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(26, 115, 232, 0.6);
        }

        .card {
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .text-white-50 {
            color: rgba(255, 255, 255, 0.85) !important;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
<<<<<<< HEAD

=======
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out;
        }
<<<<<<< HEAD

=======
        
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
        /* Estilos adicionais para melhor visual */
        .feature-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.12);
<<<<<<< HEAD
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
=======
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .login-section {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            border-left: 1px solid rgba(255, 255, 255, 0.1);
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
<<<<<<< HEAD
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
=======
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
            transition: all 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
<<<<<<< HEAD
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
=======
            box-shadow: 0 30px 60px rgba(0,0,0,0.15);
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
        }

        /* Estilos adicionais para o pattern e header */
        .pattern-overlay {
            background-image: url('assets/pattern.png');
            background-size: 20px 20px;
            opacity: 0.03;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .creative-header {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .creative-header:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            color: white;
        }

        .stats-pill {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            font-size: 0.9rem;
        }

        /* Ajuste do pattern overlay */
        .pattern-overlay {
<<<<<<< HEAD
            background:
                linear-gradient(135deg, rgba(26, 115, 232, 0.8) 0%, rgba(13, 71, 161, 0.9) 100%),
                repeating-linear-gradient(45deg,
                    rgba(255, 255, 255, 0.05) 0px,
                    rgba(255, 255, 255, 0.05) 2px,
                    transparent 2px,
                    transparent 8px);
=======
            background: 
                linear-gradient(135deg, rgba(26,115,232,0.8) 0%, rgba(13,71,161,0.9) 100%),
                repeating-linear-gradient(45deg, 
                    rgba(255,255,255,0.05) 0px, 
                    rgba(255,255,255,0.05) 2px,
                    transparent 2px, 
                    transparent 8px
                );
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
            opacity: 1;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        /* Estilo para o header alternativo */
        .header-decoration {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 30px;
            font-size: 0.9rem;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Melhorias de responsividade e design */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 0;
            }
<<<<<<< HEAD

            .col-lg-7,
            .col-lg-5 {
                padding: 1.5rem !important;
            }

            .feature-card {
                margin-bottom: 1rem;
            }

=======
            
            .col-lg-7, .col-lg-5 {
                padding: 1.5rem !important;
            }
            
            .feature-card {
                margin-bottom: 1rem;
            }
            
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
            .login-section {
                border-left: none !important;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(30px);
            }
        }

        /* Melhorias visuais */
        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
        }

        .dev-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .dev-link:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            transform: translateY(-2px);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        /* Melhorias para mobile */
        @media (max-width: 768px) {
            .container-fluid {
                padding: 0;
            }
<<<<<<< HEAD

            .col-lg-7,
            .col-lg-5 {
=======
            
            .col-lg-7, .col-lg-5 {
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
                padding: 1rem !important;
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 1rem !important;
                margin-top: 1.5rem;
            }

            .feature-card {
                padding: 1rem !important;
            }

            .feature-card .feature-icon {
                width: 40px;
                height: 40px;
            }

            .feature-card h3 {
                font-size: 0.9rem !important;
            }

            .feature-card p {
                font-size: 0.8rem !important;
                margin-top: 0.5rem;
            }

            /* Ajuste do header do GitHub para não sobrepor */
            .creative-header {
                top: auto;
                bottom: 1rem;
                right: 1rem;
                background: rgba(0, 0, 0, 0.2);
                backdrop-filter: blur(10px);
            }

            /* Ajuste do título e badge Beta */
            .title-wrapper {
                display: flex;
                align-items: flex-start;
                gap: 0.5rem;
                flex-wrap: wrap;
            }

            .title-wrapper .badge {
                margin-top: 0.5rem;
            }

            /* Layout mais compacto */
            .mb-4 {
                margin-bottom: 1rem !important;
            }

            .mb-3 {
                margin-bottom: 0.75rem !important;
            }
        }

        /* Melhorias para mobile - coluna de login */
        @media (max-width: 768px) {
            .login-section {
                padding: 1.5rem !important;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(255, 255, 255, 0.1);
            }

            .login-card {
                margin: 0;
                width: 100%;
            }

            .login-card .card-body {
                padding: 2rem !important;
            }

            .login-card .p-5 {
                padding: 1.5rem !important;
            }

            /* Ajusta o espaçamento do logo */
            .login-card .bg-primary {
                padding: 1.5rem !important;
                margin-bottom: 1.5rem !important;
            }

            /* Melhora a legibilidade dos textos */
            .login-card .text-muted {
                opacity: 0.8;
                font-size: 0.9rem;
            }

            /* Ajusta o botão de login */
            .login-card .btn-lg {
                padding: 0.8rem 1rem;
                font-size: 1rem;
            }

            /* Ajusta o alerta de informação */
            .login-card .alert {
                padding: 1rem;
                font-size: 0.85rem;
                margin: 1rem 0;
            }

            /* Melhora o espaçamento geral */
            .login-card .gap-3 {
                gap: 1rem !important;
            }

            /* Ajusta margens e paddings */
            .mb-4 {
                margin-bottom: 1.5rem !important;
            }

            .my-4 {
                margin: 1.5rem 0 !important;
            }
        }

        /* Transição suave entre layouts */
        .login-section {
            transition: all 0.3s ease;
        }

        .login-card {
            transition: all 0.3s ease;
        }
    </style>
</head>
<<<<<<< HEAD

=======
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
<body class="bg-gradient-primary">
    <!-- Header Criativo -->
    <a href="https://github.com/kellyson71/IF-calc" target="_blank" class="creative-header">
        <div class="stats-pill">
            <i class="fas fa-code-branch"></i>
            <span>v1.0</span>
        </div>
        <div class="stats-pill">
            <i class="fas fa-star"></i>
            <span>Beta</span>
        </div>
        <i class="fab fa-github fs-5"></i>
    </a>

    <!-- Conteúdo Principal -->
    <div class="min-vh-100 d-flex align-items-stretch">
        <!-- Pattern overlay com opacidade ajustada -->
<<<<<<< HEAD
        <div class="pattern-overlay"
            style="background: linear-gradient(135deg, rgba(26,115,232,0.97) 0%, rgba(13,71,161,0.97) 100%), 
                    url('assets/pattern.png');
                    background-size: cover;">
        </div>

=======
        <div class="pattern-overlay" 
             style="background: linear-gradient(135deg, rgba(26,115,232,0.97) 0%, rgba(13,71,161,0.97) 100%), 
                    url('assets/pattern.png');
                    background-size: cover;">
        </div>
        
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
        <div class="container-fluid p-0">
            <div class="row g-0 min-vh-100">
                <!-- Coluna de apresentação -->
                <div class="col-lg-7 p-5 d-flex flex-column">
                    <div class="mb-4 animate__animated animate__fadeInUp">
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <div class="title-wrapper">
                                    <h1 class="display-4 fw-bold m-0 text-white text-shadow">SUPACO</h1>
                                    <span class="badge bg-white text-primary fs-6">Beta</span>
                                </div>
                                <p class="h5 text-white-50 mb-3">Sistema Útil Pra Aluno Cansado e Ocupado</p>
<<<<<<< HEAD
                                <a href="https://github.com/Kellyson71"
                                    target="_blank"
                                    class="dev-link hover-lift">
=======
                                <a href="https://github.com/Kellyson71" 
                                   target="_blank"
                                   class="dev-link hover-lift">
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
                                    <i class="fab fa-github"></i>
                                    <span>Desenvolvido por Kellyson</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Features em grid responsivo -->
                    <div class="features-grid flex-grow-1">
                        <?php
                        $features = [
                            [
                                'icon' => 'check-circle',
                                'title' => 'Controle de Frequência',
                                'desc' => 'Monitore suas faltas e saiba quando pode faltar sem preocupações'
                            ],
                            [
                                'icon' => 'calculator',
                                'title' => 'Calculadora de Notas',
                                'desc' => 'Simule suas notas e descubra quanto precisa para passar'
                            ],
                            [
                                'icon' => 'calendar',
                                'title' => 'Horários Inteligentes',
                                'desc' => 'Visualize suas aulas de forma organizada e prática'
                            ],
                            [
                                'icon' => 'sync',
                                'title' => 'Sincronização SUAP',
                                'desc' => 'Seus dados sempre atualizados com o sistema do IFRN'
                            ]
                        ];

                        foreach ($features as $index => $feature): ?>
<<<<<<< HEAD
                            <div class="animate__animated animate__fadeInUp"
                                style="animation-delay: <?php echo $index * 0.1; ?>s">
=======
                            <div class="animate__animated animate__fadeInUp" 
                                 style="animation-delay: <?php echo $index * 0.1; ?>s">
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
                                <div class="feature-card h-100 rounded-4 p-4 hover-lift">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="feature-icon me-3">
                                            <i class="fas fa-<?php echo $feature['icon']; ?> fa-lg text-white"></i>
                                        </div>
                                        <h3 class="h5 mb-0 text-white"><?php echo $feature['title']; ?></h3>
                                    </div>
                                    <p class="mb-0 text-white-50"><?php echo $feature['desc']; ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Coluna de login -->
                <div class="col-lg-5 login-section p-5 d-flex align-items-center">
                    <div class="w-100 max-width-500 mx-auto">
                        <div class="login-card rounded-4 p-5">
                            <div class="card-body p-5">
                                <div class="text-center mb-4">
                                    <div class="bg-primary bg-opacity-10 rounded-4 p-3 d-inline-block mb-3">
<<<<<<< HEAD
                                        <img src="assets/logo.png" alt="SUPACO Logo"
                                            class="rounded-3" style="width: 80px; height: 80px;">
=======
                                        <img src="assets/logo.png" alt="SUPACO Logo" 
                                             class="rounded-3" style="width: 80px; height: 80px;">
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
                                    </div>
                                    <h2 class="h3 text-primary mb-2">Bem-vindo ao SUPACO</h2>
                                    <p class="text-muted">Faça login com suas credenciais do SUAP</p>
                                </div>
<<<<<<< HEAD

                                <div class="d-grid gap-3">
                                    <a href="<?php echo $auth_url; ?>"
                                        class="btn btn-primary btn-lg py-3 rounded-3 shadow-sm d-flex align-items-center justify-content-center">
                                        <i class="fas fa-sign-in-alt me-2"></i>
                                        Entrar com SUAP </a>

                                    <?php if (isset($_GET['erro'])): ?>
                                        <div class="alert alert-danger border-0 rounded-3 shadow-sm">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-exclamation-triangle fa-lg me-3 text-danger"></i>
                                                <p class="mb-0 small">
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
                                                </p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
=======
                                
                                <div class="d-grid gap-3">
                                    <a href="<?php echo $auth_url; ?>" 
                                       class="btn btn-primary btn-lg py-3 rounded-3 shadow-sm d-flex align-items-center justify-content-center">
                                        <i class="fas fa-sign-in-alt me-2"></i>
                                        Entrar com SUAP
                                    </a>
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30

                                    <div class="alert alert-info border-0 rounded-3 shadow-sm">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-info-circle fa-lg me-3 text-primary"></i>
                                            <p class="mb-0 small">
                                                Suas credenciais são gerenciadas diretamente pelo SUAP.
                                                O login é seguro e criptografado.
                                            </p>
                                        </div>
                                    </div>

                                    <hr class="my-4">
<<<<<<< HEAD

=======
                                    
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
                                    <div class="text-center text-muted">
                                        <small>
                                            Desenvolvido com <i class="fas fa-heart text-danger"></i> por estudantes do IFRN
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
<<<<<<< HEAD

</html>
=======
</html>
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
