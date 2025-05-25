<?php
// Página de horários para acessar dados salvos offline
session_start();
require_once 'base_pwa.php';

// Conteúdo da página 
$pageTitle = 'Horários Offline - SUPACO';
$pageContent = '
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Horários de Aula
                    </h5>
                    <div id="offlineStatusIndicator"></div>
                </div>
                <div class="card-body p-0">
                    <div id="horarios-loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <p class="mt-3 text-muted">Carregando dados de horários...</p>
                    </div>
                    
                    <div id="horarios-container" class="d-none">
                        <!-- Aqui será renderizado o horário -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Carregar horários usando o novo sistema de cache
    loadAcademicData("horarios", "horarios", document.getElementById("horarios-container"), renderHorarios);
});

// Função para renderizar o horário com os dados
function renderHorarios(data, container) {
    // Esconder o indicador de carregamento
    const loadingEl = document.getElementById("horarios-loading");
    if (loadingEl) loadingEl.classList.add("d-none");
    container.classList.remove("d-none");
    
    if (!data || !data.length) {
        container.innerHTML = `
            <div class="alert alert-warning m-4">
                <i class="fas fa-exclamation-circle me-2"></i>
                Nenhum dado de horário disponível.
            </div>
        `;
        return;
    }
    
    // Dias da semana
    const diasSemana = ["Segunda-feira", "Terça-feira", "Quarta-feira", "Quinta-feira", "Sexta-feira", "Sábado"];
    
    // Agrupar horários por dia da semana
    const horariosPorDia = {};
    
    diasSemana.forEach(dia => {
        horariosPorDia[dia] = data.filter(aula => aula.dia_semana === dia);
    });
    
    let html = "";
    
    // Verificar se há aulas em algum dia
    const temAulas = diasSemana.some(dia => horariosPorDia[dia].length > 0);
    
    if (!temAulas) {
        html = `
            <div class="alert alert-info m-4">
                <i class="fas fa-info-circle me-2"></i>
                Não há horários registrados para o período atual.
            </div>
        `;
    } else {
        // Para cada dia da semana, criar uma seção
        diasSemana.forEach(dia => {
            const aulasDoDia = horariosPorDia[dia];
            
            if (aulasDoDia.length === 0) return; // Pular dias sem aulas
            
            html += `
                <div class="dia-section mb-4 fade-in" data-aos="fade-up">
                    <div class="dia-header bg-light p-3 border-bottom">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-day me-2 text-primary"></i>
                            ${dia}
                        </h5>
                    </div>
                    <div class="row g-3 p-3">
            `;
            
            // Ordenar aulas por horário
            aulasDoDia.sort((a, b) => a.horario.localeCompare(b.horario));
            
            aulasDoDia.forEach(aula => {
                html += `
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-clock text-primary me-2"></i>
                                    <span class="badge bg-primary">${aula.horario}</span>
                                </div>
                                <h6 class="card-title mb-2">${aula.disciplina}</h6>
                                <div class="text-muted small">
                                    <div><i class="fas fa-user me-1"></i> ${aula.professor || "Professor não informado"}</div>
                                    <div><i class="fas fa-map-marker-alt me-1"></i> ${aula.sala || "Sala não informada"}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += `
                    </div>
                </div>
            `;
        });
    }
    
    container.innerHTML = html;
    
    // Inicializar animações AOS se disponível
    if (typeof AOS !== "undefined") {
        AOS.refresh();
    }
}
</script>
';

// Incluir o template base
