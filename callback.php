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

// Salva o token
$_SESSION['access_token'] = $token_data['access_token'];

// Salva o refresh_token se disponível
if (isset($token_data['refresh_token'])) {
    $_SESSION['refresh_token'] = $token_data['refresh_token'];
    error_log("Refresh token salvo com sucesso");
}

error_log("Token de acesso salvo com sucesso: " . substr($token_data['access_token'], 0, 10) . '...');

// Buscar informações do usuário usando o token
error_log("Buscando informações do usuário com o token");
$ch = curl_init(SUAP_URL . "/api/v2/minhas-informacoes/meus-dados/");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . $_SESSION['access_token']
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_VERBOSE => true
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

error_log("API resposta (meus-dados): Código HTTP " . $httpcode);
if (curl_errno($ch)) {
    error_log("Erro CURL ao buscar dados do usuário: " . curl_error($ch));
}
curl_close($ch);

$user_data = json_decode($response, true);

// Verificar se conseguimos obter as informações do usuário
if ($user_data && isset($user_data['id'])) {
    // Salvar informações do usuário na sessão
    $_SESSION['user_id'] = $user_data['id'];

    // Salvar outros dados úteis do usuário se disponíveis
    if (isset($user_data['nome'])) {
        $_SESSION['user_nome'] = $user_data['nome'];
    }

    if (isset($user_data['email'])) {
        $_SESSION['user_email'] = $user_data['email'];
    }

    if (isset($user_data['tipo_usuario'])) {
        $_SESSION['user_tipo'] = $user_data['tipo_usuario'];
    }

    // Definir expiração do token (típico OAuth2: 1 hora = 3600 segundos)
    // Se o token_data tiver 'expires_in', use esse valor, caso contrário use 3600 segundos (1 hora)
    $expires_in = isset($token_data['expires_in']) ? $token_data['expires_in'] : 3600;
    $_SESSION['access_token_expires'] = time() + $expires_in;

    // Log detalhado das variáveis de sessão para diagnóstico
    $session_vars = [
        'user_id' => $_SESSION['user_id'],
        'user_nome' => isset($_SESSION['user_nome']) ? $_SESSION['user_nome'] : 'não definido',
        'access_token' => 'definido (primeiro 10 chars: ' . substr($_SESSION['access_token'], 0, 10) . '...)',
        'refresh_token' => isset($_SESSION['refresh_token']) ? 'definido' : 'não definido',
        'access_token_expires' => date('Y-m-d H:i:s', $_SESSION['access_token_expires'])
    ];

    error_log("Informações do usuário salvas com sucesso. Variáveis de sessão: " . print_r($session_vars, true));
} else {
    // Log detalhado do erro
    error_log("Falha ao obter informações do usuário. Resposta HTTP: " . $httpcode);
    error_log("Corpo da resposta: " . $response);
    error_log("Dados decodificados: " . print_r($user_data, true));

    // Se não conseguirmos obter as informações do usuário, limpar a sessão
    session_unset();
    session_destroy();
    header('Location: login.php?erro=usuario_nao_encontrado');
    exit;
}

// Salva dados no cache após login bem-sucedido
echo '<script>
    // Aguarda o carregamento dos managers
    document.addEventListener("DOMContentLoaded", function() {
        // Salva no novo sistema de cache
        if (typeof AppCacheManager !== "undefined") {
            const appData = {
                meusDados: ' . json_encode($user_data) . ',
                timestamp: Date.now(),
                version: "1.0.0",
                isBasic: false
            };
            
            AppCacheManager.saveAppData(appData);
            console.log("SUPACO: Dados salvos no cache após login");
        }
        
        // Compatibilidade com sistema legado
        if (typeof LocalStorageManager !== "undefined") {
            const userData = {
                meusDados: ' . json_encode($user_data) . ',
                timestamp: Date.now(),
                isBasic: false
            };
            
            LocalStorageManager.saveUserData(userData);
            console.log("SUPACO: Dados salvos no localStorage (legado) após login");
        }
    });
</script>';

// Sempre redireciona para index.php (sem parâmetros para evitar problemas offline)
header('Location: index.php');
exit;
