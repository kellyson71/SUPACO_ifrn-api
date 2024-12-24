<?php
require_once 'config.php';
session_start();

// Log detalhado para debug
error_log("=== Início do Callback ===");
error_log("URI Atual: " . $_SERVER['REQUEST_URI']);
error_log("Parâmetros GET: " . print_r($_GET, true));
error_log("Headers: " . print_r(getallheaders(), true));

// Verifica erro de autorização
if (isset($_GET['error'])) {
    error_log("Erro de autorização: " . $_GET['error']);
    error_log("Descrição: " . ($_GET['error_description'] ?? 'Sem descrição'));
    header('Location: index.php?error=' . urlencode($_GET['error']));
    exit;
}

// Verifica código de autorização
if (!isset($_GET['code'])) {
    error_log("Código de autorização não recebido");
    header('Location: index.php?error=no_code');
    exit;
}

$code = $_GET['code'];
error_log("Código recebido: " . $code);

// Prepara requisição do token
$token_request = [
    'grant_type' => 'authorization_code',
    'code' => $code,
    'client_id' => SUAP_CLIENT_ID,
    'client_secret' => SUAP_CLIENT_SECRET,
    'redirect_uri' => REDIRECT_URI
];

error_log("Dados da requisição token: " . print_r($token_request, true));

// Configura CURL
$ch = curl_init(SUAP_URL . "/o/token/");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($token_request),
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_VERBOSE => true,
    CURLOPT_HEADER => true
]);

// Captura saída verbose do CURL
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$header = substr($response, 0, $header_size);
$body = substr($response, $header_size);

// Log da resposta CURL
error_log("Headers da resposta: " . $header);
error_log("Corpo da resposta: " . $body);

if (curl_errno($ch)) {
    error_log("Erro CURL: " . curl_error($ch));
    header('Location: index.php?error=curl_error');
    exit;
}

// Log verbose do CURL
rewind($verbose);
$verboseLog = stream_get_contents($verbose);
error_log("Log verbose CURL: " . $verboseLog);

curl_close($ch);

// Processa resposta
$token_data = json_decode($body, true);

if (!$token_data || !isset($token_data['access_token'])) {
    error_log("Falha ao decodificar resposta do token: " . print_r($token_data, true));
    header('Location: index.php?error=invalid_token_response');
    exit;
}

// Salva token e redireciona
$_SESSION['access_token'] = $token_data['access_token'];
error_log("Token salvo com sucesso: " . substr($token_data['access_token'], 0, 10) . '...');
header('Location: index.php?auth=success');
exit;
