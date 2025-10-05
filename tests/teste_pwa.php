<?php
// Página de teste para verificar funcionalidades PWA
session_start();
require_once 'base_pwa.php';

$pageTitle = 'Teste PWA - SUPACO';
$pageContent = '
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        Teste de Funcionalidades PWA
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Status de Conexão -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-wifi me-2"></i>
                                        Status de Conexão
                                    </h5>
                                    <p id="connectionStatus" class="fw-bold fs-5">
                                        <span data-connection-status>Verificando...</span>
                                    </p>
                                    <button class="btn btn-outline-primary" onclick="testConnection()">
                                        <i class="fas fa-sync-alt me-2"></i>
                                        Verificar Conexão
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Service Worker -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-worker me-2"></i>
                                        Service Worker
                                    </h5>
                                    <p id="swStatus">Verificando...</p>
                                    <button class="btn btn-outline-success" onclick="testServiceWorker()">
                                        <i class="fas fa-check me-2"></i>
                                        Verificar SW
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Cache -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-database me-2"></i>
                                        Cache
                                    </h5>
                                    <p id="cacheStatus">Verificando...</p>
                                    <button class="btn btn-outline-info" onclick="testCache()">
                                        <i class="fas fa-search me-2"></i>
                                        Verificar Cache
                                    </button>
                                    <button class="btn btn-outline-danger ms-2" onclick="clearCache()">
                                        <i class="fas fa-trash me-2"></i>
                                        Limpar Cache
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- IndexedDB -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-hdd me-2"></i>
                                        IndexedDB
                                    </h5>
                                    <p id="dbStatus">Verificando...</p>
                                    <button class="btn btn-outline-primary" onclick="testIndexedDB()">
                                        <i class="fas fa-test me-2"></i>
                                        Testar IndexedDB
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Manifest -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-file-code me-2"></i>
                                        Manifest
                                    </h5>
                                    <p id="manifestStatus">Verificando...</p>
                                    <button class="btn btn-outline-success" onclick="testManifest()">
                                        <i class="fas fa-check me-2"></i>
                                        Verificar Manifest
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Instalação PWA -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-download me-2"></i>
                                        Instalação PWA
                                    </h5>
                                    <p id="installStatus">Verificando...</p>
                                    <button class="btn btn-success" id="installButton" style="display: none;" onclick="installPWA()">
                                        <i class="fas fa-download me-2"></i>
                                        Instalar App
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Dados Offline -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-cloud-download-alt me-2"></i>
                                        Teste de Dados Offline
                                    </h5>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <button class="btn btn-primary w-100" onclick="testSaveData()">
                                                <i class="fas fa-save me-2"></i>
                                                Salvar Dados de Teste
                                            </button>
                                        </div>
                                        <div class="col-md-4">
                                            <button class="btn btn-info w-100" onclick="testLoadData()">
                                                <i class="fas fa-download me-2"></i>
                                                Carregar Dados
                                            </button>
                                        </div>
                                        <div class="col-md-4">
                                            <button class="btn btn-warning w-100 online-only" onclick="testSync()">
                                                <i class="fas fa-sync me-2"></i>
                                                Testar Sincronização
                                            </button>
                                        </div>
                                    </div>
                                    <div id="dataTestResult" class="mt-3"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Resultados dos Testes -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-list-alt me-2"></i>
                                        Resultados dos Testes
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div id="testResults">
                                        <p class="text-muted">Execute os testes acima para ver os resultados aqui.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let deferredPrompt;
let testResults = [];

// Captura o evento beforeinstallprompt
window.addEventListener("beforeinstallprompt", (e) => {
    e.preventDefault();
    deferredPrompt = e;
    document.getElementById("installButton").style.display = "block";
    document.getElementById("installStatus").textContent = "App pode ser instalado";
});

// Teste de conexão
function testConnection() {
    const status = navigator.onLine ? "Online" : "Offline";
    const color = navigator.onLine ? "text-success" : "text-warning";
    
    document.getElementById("connectionStatus").innerHTML = 
        `<span class="${color}">${status}</span>`;
    
    addTestResult("Conexão", status, navigator.onLine);
}

// Teste de Service Worker
async function testServiceWorker() {
    if ("serviceWorker" in navigator) {
        try {
            const registration = await navigator.serviceWorker.getRegistration();
            if (registration) {
                document.getElementById("swStatus").innerHTML = 
                    "<span class=\'text-success\'>✓ Service Worker registrado</span>";
                addTestResult("Service Worker", "Registrado", true);
            } else {
                document.getElementById("swStatus").innerHTML = 
                    "<span class=\'text-warning\'>⚠ Service Worker não encontrado</span>";
                addTestResult("Service Worker", "Não encontrado", false);
            }
        } catch (error) {
            document.getElementById("swStatus").innerHTML = 
                "<span class=\'text-danger\'>✗ Erro ao verificar SW</span>";
            addTestResult("Service Worker", "Erro: " + error.message, false);
        }
    } else {
        document.getElementById("swStatus").innerHTML = 
            "<span class=\'text-danger\'>✗ Service Worker não suportado</span>";
        addTestResult("Service Worker", "Não suportado", false);
    }
}

// Teste de Cache
async function testCache() {
    if ("caches" in window) {
        try {
            const cacheNames = await caches.keys();
            const cacheSize = cacheNames.length;
            
            document.getElementById("cacheStatus").innerHTML = 
                `<span class="text-success">✓ ${cacheSize} cache(s) encontrado(s)</span>`;
            addTestResult("Cache", `${cacheSize} caches encontrados`, true);
        } catch (error) {
            document.getElementById("cacheStatus").innerHTML = 
                "<span class=\'text-danger\'>✗ Erro ao verificar cache</span>";
            addTestResult("Cache", "Erro: " + error.message, false);
        }
    } else {
        document.getElementById("cacheStatus").innerHTML = 
            "<span class=\'text-danger\'>✗ Cache API não suportada</span>";
        addTestResult("Cache", "API não suportada", false);
    }
}

// Limpar cache
async function clearCache() {
    if ("caches" in window) {
        try {
            const cacheNames = await caches.keys();
            await Promise.all(cacheNames.map(name => caches.delete(name)));
            
            showToast("Cache limpo com sucesso", "success");
            testCache();
        } catch (error) {
            showToast("Erro ao limpar cache: " + error.message, "danger");
        }
    }
}

// Teste de IndexedDB
async function testIndexedDB() {
    if ("indexedDB" in window) {
        try {
            // Testa se o OfflineManager está funcionando
            if (typeof OfflineManager !== "undefined") {
                const stats = await OfflineManager.getStorageStats();
                if (stats) {
                    document.getElementById("dbStatus").innerHTML = 
                        `<span class="text-success">✓ IndexedDB funcionando (${stats.totalItems} itens)</span>`;
                    addTestResult("IndexedDB", `Funcionando - ${stats.totalItems} itens`, true);
                } else {
                    document.getElementById("dbStatus").innerHTML = 
                        "<span class=\'text-warning\'>⚠ IndexedDB vazio</span>";
                    addTestResult("IndexedDB", "Funcionando - vazio", true);
                }
            } else {
                document.getElementById("dbStatus").innerHTML = 
                    "<span class=\'text-warning\'>⚠ OfflineManager não carregado</span>";
                addTestResult("IndexedDB", "OfflineManager não disponível", false);
            }
        } catch (error) {
            document.getElementById("dbStatus").innerHTML = 
                "<span class=\'text-danger\'>✗ Erro no IndexedDB</span>";
            addTestResult("IndexedDB", "Erro: " + error.message, false);
        }
    } else {
        document.getElementById("dbStatus").innerHTML = 
            "<span class=\'text-danger\'>✗ IndexedDB não suportado</span>";
        addTestResult("IndexedDB", "Não suportado", false);
    }
}

// Teste de Manifest
async function testManifest() {
    try {
        const response = await fetch("/SUAP/manifest.json");
        if (response.ok) {
            const manifest = await response.json();
            document.getElementById("manifestStatus").innerHTML = 
                `<span class="text-success">✓ Manifest carregado (${manifest.name})</span>`;
            addTestResult("Manifest", "Carregado - " + manifest.name, true);
        } else {
            document.getElementById("manifestStatus").innerHTML = 
                "<span class=\'text-danger\'>✗ Erro ao carregar manifest</span>";
            addTestResult("Manifest", "Erro HTTP: " + response.status, false);
        }
    } catch (error) {
        document.getElementById("manifestStatus").innerHTML = 
            "<span class=\'text-danger\'>✗ Falha ao buscar manifest</span>";
        addTestResult("Manifest", "Erro: " + error.message, false);
    }
}

// Instalar PWA
async function installPWA() {
    if (deferredPrompt) {
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        
        if (outcome === "accepted") {
            showToast("PWA instalado com sucesso!", "success");
        } else {
            showToast("Instalação do PWA cancelada", "info");
        }
        
        deferredPrompt = null;
        document.getElementById("installButton").style.display = "none";
    }
}

// Teste de salvamento de dados
async function testSaveData() {
    if (typeof OfflineManager !== "undefined") {
        try {
            const testData = {
                timestamp: new Date().toISOString(),
                test: "Dados de teste PWA",
                random: Math.random()
            };
            
            const success = await OfflineManager.saveData("teste_pwa", testData);
            if (success) {
                showToast("Dados de teste salvos com sucesso", "success");
                document.getElementById("dataTestResult").innerHTML = 
                    `<div class="alert alert-success">✓ Dados salvos: ${JSON.stringify(testData, null, 2)}</div>`;
            } else {
                showToast("Erro ao salvar dados de teste", "danger");
            }
        } catch (error) {
            showToast("Erro: " + error.message, "danger");
        }
    } else {
        showToast("OfflineManager não disponível", "warning");
    }
}

// Teste de carregamento de dados
async function testLoadData() {
    if (typeof OfflineManager !== "undefined") {
        try {
            const data = await OfflineManager.getData("teste_pwa");
            if (data) {
                showToast("Dados carregados com sucesso", "success");
                document.getElementById("dataTestResult").innerHTML = 
                    `<div class="alert alert-info">📁 Dados carregados: ${JSON.stringify(data, null, 2)}</div>`;
            } else {
                showToast("Nenhum dado encontrado", "info");
                document.getElementById("dataTestResult").innerHTML = 
                    `<div class="alert alert-warning">⚠ Nenhum dado encontrado. Execute o teste de salvamento primeiro.</div>`;
            }
        } catch (error) {
            showToast("Erro ao carregar dados: " + error.message, "danger");
        }
    } else {
        showToast("OfflineManager não disponível", "warning");
    }
}

// Teste de sincronização
async function testSync() {
    if (typeof OfflineManager !== "undefined") {
        try {
            await OfflineManager.syncData();
            showToast("Sincronização iniciada", "success");
        } catch (error) {
            showToast("Erro na sincronização: " + error.message, "danger");
        }
    } else {
        showToast("OfflineManager não disponível", "warning");
    }
}

// Adiciona resultado do teste
function addTestResult(test, result, success) {
    const timestamp = new Date().toLocaleTimeString();
    const icon = success ? "✓" : "✗";
    const colorClass = success ? "text-success" : "text-danger";
    
    testResults.unshift({
        test,
        result,
        success,
        timestamp
    });
    
    updateTestResults();
}

// Atualiza a exibição dos resultados
function updateTestResults() {
    const container = document.getElementById("testResults");
    
    if (testResults.length === 0) {
        container.innerHTML = "<p class=\'text-muted\'>Execute os testes acima para ver os resultados aqui.</p>";
        return;
    }
    
    const html = testResults.map(result => `
        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
            <div>
                <strong>${result.test}:</strong> 
                <span class="${result.success ? \'text-success\' : \'text-danger\'}">${result.result}</span>
            </div>
            <small class="text-muted">${result.timestamp}</small>
        </div>
    `).join("");
    
    container.innerHTML = html;
}

// Executa testes iniciais
document.addEventListener("DOMContentLoaded", function() {
    // Aguarda um pouco para garantir que tudo carregou
    setTimeout(() => {
        testConnection();
        testServiceWorker();
        testCache();
        testIndexedDB();
        testManifest();
    }, 1000);
});
</script>
';
