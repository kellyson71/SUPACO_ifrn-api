<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'SUPACO'; ?></title> <!-- CSS -->
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
    <style>
        :root {
            /* Paleta de cores moderna e elegante - Atualizada */
            --nav-bg: #7353BA;
            --nav-hover: #5D43A6;
            --primary-color: #7353BA;
            --primary-dark: #5D43A6;
            --primary-light: #F1ECFC;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
            --accent-color: #FF8A5B;
            --accent-dark: #F27141;
            --accent-light: #FFE0D4;
            --neutral-100: #f9fafb;
            --neutral-200: #f3f4f6;
            --neutral-300: #e5e7eb;
            --neutral-400: #d1d5db;
            --neutral-500: #9ca3af;
            --neutral-600: #6b7280;
            --neutral-700: #4b5563;
            --neutral-800: #374151;
            --neutral-900: #1f2937;

            /* Elementos de UI */
            --card-border-radius: 16px;
            --btn-border-radius: 10px;
            --transition-speed: 0.25s;
            --transition-function: cubic-bezier(0.4, 0, 0.2, 1);

            /* Sombras com efeitos mais suaves */
            --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.035), 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 5px 15px rgba(0, 0, 0, 0.07), 0 2px 5px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 25px rgba(67, 97, 238, 0.1), 0 5px 10px rgba(0, 0, 0, 0.04);

            /* Espaçamento base */
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;

            /* Tipografia */
            --font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
        }

        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            background-color: var(--neutral-100);
            font-family: var(--font-family);
            min-height: 100vh;
            color: var(--neutral-800);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Barra de rolagem personalizada */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--neutral-200);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--neutral-400);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--neutral-500);
        }

        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: var(--shadow-md);
            padding: 0.8rem 0;
            z-index: 1040;
            position: sticky;
            top: 0;
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
            transition: all 0.3s var(--transition-function);
            overflow: hidden;
            background-color: white;
            position: relative;
            margin-bottom: var(--spacing-lg);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--neutral-200);
            padding: var(--spacing-lg);
            font-weight: 600;
        }

        .card-body {
            padding: var(--spacing-lg);
        }

        /* Card destacado */
        .card.card-highlighted {
            border-left: 4px solid var(--primary-color);
            background: linear-gradient(to right, var(--primary-light), white 15%);
        }

        .card.card-highlighted:hover {
            border-left-width: 6px;
        }

        /* Card de disciplina */
        .card.disciplina-card {
            transition: all 0.3s var(--transition-function);
            border-top: 3px solid transparent;
        }

        .card.disciplina-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
            border-top-color: var(--primary-color);
        }

        /* Card com destaques coloridos */
        .card.card-success-accent {
            border-top: 3px solid var(--success-color);
        }

        .card.card-warning-accent {
            border-top: 3px solid var(--warning-color);
        }

        .card.card-danger-accent {
            border-top: 3px solid var(--danger-color);
        }

        .card.card-accent {
            border-top: 3px solid var(--accent-color);
        }

        .disciplina-nome {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--neutral-800);
            margin-bottom: 0.2rem;
        }

        .disciplina-codigo {
            font-size: 0.85rem;
            color: var(--neutral-600);
            font-weight: 500;
            margin-bottom: var(--spacing-md);
        }

        .table {
            width: 100%;
            border-radius: var(--card-border-radius);
            box-shadow: var(--shadow-sm);
            background-color: white;
            overflow: hidden;
            margin-bottom: var(--spacing-lg);
            border-collapse: separate;
            border-spacing: 0;
        }

        .table thead th {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            font-weight: 500;
            border: none;
            padding: 1rem;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table thead th:first-child {
            border-top-left-radius: var(--card-border-radius);
        }

        .table thead th:last-child {
            border-top-right-radius: var(--card-border-radius);
        }

        .table tbody tr {
            transition: background-color var(--transition-speed);
            border-bottom: 1px solid var(--neutral-200);
        }

        .table tbody tr:last-child {
            border-bottom: none;
        }

        .table tbody tr:hover {
            background-color: var(--primary-light);
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
        }

        .badge {
            padding: 0.5rem 0.8rem;
            border-radius: 10px;
            font-weight: 500;
            letter-spacing: 0.02em;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }

        .badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.08);
        }

        .btn {
            border-radius: 10px;
            padding: 0.6rem 1.2rem;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%, -50%);
            transform-origin: 50% 50%;
        }

        .btn:hover::after {
            animation: ripple 0.6s ease-out;
        }

        @keyframes ripple {
            0% {
                opacity: 0.5;
                transform: scale(0, 0);
            }

            100% {
                opacity: 0;
                transform: scale(30, 30);
            }
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
            transition: all 0.25s ease;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }

        .disciplina-info:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary-color);
            z-index: -1;
            transition: width 0.25s ease;
        }

        .disciplina-info:hover {
            transform: translateX(4px);
            box-shadow: var(--shadow-md);
            color: white;
        }

        .disciplina-info:hover:before {
            width: 100%;
        }

        .disciplina-info strong {
            font-size: 0.95rem;
            transition: all 0.25s ease;
            position: relative;
            z-index: 2;
        }

        .disciplina-info small {
            transition: all 0.25s ease;
            position: relative;
            z-index: 2;
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            margin-top: 3px;
            color: var(--neutral-700);
        }

        .disciplina-info:hover strong,
        .disciplina-info:hover small {
            color: white;
        }

        .disciplina-info .text-muted {
            transition: all 0.25s ease;
            position: relative;
            z-index: 2;
        }

        .disciplina-info:hover .text-muted {
            color: rgba(255, 255, 255, 0.8) !important;
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

        /* Estilos específicos para o boletim */
        .table-boletim {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            overflow: hidden;
            border-radius: var(--card-border-radius);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.03);
        }

        .table-boletim thead th {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1.2rem 1rem;
            text-align: center;
            position: relative;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
        }

        .table-boletim thead th:first-child {
            text-align: left;
            padding-left: 1.5rem;
        }

        .table-boletim tbody td {
            padding: 1.2rem 1rem;
            border-bottom: 1px solid var(--neutral-200);
            vertical-align: middle;
            transition: all 0.25s ease;
        }

        .table-boletim tbody td:first-child {
            padding-left: 1.5rem;
        }

        .table-boletim tr:last-child td {
            border-bottom: none;
        }

        .table-boletim tr {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .table-boletim tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(115, 83, 186, 0.08);
            z-index: 1;
            position: relative;
        }

        .table-boletim tr:hover td {
            background-color: var(--primary-light);
        }

        .disciplina-nome {
            font-size: 1.05rem;
            color: var(--primary-dark);
            font-weight: 600;
            margin: 0.5rem 0 0.2rem;
            transition: all 0.2s ease;
            line-height: 1.4;
        }

        tr:hover .disciplina-nome {
            color: var(--primary-color);
            transform: translateX(3px);
        }

        .disciplina-codigo {
            display: inline-block;
            font-size: 0.7rem;
            background-color: var(--neutral-200);
            color: var(--neutral-700);
            border-radius: 4px;
            padding: 0.15rem 0.5rem;
            font-weight: 500;
            letter-spacing: 0.05em;
            margin-right: 0.5rem;
        }

        /* Visualização de situação de notas */
        .situacao-aprovado,
        .situacao-reprovado,
        .situacao-cursando {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .situacao-aprovado {
            background-color: rgba(16, 185, 129, 0.2);
            color: #047857;
            /* Cor mais escura para melhor contraste */
            border-left: 4px solid var(--success-color);
        }

        .situacao-reprovado {
            background-color: rgba(239, 68, 68, 0.2);
            color: #b91c1c;
            /* Cor mais escura para melhor contraste */
            border-left: 4px solid var(--danger-color);
        }

        .situacao-cursando {
            background-color: rgba(6, 182, 212, 0.2);
            color: #0e7490;
            /* Cor mais escura para melhor contraste */
            border-left: 4px solid var(--info-color);
        }

        /* Animação de entrada para elementos importantes */
        .boletim-item {
            animation: fadeInUp 0.5s ease-out forwards;
            opacity: 0;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .disciplina-nome {
            display: block;
            margin-top: 0.5rem;
            font-size: 1.1rem;
            color: var(--primary-dark);
            font-weight: 600;
            line-height: 1.4;
            letter-spacing: -0.01em;
            transition: all 0.25s ease;
        }

        .disciplina-item:hover .disciplina-nome {
            transform: translateX(3px);
        }

        .disciplina-codigo {
            display: inline-block;
            font-size: 0.7rem;
            color: var(--neutral-500);
            font-weight: 500;
            background-color: var(--neutral-200);
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            margin-right: 0.5rem;
            letter-spacing: 0.05em;
        }

        /* Toast notifications com estilo melhorado */
        .toast {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border: none;
            opacity: 1 !important;
        }

        /* Melhorias específicas para tabelas */
        .table {
            border-radius: var(--card-border-radius);
            overflow: hidden;
        }

        .table thead th {
            white-space: nowrap;
        }

        /* Status de disciplina */
        .status-indicador {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }

        .status-verde {
            background-color: var(--success-color);
        }

        .status-amarelo {
            background-color: var(--warning-color);
        }

        .status-vermelho {
            background-color: var(--danger-color);
        }

        /* Animações suaves para transições de seção */
        .section-transition {
            transition: all 0.5s ease;
        }

        .section-transition:hover {
            transform: translateY(-5px);
        }

        /* Table horário estilizada */
        .table-horario {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            border-radius: var(--card-border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .table-horario thead th {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 1rem;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }

        .table-horario tbody tr:hover {
            background-color: var(--primary-light);
        }

        .table-horario td {
            padding: 0.75rem;
            transition: all 0.25s ease;
            border: 1px solid var(--neutral-200);
        }

        /* Melhorias para sistema de cards e cores */
        .card.bg-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)) !important;
        }

        .card.bg-primary .badge.bg-white {
            color: var(--primary-dark) !important;
        }

        .card-header.bg-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)) !important;
            border-bottom: none;
        }

        .card-header.bg-primary-dark {
            background-color: var(--primary-dark) !important;
            color: white;
            border-bottom: none;
        }

        /* Status melhorado para indicadores */
        .status-indicador {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
            animation: pulsar 1.5s ease-in-out infinite;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.5);
        }

        @keyframes pulsar {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.2);
                opacity: 0.8;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .status-verde {
            background-color: var(--success-color);
            box-shadow: 0 0 0 rgba(16, 185, 129, 0.4);
        }

        .status-amarelo {
            background-color: var(--warning-color);
            box-shadow: 0 0 0 rgba(245, 158, 11, 0.4);
        }

        .status-vermelho {
            background-color: var(--danger-color);
            box-shadow: 0 0 0 rgba(239, 68, 68, 0.4);
        }

        .disciplina-nome-pequeno {
            color: var(--primary-dark);
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            vertical-align: middle;
        }

        .list-group-item:hover .disciplina-nome-pequeno {
            color: var(--primary-color);
        }
    </style>
</head>

<body>
    <div class="loading-bar" id="loadingBar"></div>

    <!-- Notificações toast -->
    <div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 5000;"></div>

    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i>
                SUPACO
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

                <!-- Modal para visualização de dados da API -->
                <div class="modal fade" id="apiInfoModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header bg-dark text-white border-0">
                                <h5 class="modal-title">
                                    <i class="fas fa-code me-2"></i>
                                    Dados da API
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div class="nav nav-tabs" id="apiTabs" role="tablist">
                                    <button class="nav-link active" id="meusDados-tab" data-bs-toggle="tab"
                                        data-bs-target="#meusDados-tab-pane" type="button" role="tab">
                                        Meus Dados
                                    </button>
                                    <button class="nav-link" id="boletim-tab" data-bs-toggle="tab"
                                        data-bs-target="#boletim-tab-pane" type="button" role="tab">
                                        Boletim
                                    </button>
                                    <button class="nav-link" id="horarios-tab" data-bs-toggle="tab"
                                        data-bs-target="#horarios-tab-pane" type="button" role="tab">
                                        Horários
                                    </button>
                                </div>
                                <div class="tab-content p-3" id="apiTabsContent">
                                    <div class="tab-pane fade show active" id="meusDados-tab-pane" role="tabpanel" tabindex="0">
                                        <pre class="bg-light p-3 rounded" style="max-height: 70vh; overflow: auto;"><code><?php
                                                                                                                            echo isset($apiResponses['meusDados']) ? json_encode($apiResponses['meusDados'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'Dados não disponíveis';
                                                                                                                            ?></code></pre>
                                    </div>
                                    <div class="tab-pane fade" id="boletim-tab-pane" role="tabpanel" tabindex="0">
                                        <pre class="bg-light p-3 rounded" style="max-height: 70vh; overflow: auto;"><code><?php
                                                                                                                            echo isset($apiResponses['boletim']) ? json_encode($apiResponses['boletim'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'Dados não disponíveis';
                                                                                                                            ?></code></pre>
                                    </div>
                                    <div class="tab-pane fade" id="horarios-tab-pane" role="tabpanel" tabindex="0">
                                        <pre class="bg-light p-3 rounded" style="max-height: 70vh; overflow: auto;"><code><?php
                                                                                                                            echo isset($apiResponses['horarios']) ? json_encode($apiResponses['horarios'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : 'Dados não disponíveis';
                                                                                                                            ?></code></pre>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i>
                                    Fechar
                                </button>
                            </div>
                        </div>
                    </div>
                </div> <!-- Scripts -->
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
                <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
                <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/luxon@3.3.0/build/global/luxon.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@1.3.1/dist/chartjs-adapter-luxon.umd.min.js"></script>
                <script src="assets/day-selector.js"></script>
                <script src="assets/notification.js"></script>
                <script>
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
                            style: {
                                background: bgColors[type],
                                boxShadow: "0 3px 10px rgba(0,0,0,0.1)",
                                borderRadius: "8px",
                            }
                        }).showToast();
                    }

                    // Inicialização dos elementos da interface
                    document.addEventListener('DOMContentLoaded', function() {
                        // Mostrar notificação de boas-vindas
                        setTimeout(() => {
                            showToast('Bem-vindo(a) ao SUPACO! Dados acadêmicos carregados.', 'success');
                        }, 1000);

                        // Inicializar seletor de dias
                        const daySelector = document.getElementById('daySelector');
                        if (daySelector) {
                            daySelector.addEventListener('change', function() {
                                if (this.value) {
                                    window.location.href = `index.php?data=${this.value}`;
                                }
                            });
                        }

                        // Animações para itens do boletim
                        const disciplinaItems = document.querySelectorAll('.disciplina-item');
                        disciplinaItems.forEach((item, index) => {
                            item.classList.add('boletim-item');
                            item.style.animationDelay = `${0.1 + index * 0.05}s`;
                        });
                    });

                    // Inicialização do tema e eventos        
                    document.addEventListener('DOMContentLoaded', function() {
                        // Inicializa animações AOS
                        AOS.init({
                            duration: 800,
                            easing: 'ease-in-out',
                            once: true,
                            mirror: false
                        });

                        // Loading bar
                        const loadingBar = document.getElementById('loadingBar');
                        document.querySelectorAll('a').forEach(link => {
                            if (!link.getAttribute('target') && !link.dataset.bsToggle) {
                                link.addEventListener('click', function(e) {
                                    if (link.href && !link.href.includes('#') && !e.ctrlKey && !e.metaKey) {
                                        loadingBar.style.display = 'block';
                                    }
                                });
                            }
                        });

                        // Tooltips inicialização
                        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                        tooltipTriggerList.map(function(tooltipTriggerEl) {
                            return new bootstrap.Tooltip(tooltipTriggerEl, {
                                boundary: document.body
                            });
                        });

                        // Popovers inicialização
                        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
                        popoverTriggerList.map(function(popoverTriggerEl) {
                            return new bootstrap.Popover(popoverTriggerEl);
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
                <!-- Modal de Ajuda sobre Frequência -->
                <div class="modal fade" id="frequenciaHelpModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header bg-primary text-white border-0">
                                <h5 class="modal-title">
                                    <i class="fas fa-question-circle me-2"></i>
                                    Entendendo sua Frequência
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="mb-4">
                                    <h6 class="border-bottom pb-2 mb-2">Como calcular as faltas</h6>
                                    <p class="text-muted small">
                                        Para aprovação, você precisa ter no mínimo <strong>75% de frequência</strong> em cada disciplina.
                                        Isso significa que você pode faltar até <strong>25% da carga horária</strong> total da disciplina.
                                    </p>
                                    <div class="bg-light p-2 rounded small">
                                        <strong>Exemplo:</strong> Em uma disciplina de 80 horas, você pode faltar até 20 horas
                                        (25% de 80h = 20h).
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h6 class="border-bottom pb-2 mb-2">O que significam as cores?</h6>
                                    <ul class="small list-unstyled">
                                        <li class="mb-2">
                                            <span class="badge bg-success bg-opacity-20 text-success px-3 py-1">
                                                <i class="fas fa-check-circle me-1"></i> Verde
                                            </span>
                                            <span class="ms-2">Você pode faltar com segurança (mais de 3 faltas disponíveis)</span>
                                        </li>
                                        <li class="mb-2">
                                            <span class="badge bg-warning bg-opacity-20 text-warning px-3 py-1">
                                                <i class="fas fa-exclamation-circle me-1"></i> Amarelo
                                            </span>
                                            <span class="ms-2">Alerta! Você tem 3 ou menos faltas disponíveis</span>
                                        </li>
                                        <li>
                                            <span class="badge bg-danger bg-opacity-20 text-danger px-3 py-1">
                                                <i class="fas fa-exclamation-triangle me-1"></i> Vermelho
                                            </span>
                                            <span class="ms-2">Risco de reprovação! Limite de faltas atingido ou excedido</span>
                                        </li>
                                    </ul>
                                </div>

                                <div>
                                    <h6 class="border-bottom pb-2 mb-2">Como interpretar o painel</h6>
                                    <ul class="small">
                                        <li>
                                            <strong>Frequência atual:</strong> Seu percentual de presença nas aulas já ministradas
                                        </li>
                                        <li>
                                            <strong>Após falta:</strong> Como sua frequência ficaria se você faltasse mais um dia de aula
                                        </li>
                                        <li>
                                            <strong>Faltas utilizadas:</strong> Quantas faltas você já teve em relação ao máximo permitido
                                        </li>
                                        <li>
                                            <strong>Aulas dadas:</strong> Quantas aulas já foram ministradas do total previsto
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                                    <i class="fas fa-check me-1"></i>
                                    Entendi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal da Calculadora de Frequência -->
                <div class="modal fade" id="calculadoraFrequenciaModal" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header bg-primary text-white border-0">
                                <h5 class="modal-title">
                                    <i class="fas fa-calculator me-2"></i>
                                    Calculadora de Frequência
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <form id="calculadoraFrequenciaForm">
                                    <div class="mb-3">
                                        <label for="cargaHoraria" class="form-label">Carga horária total da disciplina (horas):</label>
                                        <input type="number" class="form-control" id="cargaHoraria" min="1" value="60" required>
                                        <div class="form-text">Geralmente 30h, 60h, 80h ou mais conforme seu curso.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="aulasDadas" class="form-label">Aulas já ministradas (horas):</label>
                                        <input type="number" class="form-control" id="aulasDadas" min="0" value="30" required>
                                        <div class="form-text">Quantas horas de aula já foram dadas até o momento.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="faltasAtuais" class="form-label">Faltas atuais (horas):</label>
                                        <input type="number" class="form-control" id="faltasAtuais" min="0" value="4" required>
                                        <div class="form-text">Quantas horas de aula você já faltou até agora.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="faltasAdicionais" class="form-label">Faltas a simular (horas):</label>
                                        <input type="number" class="form-control" id="faltasAdicionais" min="1" value="2" required>
                                        <div class="form-text">Quantas horas adicionais você pretende faltar.</div>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-calculator me-1"></i>
                                        Calcular
                                    </button>
                                </form>

                                <div id="resultadoFrequencia" class="mt-4 d-none">
                                    <h6 class="border-bottom pb-2 mb-3">Resultado da Simulação</h6>
                                    <div class="row text-center">
                                        <div class="col-6 mb-3">
                                            <div class="small text-muted">Frequência Atual</div>
                                            <div id="freqAtual" class="h4 text-primary">93.3%</div>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <div class="small text-muted">Frequência Após Faltas</div>
                                            <div id="freqNova" class="h4">86.7%</div>
                                        </div>
                                    </div>

                                    <div class="progress mb-3" style="height: 8px;">
                                        <div id="barraFreq" class="progress-bar bg-success" style="width: 86.7%;"></div>
                                    </div>

                                    <div class="text-center">
                                        <div id="statusFreq" class="alert alert-success p-2">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Você continuará com frequência suficiente
                                        </div>

                                        <div id="detalhesFreq" class="small text-muted mt-2">
                                            Máximo de faltas permitidas: <span id="maxFaltas">15h</span> (25% da carga horária)
                                            <br>
                                            Faltas após simulação: <span id="totalFaltas">6h</span> de <span id="maxFaltasRepeat">15h</span>
                                            <br>
                                            Você ainda pode faltar <span id="faltasRestantes">9h</span> sem reprovar
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i> Fechar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Script para a calculadora de frequência -->
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const calcForm = document.getElementById('calculadoraFrequenciaForm');

                        calcForm.addEventListener('submit', function(e) {
                            e.preventDefault();

                            // Obter os valores do formulário
                            const cargaHoraria = Number(document.getElementById('cargaHoraria').value);
                            const aulasDadas = Number(document.getElementById('aulasDadas').value);
                            const faltasAtuais = Number(document.getElementById('faltasAtuais').value);
                            const faltasAdicionais = Number(document.getElementById('faltasAdicionais').value);

                            // Validações básicas
                            if (aulasDadas > cargaHoraria) {
                                alert('As aulas dadas não podem ser maiores que a carga horária total.');
                                return;
                            }

                            if (faltasAtuais > aulasDadas) {
                                alert('As faltas não podem ser maiores que as aulas já dadas.');
                                return;
                            }

                            // Cálculos de frequência
                            const maximoFaltas = Math.ceil(cargaHoraria * 0.25);
                            const totalFaltas = faltasAtuais + faltasAdicionais;
                            const faltasRestantes = Math.max(0, maximoFaltas - totalFaltas);

                            const frequenciaAtual = ((aulasDadas - faltasAtuais) / aulasDadas) * 100;
                            const frequenciaNova = ((aulasDadas - totalFaltas) / aulasDadas) * 100;

                            // Definir status
                            let status = 'success';
                            let mensagem = 'Você continuará com frequência suficiente';

                            if (totalFaltas > maximoFaltas) {
                                status = 'danger';
                                mensagem = 'Você ultrapassará o limite de faltas!';
                            } else if ((maximoFaltas - totalFaltas) <= 3) {
                                status = 'warning';
                                mensagem = 'Você estará próximo ao limite de faltas!';
                            }

                            // Atualizar a interface
                            document.getElementById('freqAtual').textContent = frequenciaAtual.toFixed(1) + '%';
                            document.getElementById('freqNova').textContent = Math.max(0, frequenciaNova).toFixed(1) + '%';

                            document.getElementById('freqNova').className = 'h4 text-' + status;

                            document.getElementById('barraFreq').className = 'progress-bar bg-' + status;
                            document.getElementById('barraFreq').style.width = Math.max(0, Math.min(100, frequenciaNova)) + '%';

                            document.getElementById('statusFreq').className = 'alert alert-' + status + ' p-2';
                            document.getElementById('statusFreq').innerHTML = `<i class="fas fa-${status === 'success' ? 'check' : 'exclamation'}-circle me-1"></i> ${mensagem}`;

                            document.getElementById('maxFaltas').textContent = maximoFaltas + 'h';
                            document.getElementById('maxFaltasRepeat').textContent = maximoFaltas + 'h';
                            document.getElementById('totalFaltas').textContent = totalFaltas + 'h';
                            document.getElementById('faltasRestantes').textContent = faltasRestantes + 'h';

                            // Mostrar resultados
                            document.getElementById('resultadoFrequencia').classList.remove('d-none');
                        });
                    });

                    // Inicializar biblioteca AOS para animações ao rolar
                    AOS.init({
                        duration: 800,
                        easing: 'ease-in-out',
                        once: true
                    });
                </script>
</body>

</html>