<?php
require_once 'config.php';
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_log("=== Mobile API Request ===");
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("GET params: " . print_r($_GET, true));
error_log("Session ID: " . session_id());
error_log("Session data: " . print_r($_SESSION, true));

if (isset($_GET['check_token']) && isset($_GET['mobile_id'])) {
    $mobile_id = $_GET['mobile_id'];
    $temp_file = sys_get_temp_dir() . '/supaco_mobile_' . $mobile_id . '.json';
    
    error_log("Verificando token para mobile_id: " . $mobile_id);
    error_log("Arquivo: " . $temp_file);
    
    if (file_exists($temp_file)) {
        $file_age = time() - filemtime($temp_file);
        
        if ($file_age > 300) {
            error_log("Arquivo expirado (idade: " . $file_age . "s)");
            unlink($temp_file);
            http_response_code(410);
            echo json_encode([
                'success' => false,
                'error' => 'token_expired',
                'message' => 'Token expirou. Faça login novamente.'
            ]);
            exit;
        }
        
        $data = json_decode(file_get_contents($temp_file), true);
        error_log("Token encontrado - retornando para mobile");
        
        unlink($temp_file);
        
        echo json_encode($data);
        exit;
    } else {
        error_log("Arquivo não encontrado - ainda aguardando");
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'token_not_found',
            'message' => 'Aguardando autenticação'
        ]);
        exit;
    }
}

http_response_code(400);
echo json_encode([
    'success' => false,
    'error' => 'invalid_request',
    'message' => 'Parâmetro inválido'
]);

