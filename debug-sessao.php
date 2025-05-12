<?php
require_once 'config.php';
session_start();

// Verificar se o usuário está autenticado
if (!isset($_SESSION['access_token'])) {
    echo "<h1>Usuário não autenticado</h1>";
    echo "<p>Não há token de acesso na sessão.</p>";
    echo "<p><a href='login.php'>Ir para página de login</a></p>";
    exit;
}

// Função para formatar data/hora
function formatarDataHora($timestamp)
{
    return date('d/m/Y H:i:s', $timestamp);
}

// Calcular tempo restante do token
$tempo_restante = 0;
$expiracao_formatada = "Não definido";

if (isset($_SESSION['access_token_expires'])) {
    $tempo_restante = $_SESSION['access_token_expires'] - time();
    $expiracao_formatada = formatarDataHora($_SESSION['access_token_expires']);
}

// Verificar status da sessão
$status_sessao = "OK";
$detalhes_status = "";

if (!isset($_SESSION['user_id'])) {
    $status_sessao = "PROBLEMA";
    $detalhes_status .= "- Falta a variável user_id na sessão<br>";
}

if (!isset($_SESSION['access_token_expires'])) {
    $status_sessao = "PROBLEMA";
    $detalhes_status .= "- Falta a variável access_token_expires na sessão<br>";
} elseif ($_SESSION['access_token_expires'] < time()) {
    $status_sessao = "PROBLEMA";
    $detalhes_status .= "- Token de acesso expirado<br>";
}

// Estilo da página
$cor_status = ($status_sessao == "OK") ? "green" : "red";
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Sessão - SUPACO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 2rem;
        }

        .status-badge {
            display: inline-block;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 4px;
            color: white;
            background-color: <?php echo $cor_status; ?>;
        }

        .token-info {
            word-break: break-all;
            max-width: 100%;
            overflow-x: auto;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="mb-4">Diagnóstico de Sessão</h1>

        <div class="card mb-4">
            <div class="card-header">
                <h3>Status da Sessão: <span class="status-badge"><?php echo $status_sessao; ?></span></h3>
            </div>
            <div class="card-body">
                <?php if ($detalhes_status): ?>
                    <div class="alert alert-danger">
                        <strong>Problemas detectados:</strong><br>
                        <?php echo $detalhes_status; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success">
                        <strong>Sessão válida!</strong> Todos os parâmetros necessários estão presentes.
                    </div>
                <?php endif; ?>

                <p><strong>Expiração do token:</strong> <?php echo $expiracao_formatada; ?></p>
                <p><strong>Tempo restante:</strong> <?php echo floor($tempo_restante / 60); ?> minutos e <?php echo $tempo_restante % 60; ?> segundos</p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h3>Detalhes da Sessão</h3>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Variável</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>user_id</td>
                            <td><?php echo isset($_SESSION['user_id']) ? htmlspecialchars($_SESSION['user_id']) : 'Não definido'; ?></td>
                        </tr>
                        <tr>
                            <td>user_nome</td>
                            <td><?php echo isset($_SESSION['user_nome']) ? htmlspecialchars($_SESSION['user_nome']) : 'Não definido'; ?></td>
                        </tr>
                        <tr>
                            <td>access_token</td>
                            <td class="token-info">
                                <?php if (isset($_SESSION['access_token'])): ?>
                                    <?php echo substr(htmlspecialchars($_SESSION['access_token']), 0, 20); ?>...
                                    <small>(primeiros 20 caracteres)</small>
                                <?php else: ?>
                                    Não definido
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>refresh_token</td>
                            <td>
                                <?php if (isset($_SESSION['refresh_token'])): ?>
                                    <span class="text-success">Definido</span>
                                <?php else: ?>
                                    <span class="text-danger">Não definido</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>access_token_expires</td>
                            <td>
                                <?php if (isset($_SESSION['access_token_expires'])): ?>
                                    <?php echo formatarDataHora($_SESSION['access_token_expires']); ?>
                                    (<?php echo $_SESSION['access_token_expires']; ?>)
                                <?php else: ?>
                                    Não definido
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="index.php" class="btn btn-primary">Voltar ao Dashboard</a>
            <a href="logout.php" class="btn btn-danger">Encerrar Sessão</a>
        </div>
    </div>
</body>

</html>