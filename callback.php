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

    if (isset($user_data['matricula'])) {
        $_SESSION['user_matricula'] = $user_data['matricula'];
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
    
    $mobile_id = isset($_GET['state']) ? $_GET['state'] : null;
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $is_mobile = (strpos($user_agent, 'Expo') !== false || $mobile_id !== null);
    
    if ($is_mobile && $mobile_id) {
        error_log("MOBILE: Requisição detectada via state parameter");
        error_log("MOBILE: mobile_id (state) = " . $mobile_id);
        
        $temp_file = sys_get_temp_dir() . '/supaco_mobile_' . $mobile_id . '.json';
        
        $mobile_data = [
            'success' => true,
            'access_token' => $token_data['access_token'],
            'refresh_token' => isset($token_data['refresh_token']) ? $token_data['refresh_token'] : null,
            'expires_in' => $expires_in,
            'token_type' => 'Bearer',
            'user_data' => $user_data,
            'timestamp' => time()
        ];
        
        file_put_contents($temp_file, json_encode($mobile_data));
        error_log("MOBILE: Dados salvos em: " . $temp_file);
        error_log("MOBILE: mobile_id = " . $mobile_id);
        
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Autenticação Concluída</title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body {
                    font-family: system-ui, -apple-system, sans-serif;
                    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    margin: 0;
                    padding: 20px;
                }
                .container {
                    background: white;
                    padding: 40px;
                    border-radius: 20px;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                    text-align: center;
                    max-width: 400px;
                }
                .success-icon {
                    font-size: 64px;
                    margin-bottom: 20px;
                    animation: bounce 1s infinite;
                }
                @keyframes bounce {
                    0%, 100% { transform: scale(1); }
                    50% { transform: scale(1.1); }
                }
                h1 {
                    color: #10b981;
                    margin: 0 0 10px 0;
                    font-size: 28px;
                }
                p {
                    color: #666;
                    margin: 10px 0;
                    font-size: 18px;
                }
                .code {
                    background: #f3f4f6;
                    padding: 15px;
                    border-radius: 10px;
                    font-family: monospace;
                    font-size: 12px;
                    margin: 20px 0;
                    word-break: break-all;
                    color: #3b82f6;
                    font-weight: bold;
                }
                .info {
                    font-size: 14px;
                    color: #999;
                    margin-top: 20px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="success-icon">✅</div>
                <h1>Autenticação Concluída!</h1>
                <p><strong>Volte para o aplicativo SUPACO</strong></p>
                <div class="code">ID: ' . $mobile_id . '</div>
                <p class="info">Aguarde... O app está buscando seus dados.</p>
                <p class="info" style="margin-top: 30px;">Pode fechar esta janela</p>
            </div>
        </body>
        </html>';
        exit;
    }
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
