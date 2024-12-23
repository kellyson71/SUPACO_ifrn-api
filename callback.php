<?php
require_once 'config.php';
session_start();

error_log("Callback.php iniciado");
error_log("REDIRECT_URI configurada: " . REDIRECT_URI);
error_log("URI atual: " . $_SERVER['REQUEST_URI']);
error_log("GET params: " . print_r($_GET, true));

if (isset($_GET['error'])) {
    error_log("Erro recebido: " . $_GET['error']);
    error_log("Descrição do erro: " . $_GET['error_description'] ?? 'Sem descrição');
}

if (isset($_GET['code'])) {
    error_log("Código recebido: " . $_GET['code']);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, SUAP_URL . "/o/token/");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'authorization_code',
        'code' => $_GET['code'],
        'client_id' => SUAP_CLIENT_ID,
        'client_secret' => SUAP_CLIENT_SECRET,
        'redirect_uri' => REDIRECT_URI
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Apenas para debug
    
    $response = curl_exec($ch);
    
    if(curl_errno($ch)) {
        error_log("Curl error: " . curl_error($ch));
    }
    
    error_log("Resposta do token: " . $response);
    curl_close($ch);
    
    $token_data = json_decode($response, true);
    if (isset($token_data['access_token'])) {
        $_SESSION['access_token'] = $token_data['access_token'];
        error_log("Token recebido com sucesso");
        header('Location: index.php');
        exit;
    } else {
        error_log("Erro nos dados do token: " . print_r($token_data, true));
    }
}

error_log("Redirecionando para error");
header('Location: index.php?error=auth_failed');
exit;
