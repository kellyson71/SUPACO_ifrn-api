<?php
// Modifique o cabeçalho base.php para incluir o manifest.json e registrar o service worker
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'SUPACO'; ?></title>

    <!-- PWA Configs -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#7353BA">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SUPACO">
    <link rel="apple-touch-icon" href="assets/icons/icon-152x152.png">

    <!-- CSS -->
    <!-- Bootstrap 5 com tema personalizado -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Pro para ícones modernos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Animações CSS -->
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <!-- AOS - Animate On Scroll para efeitos de rolagem -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Toastify para notificações elegantes -->
    <link href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css" rel="stylesheet">
    <!-- Chart.js para gráficos interativos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/Chart.min.css"> <!-- Custom Styles -->
    <link href="assets/day-selector.css" rel="stylesheet">
    <link href="assets/dashboard.css" rel="stylesheet">
    <link href="assets/status-style.css" rel="stylesheet">
    <!-- Offline Visual Styles -->
    <link href="assets/offline-styles.css" rel="stylesheet">
    <style>
        /* Ajustes para espaçamento do banner offline */
        body.has-offline-banner {
            padding-top: 50px;
        }

        /* Barra indicadora de status offline (legacy) */
        #offlineIndicator {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #ef4444;
            color: white;
            text-align: center;
            padding: 8px 16px;
            font-weight: 500;
            z-index: 9999;
            display: none;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <!-- Indicador de estado offline -->
    <div id="offlineIndicator">
        <i class="fas fa-wifi-slash me-2"></i>
        Você está offline. Usando dados salvos.
    </div>

    <div class="loading-bar" id="loadingBar"></div>

    <!-- Notificações toast -->
    <div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 5000;"></div>

    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i>
                SUPACO
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars" style="color: white;"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active-page' : ''; ?>" href="index.php">
                            <i class="fas fa-home me-1"></i> Início
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="calcMediaBtn">
                            <i class="fas fa-calculator me-1"></i> Calcular Média
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://classroom.google.com/u/1/a/not-turned-in/all" target="_blank">
                            <i class="fab fa-google me-1"></i> Classroom
                        </a>
                    </li>
                </ul>

                <?php if (isset($_SESSION['access_token'])): ?>
                    <!-- Botão de sincronização -->
                    <button type="button" class="btn btn-sm text-white me-2" id="syncButton" title="Sincronizar dados">
                        <i class="fas fa-sync-alt"></i>
                    </button>

                    <!-- Botão de informações API -->
                    <button type="button" class="btn btn-sm text-white me-2 px-1" id="apiInfoBtn" data-bs-toggle="modal" data-bs-target="#apiInfoModal" title="Informações da API">
                        <i class="fas fa-code"></i>
                    </button>
                <?php endif; ?>

                <?php if (isset($_SESSION['access_token']) && isset($meusDados)): ?>
                    <div class="dropdown">
                        <a class="nav-link user-nav-item" href="#" role="button" data-bs-toggle="dropdown">
                            <?php if (isset($meusDados['url_foto_150x200'])): ?>
                                <img src="<?php echo htmlspecialchars($meusDados['url_foto_150x200']); ?>"
                                    class="user-avatar" alt="Foto do usuário">
                            <?php else: ?>
                                <i class="fas fa-user-circle user-avatar d-flex align-items-center justify-content-center bg-light text-primary"></i>
                            <?php endif; ?>
                            <span class="text-white"><?php echo htmlspecialchars($meusDados['nome_usual']); ?></span>
                            <i class="fas fa-chevron-down ms-2 text-white-50"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end animate__animated animate__fadeIn">
                            <li>
                                <div class="user-info-item">
                                    <div>
                                        <div class="fw-500"><?php echo htmlspecialchars($meusDados['nome_usual']); ?></div>
                                        <div class="user-role"><?php echo htmlspecialchars($meusDados['tipo_vinculo']); ?></div>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?php echo $meusDados['vinculo']['curriculo_lattes']; ?>" target="_blank">
                                    <i class="fas fa-graduation-cap me-2 text-primary"></i>
                                    Currículo Lattes
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>
                                    Sair da conta
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Conteúdo específico da página -->
    <main>
        <?php if (isset($pageContent)) echo $pageContent; ?>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/luxon@3.3.0/build/global/luxon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@1.3.1/dist/chartjs-adapter-luxon.umd.min.js"></script>
    <script src="assets/day-selector.js"></script>
    <script src="assets/notification.js"></script>
    <script src="assets/offline_optimized.js"></script>

    <!-- Registro do Service Worker -->
    <script>
        // Registrar o Service Worker se o navegador suportar
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/SUAP/sw.js')
                    .then(function(registration) {
                        console.log('Service Worker registrado com sucesso:', registration.scope);
                    })
                    .catch(function(error) {
                        console.log('Falha ao registrar Service Worker:', error);
                    });
            });
        }

        // Sistema de notificações Toast moderno
        function showToast(message, type = 'success', duration = 5000) {
            const bgColors = {
                success: 'linear-gradient(to right, #10b981, #059669)',
                warning: 'linear-gradient(to right, #f59e0b, #d97706)',
                danger: 'linear-gradient(to right, #ef4444, #dc2626)',
                info: 'linear-gradient(to right, #06b6d4, #0891b2)'
            };

            const icons = {
                success: '<i class="fas fa-check-circle"></i>',
                warning: '<i class="fas fa-exclamation-circle"></i>',
                danger: '<i class="fas fa-exclamation-triangle"></i>',
                info: '<i class="fas fa-info-circle"></i>'
            };

            Toastify({
                text: `${icons[type]} ${message}`,
                duration: duration,
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
    </script>

    <!-- PWA Enhanced Scripts -->
    <script src="assets/offline-cache.js"></script>
</body>

</html>