<?php
require_once 'config.php';
session_start();

$pageTitle = 'Teste - SUPACO';
ob_start();
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">
                        <i class="fas fa-rocket me-2"></i>
                        Teste da Funcionalidade de Atualizações
                    </h2>
                </div>
                
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle me-2"></i>Informações do Sistema</h5>
                        <ul class="mb-0">
                            <li><strong>Versão atual:</strong> 3.8</li>
                            <li><strong>Página de atualizações:</strong> <a href="atualizacoes.php" class="btn btn-sm btn-outline-primary">Ver Atualizações</a></li>
                            <li><strong>Arquivo de changelog:</strong> <code>get_changelog.php</code></li>
                        </ul>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Funcionalidades Implementadas</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li><i class="fas fa-check text-success me-2"></i>Página de atualizações criada</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Integração com Git para changelog</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Aba adicionada no navbar</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Correção da exibição da foto do usuário</li>
                                        <li><i class="fas fa-check text-success me-2"></i>Estilos da navbar melhorados</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>Arquivos Criados/Modificados</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li><i class="fas fa-file-code text-info me-2"></i><code>atualizacoes.php</code></li>
                                        <li><i class="fas fa-file-code text-info me-2"></i><code>get_changelog.php</code></li>
                                        <li><i class="fas fa-edit text-warning me-2"></i><code>base.php</code></li>
                                        <li><i class="fas fa-edit text-warning me-2"></i><code>base_pwa.php</code></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Observações</h6>
                            <ul class="mb-0">
                                <li>Este é um projeto PHP, não há necessidade de <code>npm run build</code></li>
                                <li>A foto do usuário agora tem fallback caso não carregue</li>
                                <li>A aba "Atualizações" deve aparecer no navbar entre "Classroom" e o menu do usuário</li>
                                <li>O changelog é gerado automaticamente baseado nos commits do Git</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="atualizacoes.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-rocket me-2"></i>
                            Ver Histórico de Atualizações
                        </a>
                        <a href="index.php" class="btn btn-outline-primary btn-lg ms-2">
                            <i class="fas fa-home me-2"></i>
                            Voltar ao Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$pageContent = ob_get_clean();
require_once 'base.php';
?>
