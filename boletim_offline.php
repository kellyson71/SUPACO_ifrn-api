<?php
// Página de boletim para acessar dados salvos offline
session_start();
require_once 'base_pwa.php';

// Conteúdo da página 
$pageTitle = 'Boletim Offline - SUPACO';
$pageContent = '
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-star me-2"></i>
                        Boletim Acadêmico
                    </h5>
                    <div id="offlineStatusIndicator"></div>
                </div>
                <div class="card-body p-0">
                    <div id="boletim-loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                        <p class="mt-3 text-muted">Carregando dados do boletim...</p>
                    </div>
                    
                    <div id="boletim-container" class="d-none">
                        <!-- Aqui será renderizado o boletim -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Carregar boletim usando o novo sistema de cache
    loadAcademicData("boletim", "boletim", document.getElementById("boletim-container"), renderBoletim);
});

// Função para renderizar o boletim com os dados
function renderBoletim(data, container) {
    // Esconder o indicador de carregamento
    const loadingEl = document.getElementById("boletim-loading");
    if (loadingEl) loadingEl.classList.add("d-none");
    container.classList.remove("d-none");
    
    if (!data || !data.length) {
        container.innerHTML = `
            <div class="alert alert-warning m-4">
                <i class="fas fa-exclamation-circle me-2"></i>
                Nenhum dado de boletim disponível.
            </div>
        `;
        return;
    }
    
    // Filtrar períodos únicos (semestres)
    const periodos = [...new Set(data.map(item => item.periodo.periodo))];
    
    let html = ``;
    
    // Para cada período, criar uma tabela de disciplinas
    periodos.forEach(periodo => {
        const disciplinasDoPeriodo = data.filter(item => item.periodo.periodo === periodo);
        
        html += `
            <div class="periodo-section mb-4 fade-in" data-aos="fade-up">
                <div class="periodo-header bg-light p-3 border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-check me-2"></i>
                        Período: ${periodo}
                    </h5>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover table-boletim mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th><i class="fas fa-book me-1"></i> Disciplina</th>
                                <th class="text-center">1º Bim</th>
                                <th class="text-center">2º Bim</th>
                                <th class="text-center">3º Bim</th>
                                <th class="text-center">4º Bim</th>
                                <th class="text-center">Média</th>
                                <th class="text-center">Situação</th>
                            </tr>
                        </thead>
                        <tbody>
        `;
        
        // Adicionar cada disciplina do período
        disciplinasDoPeriodo.forEach(disciplina => {
            const situacao = disciplina.situacao || "Cursando";
            let situacaoClass = "text-info";
            
            if (situacao.includes("APROVADO")) {
                situacaoClass = "aprovado";
            } else if (situacao.includes("REPROVADO")) {
                situacaoClass = "reprovado";
            }
            
            // Calcular média (se disponível)
            let media = 0;
            let mediaDisplay = "-";
            
            if (disciplina.segundo_semestre) {
                // Se tem notas do segundo semestre
                const nota1 = parseFloat(disciplina.segundo_semestre.nota_etapa_1.nota) || 0;
                const nota2 = parseFloat(disciplina.segundo_semestre.nota_etapa_2.nota) || 0;
                const nota3 = parseFloat(disciplina.segundo_semestre.nota_etapa_3.nota) || 0;
                const nota4 = parseFloat(disciplina.segundo_semestre.nota_etapa_4.nota) || 0;
                
                if (nota1 || nota2 || nota3 || nota4) {
                    media = ((nota1 * 2) + (nota2 * 2) + (nota3 * 3) + (nota4 * 3)) / 10;
                    mediaDisplay = media.toFixed(1);
                }
            }
            
            html += `
                <tr class="disciplina-item">
                    <td>
                        <span class="disciplina-codigo">${disciplina.codigo || "-"}</span>
                        <span class="disciplina-nome">${disciplina.disciplina}</span>
                    </td>
                    <td class="text-center">${disciplina.segundo_semestre ? (disciplina.segundo_semestre.nota_etapa_1.nota || "-") : "-"}</td>
                    <td class="text-center">${disciplina.segundo_semestre ? (disciplina.segundo_semestre.nota_etapa_2.nota || "-") : "-"}</td>
                    <td class="text-center">${disciplina.segundo_semestre ? (disciplina.segundo_semestre.nota_etapa_3.nota || "-") : "-"}</td>
                    <td class="text-center">${disciplina.segundo_semestre ? (disciplina.segundo_semestre.nota_etapa_4.nota || "-") : "-"}</td>
                    <td class="text-center"><strong>${mediaDisplay}</strong></td>
                    <td class="text-center">
                        <div class="situacao-${situacaoClass}">
                            ${situacao}
                        </div>
                    </td>
                </tr>
            `;
        });
        
        html += `
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    // Adicionar animações
    const disciplinaItems = document.querySelectorAll(".disciplina-item");
    disciplinaItems.forEach((item, index) => {
        item.classList.add("boletim-item");
        item.style.animationDelay = `${0.1 + index * 0.05}s`;
    });
}
</script>
';
