# Padrões de API - SUPACO

## Integração SUAP

### Autenticação
```php
// Configuração OAuth 2.0
define('SUAP_CLIENT_ID', 'seu_client_id');
define('SUAP_CLIENT_SECRET', 'seu_client_secret');
define('SUAP_URL', 'https://suap.ifrn.edu.br');
define('REDIRECT_URI', 'http://localhost/SUAP/callback.php');
```

### Endpoints Utilizados
- **Autenticação**: `/o/authorize/`
- **Token**: `/o/token/`
- **Dados do Usuário**: `/api/eu/`
- **Boletim**: `/api/v2/minhas-informacoes/boletim/{ano}/{periodo}/`

## Estrutura de Resposta

### Sucesso
```json
{
    "status": "success",
    "data": {
        // Dados retornados
    },
    "timestamp": "2024-01-01T00:00:00Z"
}
```

### Erro
```json
{
    "status": "error",
    "message": "Descrição do erro",
    "code": "ERROR_CODE",
    "timestamp": "2024-01-01T00:00:00Z"
}
```

## Tratamento de Erros

### Códigos HTTP
- **200**: Sucesso
- **401**: Não autorizado
- **403**: Acesso negado
- **404**: Não encontrado
- **500**: Erro interno

### Logs
```php
// Log de erro
error_log("SUAP API Error: " . $response['message']);

// Log de debug
error_log("SUAP Request: " . $endpoint);
```

## Cache e Performance

### Service Worker
```javascript
// Cache de recursos estáticos
const CACHE_NAME = 'supaco-v1';
const STATIC_CACHE_URLS = [
    '/SUAP/assets/css/dashboard.css',
    '/SUAP/assets/js/offline.js'
];
```

### Headers de Cache
- **CSS/JS**: Cache por 1 ano
- **Images**: Cache por 6 meses
- **API**: Cache por 5 minutos

## Validação de Dados

### Entrada
```php
function validarDadosEntrada($dados) {
    // Validação de tipos
    if (!is_array($dados)) {
        throw new InvalidArgumentException('Dados devem ser array');
    }
    
    // Validação de campos obrigatórios
    $camposObrigatorios = ['usuario', 'senha'];
    foreach ($camposObrigatorios as $campo) {
        if (!isset($dados[$campo])) {
            throw new InvalidArgumentException("Campo {$campo} é obrigatório");
        }
    }
    
    return true;
}
```

### Saída
```php
function sanitizarDadosSaida($dados) {
    if (is_array($dados)) {
        return array_map('htmlspecialchars', $dados);
    }
    
    return htmlspecialchars($dados);
}
```

## Rate Limiting

### Implementação
```php
// Limite de 100 requisições por hora
$rateLimitKey = 'api_limit_' . $_SESSION['user_id'];
$currentCount = $_SESSION[$rateLimitKey] ?? 0;

if ($currentCount >= 100) {
    http_response_code(429);
    echo json_encode(['error' => 'Rate limit exceeded']);
    exit;
}

$_SESSION[$rateLimitKey] = $currentCount + 1;
```

## Segurança

### Headers de Segurança
```php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000');
```

### Validação de Token
```php
function validarToken($token) {
    $payload = json_decode(base64_decode($token), true);
    
    if (!$payload || $payload['exp'] < time()) {
        return false;
    }
    
    return true;
}
```

## Monitoramento

### Métricas
- Tempo de resposta da API
- Taxa de erro
- Uso de cache
- Requisições por usuário

### Alertas
- Erro 500 > 5%
- Tempo de resposta > 2s
- Rate limit excedido
- Token expirado
