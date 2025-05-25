<?php
// Configurações para ativar o PWA
session_start();
require_once 'base_pwa.php';

// Conteúdo da página 
$pageTitle = 'Modos Offline - SUPACO';
$pageContent = '
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-highlighted mb-4">
                <div class="card-body p-4">
                    <h2 class="mb-3">
                        <i class="fas fa-wifi-slash me-2 text-primary"></i>
                        Modo Offline
                    </h2>
                    <p class="lead">
                        Agora você pode acessar suas informações acadêmicas mesmo sem internet!
                    </p>
                    <p>
                        O SUPACO agora funciona como um Progressive Web App (PWA), permitindo que você acesse suas notas, 
                        horários e outras informações importantes mesmo quando estiver sem conexão com a internet.
                    </p>
                    
                    <div class="alert alert-info">
                        <div class="d-flex">
                            <div class="me-3 fs-3">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div>
                                <h5 class="alert-heading">Como funciona?</h5>
                                <p class="mb-0">
                                    Navegue pelo sistema normalmente enquanto estiver online. 
                                    Os dados serão automaticamente salvos para uso offline. 
                                    Quando você ficar sem internet, ainda poderá acessar os dados que foram 
                                    previamente armazenados.
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
                                <i class="fas fa-download me-2"></i>
                                Instalar como App
                            </h5>
                        </div>
                        <div class="card-body">
                            <p>Adicione o SUPACO à tela inicial do seu dispositivo para acesso mais rápido:</p>
                            <ul class="list-unstyled">
                                <li class="mb-3">
                                    <i class="fab fa-chrome text-primary me-2"></i>
                                    <strong>Chrome (Android):</strong>
                                    <div class="text-muted ms-4 small">Menu ⋮ → Adicionar à tela inicial</div>
                                </li>
                                <li class="mb-3">
                                    <i class="fab fa-safari text-primary me-2"></i>
                                    <strong>Safari (iOS):</strong>
                                    <div class="text-muted ms-4 small">Compartilhar → Adicionar à Tela de Início</div>
                                </li>
                                <li>
                                    <i class="fab fa-edge text-primary me-2"></i>
                                    <strong>Edge/Chrome (PC):</strong>
                                    <div class="text-muted ms-4 small">Menu ⋮ → Instalar SUPACO</div>
                                </li>
                            </ul>
                            <button id="installPWA" class="btn btn-primary mt-2 w-100 d-none">
                                <i class="fas fa-download me-2"></i> Instalar como App
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-database me-2"></i>
                                Dados Disponíveis Offline
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="offlineDataStatus">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Carregando...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Verificando dados offline...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-sync-alt me-2"></i>
                        Sincronização de Dados
                    </h5>
                </div>
                <div class="card-body">
                    <p>
                        Para garantir que você tenha os dados mais recentes quando estiver offline, 
                        sincronize-os periodicamente enquanto estiver conectado à internet:
                    </p>
                    
                    <div class="d-grid">
                        <button id="syncDataButton" class="btn btn-primary">
                            <i class="fas fa-sync-alt me-2"></i>
                            Sincronizar Dados Agora
                        </button>
                    </div>
                    
                    <div class="mt-3 small text-muted">
                        <p>
                            <i class="fas fa-info-circle me-1"></i>
                            Última sincronização: <span id="lastSyncTime">Nunca</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Verificar dados offline disponíveis
    checkOfflineDataStatus();
    
    // Botão de sincronização
    document.getElementById("syncDataButton").addEventListener("click", function() {
        syncAllData().then(success => {
            if (success) {
                updateLastSyncTime();
                checkOfflineDataStatus();
            }
        });
    });
    
    // Verificar se a instalação do PWA está disponível
    let deferredPrompt;
    const installButton = document.getElementById("installPWA");
    
    window.addEventListener("beforeinstallprompt", (e) => {
        // Prevenir o comportamento padrão
        e.preventDefault();
        // Guardar o evento para poder disparar depois
        deferredPrompt = e;
        // Mostrar o botão de instalação
        installButton.classList.remove("d-none");
        
        // Adicionar o evento de clique
        installButton.addEventListener("click", async () => {
            // Mostrar o prompt
            deferredPrompt.prompt();
            // Esperar pela escolha do usuário
            const { outcome } = await deferredPrompt.userChoice;
            // O prompt não pode ser usado novamente
            deferredPrompt = null;
            // Esconder o botão
            installButton.classList.add("d-none");
            
            if (outcome === "accepted") {
                console.log("Usuário aceitou a instalação");
                showToast("App instalado com sucesso!", "success");
            } else {
                console.log("Usuário recusou a instalação");
            }
        });
    });
    
    // Verificar última sincronização
    const lastSync = localStorage.getItem("lastSyncTime");
    if (lastSync) {
        updateLastSyncTimeUI(lastSync);
    }
});

// Função para verificar e exibir status dos dados offline
async function checkOfflineDataStatus() {
    const container = document.getElementById("offlineDataStatus");
    
    try {
        // Obter todos os dados salvos
        const allData = await offlineStorage.getAllData();
        
        // Se não houver dados
        if (allData.length === 0) {
            container.innerHTML = `
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Nenhum dado foi salvo para uso offline ainda. Navegue pelo sistema para salvar dados.
                </div>
            `;
            return;
        }
        
        // Criar HTML para cada tipo de dado
        let html = "";
        
        for (const item of allData) {
            const date = new Date(item.timestamp);
            const formattedDate = date.toLocaleDateString() + " às " + date.toLocaleTimeString();
            let icon, title;
            
            switch(item.id) {
                case "boletim":
                    icon = "fas fa-star";
                    title = "Boletim e Notas";
                    break;
                case "horarios":
                    icon = "fas fa-calendar-alt";
                    title = "Horários de Aula";
                    break;
                case "meusDados":
                    icon = "fas fa-user";
                    title = "Dados Pessoais";
                    break;
                default:
                    icon = "fas fa-file";
                    title = item.id;
            }
            
            html += `
                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                    <div class="me-3 fs-4 text-primary">
                        <i class="${icon}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0">${title}</h6>
                        <div class="small text-muted">
                            Atualizado em: ${formattedDate}
                        </div>
                    </div>
                    <div>
                        <span class="badge bg-success">Disponível offline</span>
                    </div>
                </div>
            `;
        }
        
        container.innerHTML = html;
        
    } catch (error) {
        console.error("Erro ao verificar dados offline:", error);
        container.innerHTML = `
            <div class="alert alert-danger mb-0">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Erro ao verificar dados offline: ${error.message}
            </div>
        `;
    }
}

// Função para atualizar a hora da última sincronização
function updateLastSyncTime() {
    const now = new Date().toISOString();
    localStorage.setItem("lastSyncTime", now);
    updateLastSyncTimeUI(now);
}

// Função para atualizar a UI com a hora da última sincronização
function updateLastSyncTimeUI(timeString) {
    const date = new Date(timeString);
    const formattedDate = date.toLocaleDateString() + " às " + date.toLocaleTimeString();
    document.getElementById("lastSyncTime").textContent = formattedDate;
}
</script>
';
