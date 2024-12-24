<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'IF calc'; ?></title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    
    <style>
        :root {
            --nav-bg: #1a73e8;
            --nav-hover: #1557b0;
            --primary-color: #1a73e8;
            --primary-dark: #1557b0;
            --card-border-radius: 15px;
            --transition-speed: 0.3s;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.12);
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }
        
        .navbar-custom {
            background: linear-gradient(135deg, var(--nav-bg), var(--primary-dark));
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-md);
            padding: 0.8rem 0;
            z-index: 1040;
        }

        .navbar-custom .nav-link {
            color: white !important;
            opacity: 0.9;
        }

        .navbar-custom .nav-link:hover {
            opacity: 1;
        }

        .navbar-custom .navbar-brand {
            color: white;
        }
        
        .navbar-brand {
            font-weight: 600;
            font-size: 1.3rem;
        }
        
        .nav-link {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            margin: 0 0.2rem;
            font-weight: 500;
        }
        
        .user-nav-item {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 30px;
            padding: 0.3rem;
            transition: all var(--transition-speed);
        }
        
        .user-nav-item:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.8);
            margin-right: 10px;
        }
        
        .card {
            border-radius: var(--card-border-radius);
            border: none;
            box-shadow: var(--shadow-sm);
            transition: all var(--transition-speed);
            overflow: hidden;
            background-color: var(--card-bg);
            border-color: var(--border-color);
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .table {
            background: white;
            border-radius: var(--card-border-radius);
            box-shadow: var(--shadow-sm);
            background-color: var(--card-bg);
            border-color: var(--border-color);
        }
        
        .table thead th {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            font-weight: 500;
            border: none;
            padding: 1rem;
        }

        .table tbody tr:hover {
            background-color: rgba(26, 115, 232, 0.05);
        }
        
        .badge {
            padding: 0.5rem 0.8rem;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .btn {
            border-radius: 10px;
            padding: 0.6rem 1.2rem;
            font-weight: 500;
            transition: all var(--transition-speed);
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .disciplina-info {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            margin: 0.5rem 0;
            border-left: 4px solid var(--primary-color);
            box-shadow: var(--shadow-sm);
            transition: all var(--transition-speed);
        }
        
        .disciplina-info:hover {
            transform: translateX(4px);
            box-shadow: var(--shadow-md);
        }

        /* Atualização dos estilos existentes */
        .dropdown-menu {
            border-radius: 12px;
            border: none;
            box-shadow: var(--shadow-lg);
            padding: 0.5rem;
            min-width: 200px;
            z-index: 1050;
        }
        
        .dropdown-item {
            border-radius: 8px;
            padding: 0.8rem 1rem;
            transition: all var(--transition-speed);
        }
        
        .dropdown-item:hover {
            background-color: rgba(26, 115, 232, 0.1);
            transform: translateX(4px);
        }

        /* Animações melhoradas */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Estilo para o item de perfil */
        .user-info-item {
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            color: var(--primary-dark);
            background-color: rgba(26, 115, 232, 0.05);
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }
        
        .user-info-item .user-role {
            font-size: 0.85rem;
            opacity: 0.7;
        }

        /* Adicione estes estilos */
        .nota-simulacao {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .simular-nota {
            opacity: 0.5;
            transition: opacity 0.3s;
        }
        
        .nota-simulacao:hover .simular-nota {
            opacity: 1;
        }
        
        .simular-nota i {
            font-size: 0.8rem;
        }

        /* Estilos adicionais para o Hero */
        .hero-wrapper {
            position: relative;
            overflow: hidden;
        }

        .hero-bg {
            z-index: 0;
        }

        .hero-wrapper .card-body {
            z-index: 1;
        }

        .logo-container {
            transition: transform 0.3s ease;
        }

        .logo-container:hover {
            transform: scale(1.05);
        }

        /* Animações suaves */
        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Melhorias de responsividade */
        @media (max-width: 768px) {
            .hero-wrapper .display-4 {
                font-size: 2.5rem;
            }
            .hero-wrapper .h3 {
                font-size: 1.5rem;
            }
            .hero-wrapper .lead {
                font-size: 1rem;
            }
        }

        /* Adicione estes estilos específicos para o hero */
        .text-shadow {
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .backdrop-blur {
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .hero-bg {
            opacity: 1 !important;
        }

        .rounded-4 {
            border-radius: 1.5rem !important;
        }

        /* Ajuste para melhor contraste */
        .badge.bg-white.bg-opacity-25 {
            color: white !important;
            border: 1px solid rgba(255,255,255,0.3);
        }

        /* Estilos adicionais para a landing page */
        .text-white-50 {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            border: none;
            box-shadow: 0 4px 12px rgba(26, 115, 232, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(26, 115, 232, 0.6);
        }

        .alert-info {
            background-color: rgba(26, 115, 232, 0.1);
            color: #1a73e8;
        }

        /* Estilos adicionais para o login */
        .backdrop-blur-lg {
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .max-width-500 {
            max-width: 500px;
        }

        /* Ajustes para melhor legibilidade */
        .bg-opacity-20 {
            --bs-bg-opacity: 0.25;
        }

        .card.bg-white.bg-opacity-20 {
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .text-white-50 {
            color: rgba(255, 255, 255, 0.85) !important;
        }
    </style>
</head>
<body>
    <div class="loading-bar" id="loadingBar"></div>

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
                            <li><hr class="dropdown-divider"></li>
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

    <!-- Modal para Calcular Média -->
    <div class="modal fade" id="calcMediaModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title">
                        <i class="fas fa-calculator me-2"></i>
                        Calculadora de Média
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="nota1" min="0" max="100" placeholder="Nota">
                                <label class="text-muted">
                                    <i class="fas fa-star-half-alt me-1"></i>
                                    1º Bimestre (Peso 2)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="nota2" min="0" max="100" placeholder="Nota">
                                <label class="text-muted">
                                    <i class="fas fa-star-half-alt me-1"></i>
                                    2º Bimestre (Peso 2)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="nota3" min="0" max="100" placeholder="Nota">
                                <label class="text-muted">
                                    <i class="fas fa-star-half-alt me-1"></i>
                                    3º Bimestre (Peso 3)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="nota4" min="0" max="100" placeholder="Nota">
                                <label class="text-muted">
                                    <i class="fas fa-star-half-alt me-1"></i>
                                    4º Bimestre (Peso 3)
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mt-3 overflow-hidden">
                        <div class="card-body p-4 text-center" id="resultadoMedia">
                            <div class="h4 mb-0">Insira suas notas para calcular a média</div>
                            <div class="text-muted small">O sistema considerará os pesos de cada bimestre</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Fechar
                    </button>
                    <button type="button" class="btn btn-primary" id="calcularBtn">
                        <i class="fas fa-calculator me-1"></i>
                        Calcular Média
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inicialização do tema e eventos
        document.addEventListener('DOMContentLoaded', function() {
            // Loading bar
            const loadingBar = document.getElementById('loadingBar');
            document.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function() {
                    loadingBar.style.display = 'block';
                });
            });
        });

        // Calculadora de Média
        document.getElementById('calcMediaBtn').addEventListener('click', function() {
            new bootstrap.Modal(document.getElementById('calcMediaModal')).show();
        });

        document.getElementById('calcularBtn').addEventListener('click', function() {
            const nota1 = Number(document.getElementById('nota1').value) || 0;
            const nota2 = Number(document.getElementById('nota2').value) || 0;
            const nota3 = Number(document.getElementById('nota3').value) || 0;
            const nota4 = Number(document.getElementById('nota4').value) || 0;

            const media = ((nota1 * 2) + (nota2 * 2) + (nota3 * 3) + (nota4 * 3)) / 10;
            const resultado = document.getElementById('resultadoMedia');
            
            const status = media >= 60 ? 'aprovado' : 'reprovado';
            const statusClass = media >= 60 ? 'success' : 'danger';
            const statusIcon = media >= 60 ? 'check-circle' : 'exclamation-circle';
            
            resultado.innerHTML = `
                <div class="text-${statusClass}">
                    <i class="fas fa-${statusIcon} fa-2x mb-2"></i>
                    <div class="h2 mb-2">${media.toFixed(1)}</div>
                    <div class="text-capitalize fw-bold">
                        ${status}
                    </div>
                </div>
            `;
            
            resultado.className = `card-body p-4 text-center animate__animated animate__fadeIn`;
        });
    </script>

    <div class="container mt-4">
        <!-- Aqui vai o conteúdo específico de cada página -->
        <?php if (isset($pageContent)) echo $pageContent; ?>
    </div>
</body>
</html>
