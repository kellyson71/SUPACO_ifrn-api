<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'success' => true,
    'message' => 'mobile-api.php está funcionando!',
    'timestamp' => time(),
    'server' => $_SERVER['SERVER_NAME'],
    'temp_dir' => sys_get_temp_dir(),
    'php_version' => PHP_VERSION
]);

