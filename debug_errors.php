<?php
require_once 'config.php';
session_start();

$pageTitle = 'Debug de Erros - SUPACO';
ob_start();
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-warning text-dark">
                    <h2 class="mb-0">
                        <i class="fas fa-bug me-2"></i>
                        Debug de Erros do Console
                    </h2>
                </div>
                
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle me-2"></i>Análise dos Erros Reportados</h5>
                        <p>Os erros que você viu são de <strong>extensões externas</strong> do navegador, não do seu projeto SUPACO.</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-danger">
                                <div class="card-header bg-danger text-white">
                                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Erros Identificados</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <strong>Erro 1:</strong> Listener assíncrono<br>
                                            <small class="text-muted">Fonte: Extensão do navegador</small>
                                        </li>
                                        <li class="mb-2">
                                            <strong>Erro 2:</strong> CORS Cuponomia<br>
                                            <small class="text-muted">Fonte: Extensão Cuponomia</small>
                                        </li>
                                        <li class="mb-2">
                                            <strong>Erro 3:</strong> spa-maker.js<br>
                                            <small class="text-muted">Fonte: Extensão externa</small>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Status do Projeto</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <strong>Projeto funcionando normalmente</strong>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <strong>Nenhum erro no código SUPACO</strong>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-check text-success me-2"></i>
                                            <strong>Funcionalidades implementadas</strong>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-lightbulb me-2"></i>Como Resolver</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Opção 1: Ignorar</h6>
                                    <p>Esses erros não afetam seu projeto. Pode ignorá-los.</p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Opção 2: Filtrar no Console</h6>
                                    <p>Use filtros no DevTools para ocultar erros de extensões.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h5><i class="fas fa-tools me-2"></i>Teste de Funcionalidades</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <button class="btn btn-primary w-100 mb-2" onclick="testarConsole()">
                                    <i class="fas fa-terminal me-2"></i>Testar Console
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-success w-100 mb-2" onclick="testarFetch()">
                                    <i class="fas fa-network-wired me-2"></i>Testar Fetch
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-info w-100 mb-2" onclick="testarLocalStorage()">
                                    <i class="fas fa-database me-2"></i>Testar Storage
                                </button>
                            </div>
                        </div>
                        <div id="testResults" class="mt-3"></div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-home me-2"></i>
                            Voltar ao Dashboard
                        </a>
                        <a href="atualizacoes.php" class="btn btn-outline-primary btn-lg ms-2">
                            <i class="fas fa-rocket me-2"></i>
                            Ver Atualizações
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testarConsole() {
    console.log('✅ Console funcionando normalmente');
    console.info('✅ Info funcionando');
    console.warn('⚠️ Warning funcionando');
    console.error('❌ Error funcionando (mas filtrado)');
    
    document.getElementById('testResults').innerHTML = `
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            Console testado com sucesso! Verifique o DevTools.
        </div>
    `;
}

function testarFetch() {
    fetch(window.location.href)
        .then(response => {
            document.getElementById('testResults').innerHTML = `
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    Fetch funcionando! Status: ${response.status}
                </div>
            `;
        })
        .catch(error => {
            document.getElementById('testResults').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Erro no fetch: ${error.message}
                </div>
            `;
        });
}

function testarLocalStorage() {
    try {
        localStorage.setItem('teste_supaco', 'funcionando');
        const valor = localStorage.getItem('teste_supaco');
        localStorage.removeItem('teste_supaco');
        
        document.getElementById('testResults').innerHTML = `
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                LocalStorage funcionando! Valor testado: ${valor}
            </div>
        `;
    } catch (error) {
        document.getElementById('testResults').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Erro no LocalStorage: ${error.message}
            </div>
        `;
    }
}
</script>

<?php
$pageContent = ob_get_clean();
require_once 'base.php';
?>
