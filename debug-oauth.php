<?php
require_once 'config.php';

// Página de debug para verificar configurações OAuth
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug OAuth - SUPACO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-white">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card bg-dark border-secondary">
                    <div class="card-header">
                        <h3 class="mb-0">🔧 Debug OAuth Configuration</h3>
                    </div>
                    <div class="card-body">
                        <h5>📋 Configurações Atuais</h5>
                        <table class="table table-dark table-striped">
                            <tbody>
                                <tr>
                                    <td><strong>Ambiente:</strong></td>
                                    <td><?php echo $isLocalhost ? 'Local' : 'Produção'; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>SUAP URL:</strong></td>
                                    <td><?php echo SUAP_URL; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Client ID:</strong></td>
                                    <td><?php echo SUAP_CLIENT_ID; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Redirect URI:</strong></td>
                                    <td><code><?php echo REDIRECT_URI; ?></code></td>
                                </tr>
                                <tr>
                                    <td><strong>SERVER_NAME:</strong></td>
                                    <td><?php echo $_SERVER['SERVER_NAME']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>HTTP_HOST:</strong></td>
                                    <td><?php echo $_SERVER['HTTP_HOST']; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>HTTPS:</strong></td>
                                    <td><?php echo isset($_SERVER['HTTPS']) ? 'Sim' : 'Não'; ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <h5>🔗 URL de Autorização</h5>
                        <div class="alert alert-info">
                            <strong>URL completa:</strong><br>
                            <code><?php 
                                $auth_url = SUAP_URL . "/o/authorize/?" . http_build_query([
                                    'response_type' => 'code',
                                    'client_id' => SUAP_CLIENT_ID,
                                    'redirect_uri' => REDIRECT_URI
                                ]);
                                echo $auth_url;
                            ?></code>
                        </div>

                        <h5>🧪 Testes</h5>
                        <div class="d-grid gap-2">
                            <a href="<?php echo $auth_url; ?>" class="btn btn-primary" target="_blank">
                                🚀 Testar Autorização
                            </a>
                            
                            <a href="callback.php" class="btn btn-secondary">
                                📞 Testar Callback (sem código)
                            </a>
                            
                            <button class="btn btn-warning" onclick="copyToClipboard('<?php echo REDIRECT_URI; ?>')">
                                📋 Copiar Redirect URI
                            </button>
                        </div>

                        <h5 class="mt-4">📝 Logs do Servidor</h5>
                        <div class="alert alert-warning">
                            <small>
                                Verifique os logs do servidor para mais detalhes:<br>
                                <code>C:\wamp64\logs\php_error.log</code>
                            </small>
                        </div>

                        <h5>🔍 Possíveis Problemas</h5>
                        <ul class="list-unstyled">
                            <li>❌ <strong>Redirect URI não registrado:</strong> Verifique no painel OAuth do SUAP</li>
                            <li>❌ <strong>Protocolo incorreto:</strong> Deve ser HTTPS em produção</li>
                            <li>❌ <strong>Domínio incorreto:</strong> Verifique se o domínio está correto</li>
                            <li>❌ <strong>Caminho incorreto:</strong> Deve ser exatamente <code>/callback.php</code></li>
                            <li>❌ <strong>Client ID incorreto:</strong> Verifique se está usando o ID correto</li>
                        </ul>

                        <div class="alert alert-danger">
                            <strong>⚠️ Importante:</strong><br>
                            O redirect URI deve estar <strong>exatamente</strong> como registrado no SUAP.<br>
                            Qualquer diferença (protocolo, domínio, caminho, barra final) causará erro.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Redirect URI copiado para a área de transferência!');
            }, function(err) {
                console.error('Erro ao copiar: ', err);
            });
        }
    </script>
</body>
</html>
