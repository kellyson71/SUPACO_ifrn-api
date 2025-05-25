<?php
// Página de demonstração do sistema offline visual
session_start();

// Simular usuário logado para teste
if (!isset($_SESSION['access_token'])) {
    $_SESSION['access_token'] = 'demo_token';
    $_SESSION['meusdados'] = [
        'nome_usual' => 'Usuário Demo',
        'tipo_vinculo' => 'Estudante',
        'vinculo' => [
            'curriculo_lattes' => '#'
        ]
    ];
}

require_once 'base_pwa.php';

$pageTitle = 'Demo Offline Visual - SUPACO';
$pageContent = '
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <h5><i class="fas fa-info-circle me-2"></i>Demonstração do Sistema Offline</h5>
                <p class="mb-0">Esta página demonstra o sistema offline visual com cache inteligente. 
                Use as opções abaixo para testar diferentes cenários.</p>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-wifi me-2"></i>Controles de Conexão</h6>
                </div>
                <div class="card-body">
                    <button class="btn btn-success btn-sm mb-2 w-100" onclick="simulateOnline()">
                        <i class="fas fa-wifi me-1"></i> Simular Online
                    </button>
                    <button class="btn btn-warning btn-sm mb-2 w-100" onclick="simulateOffline()">
                        <i class="fas fa-wifi-slash me-1"></i> Simular Offline
                    </button>
                    <button class="btn btn-info btn-sm w-100" onclick="clearCache()">
                        <i class="fas fa-trash me-1"></i> Limpar Cache
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-download me-2"></i>Testar Cache</h6>
                </div>
                <div class="card-body">
                    <button class="btn btn-primary btn-sm mb-2 w-100" onclick="loadTestData(\'boletim\')">
                        <i class="fas fa-star me-1"></i> Carregar Boletim
                    </button>
                    <button class="btn btn-primary btn-sm mb-2 w-100" onclick="loadTestData(\'horarios\')">
                        <i class="fas fa-calendar me-1"></i> Carregar Horários
                    </button>
                    <button class="btn btn-secondary btn-sm w-100" onclick="syncAllData()">
                        <i class="fas fa-sync me-1"></i> Sincronizar Tudo
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Status do Sistema</h6>
                </div>
                <div class="card-body" id="system-status">
                    <div class="text-muted text-center">
                        <i class="fas fa-spinner fa-spin"></i>
                        Carregando status...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-star me-2"></i>
                        Boletim Acadêmico
                    </h5>
                    <div id="boletim-status-indicator"></div>
                </div>
                <div class="card-body">
                    <div id="boletim-container">
                        <div class="text-center py-4">
                            <i class="fas fa-download text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">Clique em "Carregar Boletim" para testar</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Horários de Aula
                    </h5>
                    <div id="horarios-status-indicator"></div>
                </div>
                <div class="card-body">
                    <div id="horarios-container">
                        <div class="text-center py-4">
                            <i class="fas fa-download text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">Clique em "Carregar Horários" para testar</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-terminal me-2"></i>Log de Eventos</h6>
                </div>
                <div class="card-body">
                    <div id="event-log" style="height: 200px; overflow-y: auto; background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 0.9rem;">
                        <div class="text-muted">Sistema iniciado...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Sistema de log de eventos
function logEvent(message, type = "info") {
    const log = document.getElementById("event-log");
    const timestamp = new Date().toLocaleTimeString();
    const icons = {
        info: "ℹ️",
        success: "✅", 
        warning: "⚠️",
        error: "❌"
    };
    
    const entry = document.createElement("div");
    entry.innerHTML = `[${timestamp}] ${icons[type]} ${message}`;
    entry.className = `text-${type === "info" ? "primary" : type}`;
    
    log.appendChild(entry);
    log.scrollTop = log.scrollHeight;
}

// Simular conexão online
function simulateOnline() {
    // Disparar evento online
    window.dispatchEvent(new Event("online"));
    logEvent("Conexão online simulada", "success");
    updateSystemStatus();
}

// Simular conexão offline  
function simulateOffline() {
    // Disparar evento offline
    window.dispatchEvent(new Event("offline"));
    logEvent("Conexão offline simulada", "warning");
    updateSystemStatus();
}

// Limpar cache
async function clearCache() {
    try {
        if ("caches" in window) {
            const cacheNames = await caches.keys();
            await Promise.all(cacheNames.map(name => caches.delete(name)));
        }
        
        // Limpar IndexedDB se disponível
        if (window.OfflineCacheManager) {
            // Implementar limpeza do IndexedDB aqui se necessário
        }
        
        logEvent("Cache limpo com sucesso", "success");
        updateSystemStatus();
    } catch (error) {
        logEvent("Erro ao limpar cache: " + error.message, "error");
    }
}

// Carregar dados de teste
async function loadTestData(type) {
    logEvent(`Carregando dados de ${type}...`, "info");
    
    const container = document.getElementById(`${type}-container`);
    const statusIndicator = document.getElementById(`${type}-status-indicator`);
    
    try {
        // Usar função global do sistema offline
        if (type === "boletim") {
            await loadAcademicData("boletim", "boletim", container, renderBoletimDemo);
        } else if (type === "horarios") {
            await loadAcademicData("horarios", "horarios", container, renderHorariosDemo);
        }
        
        logEvent(`Dados de ${type} carregados com sucesso`, "success");
        
        if (statusIndicator) {
            statusIndicator.innerHTML = `<span class="badge bg-success">✓ Carregado</span>`;
        }
    } catch (error) {
        logEvent(`Erro ao carregar ${type}: ${error.message}`, "error");
        
        if (statusIndicator) {
            statusIndicator.innerHTML = `<span class="badge bg-danger">✗ Erro</span>`;
        }
    }
    
    updateSystemStatus();
}

// Sincronizar todos os dados
async function syncAllData() {
    logEvent("Iniciando sincronização completa...", "info");
    
    try {
        const response = await fetch("api_offline.php?action=atualizar_todos");
        const result = await response.json();
        
        if (result.status === "success") {
            logEvent("Sincronização completa realizada", "success");
        } else {
            logEvent("Erro na sincronização: " + result.error, "error");
        }
    } catch (error) {
        logEvent("Erro na sincronização: " + error.message, "error");
    }
    
    updateSystemStatus();
}

// Atualizar status do sistema
async function updateSystemStatus() {
    const statusDiv = document.getElementById("system-status");
    
    let html = `
        <div class="mb-2">
            <strong>Conexão:</strong> 
            <span class="badge ${navigator.onLine ? "bg-success" : "bg-warning"}">
                ${navigator.onLine ? "Online" : "Offline"}
            </span>
        </div>
    `;
    
    // Verificar Service Worker
    if ("serviceWorker" in navigator) {
        const registration = await navigator.serviceWorker.getRegistration();
        html += `
            <div class="mb-2">
                <strong>Service Worker:</strong>
                <span class="badge ${registration ? "bg-success" : "bg-secondary"}">
                    ${registration ? "Ativo" : "Inativo"}
                </span>
            </div>
        `;
    }
    
    // Verificar cache
    if ("caches" in window) {
        try {
            const cacheNames = await caches.keys();
            html += `
                <div class="mb-2">
                    <strong>Caches:</strong>
                    <span class="badge bg-info">${cacheNames.length} cache(s)</span>
                </div>
            `;
        } catch (error) {
            html += `
                <div class="mb-2">
                    <strong>Caches:</strong>
                    <span class="badge bg-secondary">Erro</span>
                </div>
            `;
        }
    }
    
    statusDiv.innerHTML = html;
}

// Funções de renderização demo
function renderBoletimDemo(data, container) {
    if (!data || !data.length) {
        container.innerHTML = `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-circle me-2"></i>
                Nenhum dado de boletim disponível.
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Disciplina</th>
                        <th class="text-center">Média</th>
                        <th class="text-center">Situação</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    data.forEach(item => {
        const situacaoClass = item.situacao === "Aprovado" ? "success" : "warning";
        html += `
            <tr>
                <td>${item.disciplina}</td>
                <td class="text-center">${item.media}</td>
                <td class="text-center">
                    <span class="badge bg-${situacaoClass}">${item.situacao}</span>
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    container.innerHTML = html;
}

function renderHorariosDemo(data, container) {
    if (!data || !data.length) {
        container.innerHTML = `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-circle me-2"></i>
                Nenhum dado de horário disponível.
            </div>
        `;
        return;
    }
    
    let html = "";
    const diasSemana = ["Segunda-feira", "Terça-feira", "Quarta-feira", "Quinta-feira", "Sexta-feira"];
    
    diasSemana.forEach(dia => {
        const aulasDoDia = data.filter(aula => aula.dia_semana === dia);
        
        if (aulasDoDia.length > 0) {
            html += `
                <div class="mb-3">
                    <h6 class="text-primary">${dia}</h6>
            `;
            
            aulasDoDia.forEach(aula => {
                html += `
                    <div class="card card-body py-2 mb-2 border-start border-primary border-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${aula.disciplina}</strong><br>
                                <small class="text-muted">${aula.professor}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary">${aula.horario}</span><br>
                                <small class="text-muted">${aula.sala}</small>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += "</div>";
        }
    });
    
    container.innerHTML = html || `
        <div class="text-center text-muted py-4">
            <i class="fas fa-calendar-times" style="font-size: 2rem;"></i>
            <p class="mt-2">Nenhuma aula encontrada</p>
        </div>
    `;
}

// Inicializar demo
document.addEventListener("DOMContentLoaded", function() {
    logEvent("Demo iniciado", "success");
    updateSystemStatus();
    
    // Atualizar status periodicamente
    setInterval(updateSystemStatus, 5000);
    
    // Log de eventos de conexão
    window.addEventListener("online", () => {
        logEvent("Conexão restaurada", "success");
    });
    
    window.addEventListener("offline", () => {
        logEvent("Conexão perdida", "warning");
    });
});
</script>
';

// Incluir o template base
