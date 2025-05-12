<?php
// Detectar ambiente automático
$isLocalhost = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']);

define('SUAP_URL', 'https://suap.ifrn.edu.br');
define('SUAP_CLIENT_ID', 'O6ZNFqUF3IzS8ofXGHyO2y5bwHM3Pw5xXZz6Ip4r');
define('SUAP_CLIENT_SECRET', 'V56RK5XECUwziln6STQjvST2HCFsX7U30fdu66Dyd4m1371lIq3uT2BgP5DMZJChMr9A3nnJJNsqAIUNWtN9R90wHH8Q6QulcpiqkQKgADm7Q5HlxzxevLrUB9bYEQhM');
// define('SUAP_CLIENT_ID', 'pZDomFtrhLdnbr7cj7BT4JACFAlCO0OiDi8IB77L');
// define('SUAP_CLIENT_SECRET', 'sPMMkXOBlg9RVsNpl2OwFUB0kjyZogvoSqAsoZnt51RpDXz1YEyfUFuFDkamvRJ2fz9OAtup7KmJ6Gupkvs4BOTw6zy4rCNmIVKQZCcNhX5y5zyWAuLbLS3Ot0tR9cfO');

// Definir URI de redirecionamento baseado no ambiente
if ($isLocalhost) {
    // Ambiente de desenvolvimento local
    define('REDIRECT_URI', 'http://localhost/SUAP/callback.php');
} else {
    // Ambiente de produção
    define('REDIRECT_URI', 'https://suap2.estagiopaudosferros.com/callback.php');
}

// Configurações de depuração
ini_set('display_errors', $isLocalhost ? 1 : 0); // Mostrar erros apenas em localhost
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Log informativo do ambiente
error_log("Ambiente detectado: " . ($isLocalhost ? "Local" : "Produção"));
error_log("URI de redirecionamento: " . REDIRECT_URI);
