# Documentação da API do SUAP (Baseada no Projeto SUPACO)

Este documento descreve como a API do SUAP (Sistema Unificado de Administração Pública) é utilizada no projeto SUPACO. As informações aqui contidas foram inferidas a partir da análise do código-fonte do projeto.

## 1. URL Base da API

A URL base para todas as chamadas da API v2 do SUAP é:

```
https://suap.ifrn.edu.br/api/v2/
```

Isso é definido na constante `SUAP_URL` (em `config.php`) e concatenado com `/api/v2/` nas funções que realizam as chamadas.

## 2. Autenticação

O projeto utiliza o fluxo de autorização OAuth2 para autenticar usuários e obter acesso aos dados da API.

### Fluxo de Autenticação:

1.  **Redirecionamento para Autorização**:

    - O usuário é redirecionado para a URL de autorização do SUAP:
      ```
      https://suap.ifrn.edu.br/o/authorize/?response_type=code&client_id=[SEU_CLIENT_ID]&redirect_uri=[SUA_REDIRECT_URI]
      ```
    - `[SEU_CLIENT_ID]` é o ID do cliente da sua aplicação registrada no SUAP (definido como `SUAP_CLIENT_ID` em `config.php`).
    - `[SUA_REDIRECT_URI]` é a URI para a qual o SUAP redirecionará o usuário após a autorização (definida como `REDIRECT_URI` em `config.php`, por exemplo, `http://localhost/SUAP/callback.php`).
    - Este passo é iniciado em `login.php`.

2.  **Obtenção do Código de Autorização**:

    - Após o usuário autorizar a aplicação, o SUAP redireciona de volta para a `REDIRECT_URI` com um parâmetro `code` na URL.

3.  **Troca do Código pelo Token de Acesso**:

    - O script em `callback.php` recebe o `code`.
    - Uma requisição POST é feita para o endpoint de token do SUAP:
      ```
      POST https://suap.ifrn.edu.br/o/token/
      ```
    - **Corpo da Requisição** (form-urlencoded):
      - `grant_type`: "authorization_code"
      - `code`: O código recebido no passo anterior.
      - `redirect_uri`: A mesma `REDIRECT_URI` usada no passo 1.
      - `client_id`: Seu `SUAP_CLIENT_ID`.
      - `client_secret`: Seu `SUAP_CLIENT_SECRET` (definido em `config.php`).
    - **Resposta Esperada** (JSON):
      ```json
      {
        "access_token": "SEU_ACCESS_TOKEN",
        "expires_in": 36000,
        "token_type": "Bearer",
        "scope": "read write",
        "refresh_token": "SEU_REFRESH_TOKEN"
      }
      ```
    - O `access_token`, `refresh_token` e o tempo de expiração (`access_token_expires`, calculado a partir de `expires_in`) são armazenados na sessão do usuário.

4.  **Obtenção de Dados do Usuário (Opcional, mas feito no callback)**:
    - Imediatamente após obter o token, `callback.php` faz uma requisição GET para:
      ```
      GET https://suap.ifrn.edu.br/api/v2/minhas-informacoes/meus-dados/
      ```
      - **Header de Autorização**: `Authorization: Bearer [ACCESS_TOKEN]`
    - Os dados do usuário (como ID, matrícula, nome) são armazenados na sessão.

### Refresh Token:

O projeto armazena o `refresh_token` na sessão, mas a lógica para utilizá-lo para obter um novo `access_token` quando o atual expira não está explicitamente detalhada nos trechos de código analisados para esta documentação (embora `index.php` verifique a expiração do token). Geralmente, o fluxo de refresh token envolve uma requisição POST para `https://suap.ifrn.edu.br/o/token/` com:

- `grant_type`: "refresh_token"
- `refresh_token`: O `refresh_token` armazenado.
- `client_id`: Seu `SUAP_CLIENT_ID`.
- `client_secret`: Seu `SUAP_CLIENT_SECRET`.

## 3. Endpoints Utilizados

As seguintes rotas da API do SUAP são consumidas pelo projeto:

### 3.1. Informações do Usuário

- **Endpoint**: `minhas-informacoes/meus-dados/`
- **Método**: `GET`
- **Descrição**: Retorna dados cadastrais do usuário autenticado.
- **Utilizado em**: `callback.php` (após obter o token) e potencialmente em `index.php` através de `getSuapData`.

### 3.2. Boletim

- **Endpoint**: `meu-diario/boletim/[ano_letivo]/[periodo_letivo]/`
- **Método**: `GET`
- **Descrição**: Retorna o boletim do aluno para um ano e período letivo específico.
  - `[ano_letivo]`: Ano letivo (ex: 2023).
  - `[periodo_letivo]`: Período letivo (ex: 1 ou 2).
- **Utilizado em**: `index.php` através da função `getSuapData`.

### 3.3. Horário

- **Endpoint**: `meu-diario/horario/[ano_letivo]/[periodo_letivo]/`
- **Método**: `GET`
- **Descrição**: Retorna o horário de aulas do aluno para um ano e período letivo específico.
  - `[ano_letivo]`: Ano letivo.
  - `[periodo_letivo]`: Período letivo.
- **Utilizado em**: `index.php` através da função `getSuapData`.

### 3.4. Turmas Virtuais

- **Endpoint**: `meu-diario/turmas-virtuais/`
- **Método**: `GET`
- **Descrição**: Lista as turmas virtuais do aluno.
- **Utilizado em**: `index.php` através da função `getSuapData`.

- **Endpoint**: `meu-diario/turmas-virtuais/[id_turma]/`
- **Método**: `GET`
- **Descrição**: Retorna detalhes de uma turma virtual específica.
  - `[id_turma]`: ID da turma virtual.
- **Utilizado em**: `index.php` através da função `getSuapData`.

## 4. Como Fazer Requisições à API

As requisições para a API do SUAP, após a obtenção do `access_token`, são feitas utilizando cURL em PHP. As funções `getSuapData` (em `index.php`) e `suap_request` (em `api_utils.php`) encapsulam essa lógica.

### Exemplo Genérico de Requisição (baseado em `getSuapData`):

```php
<?php
session_start(); // Necessário para acessar o token da sessão

function getSuapData($endpoint) {
    if (!isset($_SESSION['access_token'])) {
        // Tratar erro: usuário não autenticado ou token não disponível
        return null;
    }

    $token = $_SESSION['access_token'];
    $suap_url_base = 'https://suap.ifrn.edu.br/api/v2/'; // Conforme definido em config.php e usado nas chamadas

    $url = $suap_url_base . $endpoint;

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . $token,
            "Accept: application/json" // É uma boa prática especificar o Accept header
        ],
        // CURLOPT_VERBOSE => true, // Útil para debug
        // CURLOPT_SSL_VERIFYPEER => false, // Apenas para desenvolvimento local, se necessário
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        error_log("Erro cURL ao acessar SUAP: " . $curl_error);
        return null; // Ou lançar uma exceção
    }

    if ($http_code >= 200 && $http_code < 300) {
        return json_decode($response, true);
    } else {
        error_log("Erro na API SUAP (HTTP {$http_code}): " . $response);
        // Tratar diferentes códigos de erro (ex: 401 Unauthorized para token expirado)
        return null; // Ou lançar uma exceção
    }
}

// Exemplo de uso:
// $dadosUsuario = getSuapData("minhas-informacoes/meus-dados/");
// $boletim = getSuapData("meu-diario/boletim/2023/1/");
?>
```

### Considerações Importantes:

- **Token de Acesso**: Todas as requisições aos endpoints protegidos devem incluir o `access_token` no header `Authorization` como um "Bearer token".
- **Tratamento de Erros**: É crucial verificar o código de status HTTP da resposta e tratar possíveis erros, como token expirado (`401 Unauthorized`), permissões insuficientes (`403 Forbidden`), ou recurso não encontrado (`404 Not Found`).
- **Escopo do Token**: O `access_token` obtido pode ter escopos limitados, permitindo acesso apenas a certos endpoints ou operações.
- **Rate Limiting**: APIs públicas geralmente implementam limites de taxa (rate limiting). Embora não detalhado no código, é uma consideração comum ao integrar com APIs.

Este documento serve como um guia inicial baseado no uso da API do SUAP pelo projeto SUPACO. Para informações completas e oficiais, consulte a documentação oficial da API do SUAP, se disponível.
