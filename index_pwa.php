<?php
// Página inicial adaptada para o modo PWA e suporte offline
session_start();
require_once 'base_pwa.php';

// Conteúdo da página
$pageTitle = 'SUPACO - Modo PWA';
$pageContent = '
<div class="container py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-highlighted mb-4">
                <div class="card-body p-4">
                    <h2 class="mb-3">
                        <i class="fas fa-graduation-cap me-2 text-primary"></i>
                        Bem-vindo ao SUPACO PWA
                    </h2>
                    <p class="lead">
                        Acesse suas informações acadêmicas de qualquer lugar, mesmo sem internet!
                    </p>
                    <p>
                        O SUPACO agora funciona como um Progressive Web App (PWA), permitindo
                        que você acesse suas notas, horários e outras informações importantes
                        mesmo quando estiver sem conexão com a internet.
                    </p>
                    
                    <div id="offlineAlert" class="alert alert-warning d-none">
                        <div class="d-flex">
                            <div class="me-3 fs-3">
                                <i class="fas fa-wifi-slash"></i>
                            </div>
                            <div>
                                <h5 class="alert-heading">Você está offline</h5>
                                <p class="mb-0">
                                    Você ainda pode acessar os dados que foram previamente salvos.
                                    Algumas funcionalidades podem estar limitadas até que a conexão seja restabelecida.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-star me-2"></i>
                                Boletim
                            </h5>
                        </div>
                        <div class="card-body">
                            <p>Acesse suas notas e médias em todas as disciplinas, mesmo sem internet.</p>
                            <div class="d-grid">
                                <a href="boletim_offline.php" class="btn btn-primary">
                                    <i class="fas fa-chevron-right me-2"></i>
                                    Ver Boletim
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Horários
                            </h5>
                        </div>
                        <div class="card-body">
                            <p>Confira seus horários de aulas da semana, mesmo sem conexão.</p>
                            <div class="d-grid">
                                <a href="horarios_offline.php" class="btn btn-primary">
                                    <i class="fas fa-chevron-right me-2"></i>
                                    Ver Horários
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>
                        Ferramentas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-grid">
                                <button class="btn btn-outline-primary" id="calcMediaBtn">
                                    <i class="fas fa-calculator me-2"></i>
                                    Calculadora de Média
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-grid">
                                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#calculadoraFrequenciaModal">
                                    <i class="fas fa-check-double me-2"></i>
                                    Calculadora de Frequência
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-sync-alt me-2"></i>
                        Status do Sistema
                    </h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <i class="fas fa-wifi me-2"></i>
                                Status de Conexão
                            </div>
                            <div>
                                <span id="conexaoStatus" class="badge bg-success">Online</span>
                            </div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <i class="fas fa-database me-2"></i>
                                Dados Offline
                            </div>
                            <div>
                                <a href="offline.php" class="btn btn-sm btn-primary">
                                    Gerenciar
                                </a>
                            </div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <i class="fas fa-sync-alt me-2"></i>
                                Sincronizar Dados
                            </div>
                            <div>
                                <button id="syncButton" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-sync-alt me-1"></i> Sincronizar
                                </button>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informações
                    </h5>
                </div>
                <div class="card-body">
                    <p class="small mb-3">
                        Esta versão do SUPACO funciona como um Progressive Web App (PWA), permitindo 
                        que você tenha acesso aos seus dados acadêmicos mesmo sem internet.
                    </p>
                    <div class="alert alert-info mb-0 p-2 small">
                        <i class="fas fa-lightbulb me-2"></i>
                        Dica: Adicione esta aplicação à tela inicial do seu dispositivo para um acesso mais rápido!
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Verificar status de conexão
    const conexaoStatus = document.getElementById("conexaoStatus");
    const offlineAlert = document.getElementById("offlineAlert");
    
    function updateConnectionStatus() {
        if (navigator.onLine) {
            conexaoStatus.className = "badge bg-success";
            conexaoStatus.textContent = "Online";
            offlineAlert.classList.add("d-none");
        } else {
            conexaoStatus.className = "badge bg-warning";
            conexaoStatus.textContent = "Offline";
            offlineAlert.classList.remove("d-none");
        }
    }
    
    // Verificar status inicial
    updateConnectionStatus();
    
    // Ouvir mudanças na conexão
    window.addEventListener("online", updateConnectionStatus);
    window.addEventListener("offline", updateConnectionStatus);
    
    // Configurar botão de sincronização
    const syncButton = document.getElementById("syncButton");
    syncButton.addEventListener("click", function() {
        syncAllData();
    });
});
</script>
';

// Se não estiver logado, redirecionar para a página de login
if (!isset($_SESSION['access_token'])) {
    // Mostrar mensagem se estiver offline
    if (isset($_GET['offline']) && $_GET['offline'] == 'true') {
        $pageContent = '
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-lg">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-wifi-slash me-2"></i>
                                Sem conexão
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <i class="fas fa-exclamation-triangle text-danger fa-4x mb-3"></i>
                                <h4>Não foi possível acessar sua conta</h4>
                                <p class="text-muted">
                                    Você está offline e ainda não havia feito login anteriormente. 
                                    É necessário se conectar à internet para fazer login pela primeira vez.
                                </p>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Tente novamente quando tiver uma conexão com a internet.
                            </div>
                            
                            <div class="d-grid">
                                <button class="btn btn-primary" onclick="checkConnection()">
                                    <i class="fas fa-sync-alt me-2"></i>
                                    Tentar novamente
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            function checkConnection() {
                if (navigator.onLine) {
                    window.location.href = "login.php";
                } else {
                    showToast("Você ainda está offline. Tente novamente quando tiver conexão.", "warning");
                }
            }
        </script>
        ';
    } else {
        header("Location: login.php");
        exit;
    }
}
