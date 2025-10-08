# Guia Completo - API do SUAP

## Sumário
1. [Introdução](#introdução)
2. [Configuração Inicial](#configuração-inicial)
3. [Fluxo de Autenticação OAuth2](#fluxo-de-autenticação-oauth2)
4. [Endpoints Disponíveis](#endpoints-disponíveis)
5. [Estrutura de Dados](#estrutura-de-dados)
6. [Implementação em React Native](#implementação-em-react-native)
7. [Tratamento de Erros](#tratamento-de-erros)
8. [Renovação de Token](#renovação-de-token)
9. [Exemplos Práticos](#exemplos-práticos)

---

## Introdução

A API do SUAP (Sistema Unificado de Administração Pública) do IFRN utiliza o protocolo **OAuth 2.0** para autenticação. Este guia documenta todo o processo de integração, desde a configuração inicial até o consumo dos dados.

### URL Base
```
https://suap.ifrn.edu.br
```

### Versão da API
```
/api/v2/
```

---

## Configuração Inicial

### 1. Registrar Aplicação no SUAP

Acesse: [https://suap.ifrn.edu.br/api/](https://suap.ifrn.edu.br/api/)

**Passos:**
1. Faça login com sua conta do SUAP
2. Vá em "Minhas Aplicações"
3. Clique em "Adicionar Aplicação"
4. Preencha os dados:

```
Nome da Aplicação: SUPACO (ou nome que preferir)
Tipo de Autorização: Authorization Code
Redirect URIs: https://seudominio.com/callback.php
              (para desenvolvimento local: http://localhost:3000/callback)
```

**Importante para React Native:**
- Para mobile, use Deep Links ou Universal Links
- Exemplo: `supaco://callback` ou `com.supaco.app://callback`

5. Anote:
   - **Client ID** (ID do Cliente)
   - **Client Secret** (Segredo do Cliente)

### 2. Variáveis de Ambiente

Crie um arquivo `.env` ou `config.php`:

```php
<?php
define('SUAP_URL', 'https://suap.ifrn.edu.br');
define('SUAP_CLIENT_ID', 'SEU_CLIENT_ID_AQUI');
define('SUAP_CLIENT_SECRET', 'SEU_CLIENT_SECRET_AQUI');
define('REDIRECT_URI', 'https://seudominio.com/callback.php');
```

**Para React Native (.env):**
```env
SUAP_URL=https://suap.ifrn.edu.br
SUAP_CLIENT_ID=seu_client_id
SUAP_CLIENT_SECRET=seu_client_secret
REDIRECT_URI=supaco://callback
```

---

## Fluxo de Autenticação OAuth2

### Passo 1: Redirecionar para Autorização

**URL de Autorização:**
```
https://suap.ifrn.edu.br/o/authorize/?response_type=code&client_id={CLIENT_ID}&redirect_uri={REDIRECT_URI}
```

**Exemplo em PHP (login.php):**
```php
$auth_url = SUAP_URL . "/o/authorize/?" . http_build_query([
    'response_type' => 'code',
    'client_id' => SUAP_CLIENT_ID,
    'redirect_uri' => REDIRECT_URI
]);

header("Location: " . $auth_url);
```

**Exemplo em React Native:**
```javascript
import { Linking } from 'react-native';

const SUAP_URL = 'https://suap.ifrn.edu.br';
const CLIENT_ID = 'seu_client_id';
const REDIRECT_URI = 'supaco://callback';

const loginWithSUAP = () => {
  const authUrl = `${SUAP_URL}/o/authorize/?response_type=code&client_id=${CLIENT_ID}&redirect_uri=${REDIRECT_URI}`;
  Linking.openURL(authUrl);
};
```

### Passo 2: Receber o Código de Autorização

Após o usuário autorizar, o SUAP redireciona para:
```
https://seudominio.com/callback.php?code=CODIGO_DE_AUTORIZACAO
```

**Possíveis Erros:**
```
https://seudominio.com/callback.php?error=access_denied&error_description=Usuário+negou+acesso
```

### Passo 3: Trocar Código por Token

**Endpoint:**
```
POST https://suap.ifrn.edu.br/o/token/
```

**Parâmetros (form-urlencoded):**
```
grant_type: authorization_code
code: {codigo_recebido}
client_id: {seu_client_id}
client_secret: {seu_client_secret}
redirect_uri: {seu_redirect_uri}
```

**Exemplo em PHP (callback.php):**
```php
$token_request = [
    'grant_type' => 'authorization_code',
    'code' => $_GET['code'],
    'client_id' => SUAP_CLIENT_ID,
    'client_secret' => SUAP_CLIENT_SECRET,
    'redirect_uri' => REDIRECT_URI
];

$ch = curl_init(SUAP_URL . "/o/token/");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($token_request),
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false
]);

$response = curl_exec($ch);
$token_data = json_decode($response, true);
curl_close($ch);
```

**Exemplo em React Native:**
```javascript
const exchangeCodeForToken = async (code) => {
  const response = await fetch('https://suap.ifrn.edu.br/o/token/', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams({
      grant_type: 'authorization_code',
      code: code,
      client_id: CLIENT_ID,
      client_secret: CLIENT_SECRET,
      redirect_uri: REDIRECT_URI,
    }).toString(),
  });
  
  const tokenData = await response.json();
  return tokenData;
};
```

### Passo 4: Resposta do Token

**Sucesso (200 OK):**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "refresh_token": "aBcDeFgHiJkLmN...",
  "scope": "read write"
}
```

**Campos:**
- `access_token`: Token para fazer requisições (válido por 1 hora)
- `refresh_token`: Token para renovar o access_token
- `expires_in`: Tempo de expiração em segundos (3600 = 1 hora)
- `token_type`: Sempre "Bearer"

**Armazenamento:**
```javascript
// React Native - AsyncStorage
import AsyncStorage from '@react-native-async-storage/async-storage';

await AsyncStorage.setItem('access_token', tokenData.access_token);
await AsyncStorage.setItem('refresh_token', tokenData.refresh_token);
await AsyncStorage.setItem('token_expires', String(Date.now() + tokenData.expires_in * 1000));
```

---

## Endpoints Disponíveis

### 1. Dados do Usuário

**Endpoint:**
```
GET /api/v2/minhas-informacoes/meus-dados/
```

**Headers:**
```
Authorization: Bearer {access_token}
```

**Exemplo PHP:**
```php
$ch = curl_init(SUAP_URL . "/api/v2/minhas-informacoes/meus-dados/");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . $access_token
    ],
    CURLOPT_SSL_VERIFYPEER => false
]);
$response = curl_exec($ch);
$userData = json_decode($response, true);
```

**Exemplo React Native:**
```javascript
const getUserData = async (accessToken) => {
  const response = await fetch(
    'https://suap.ifrn.edu.br/api/v2/minhas-informacoes/meus-dados/',
    {
      headers: {
        'Authorization': `Bearer ${accessToken}`,
      },
    }
  );
  return await response.json();
};
```

**Resposta:**
```json
{
  "id": 12345,
  "matricula": "20241234567",
  "nome_usual": "João Silva",
  "cpf": "123.456.789-00",
  "email": "joao.silva@escolar.ifrn.edu.br",
  "url_foto_75x100": "https://suap.ifrn.edu.br/media/fotos/...",
  "url_foto_150x200": "https://suap.ifrn.edu.br/media/fotos/...",
  "tipo_usuario": "Aluno",
  "vinculo": {
    "matricula": "20241234567",
    "nome": "João Silva dos Santos",
    "curso": "Tecnologia em Análise e Desenvolvimento de Sistemas",
    "campus": "Campus Natal - Central",
    "situacao": "Ativo"
  }
}
```

### 2. Boletim (Notas)

**Endpoint:**
```
GET /api/v2/minhas-informacoes/boletim/{ano}/{periodo}/
```

**Parâmetros:**
- `ano`: Ano letivo (ex: 2024)
- `periodo`: Período letivo (1 ou 2)

**Exemplo:**
```
GET /api/v2/minhas-informacoes/boletim/2024/1/
```

**Lógica de Detecção Automática (do index.php):**
```php
function detectarPeriodoMaisRecente() {
    $anoAtual = date('Y');
    $mesAtual = date('n');
    
    // Janeiro a Junho = Período 1, Julho a Dezembro = Período 2
    $periodoAtual = ($mesAtual <= 6) ? 1 : 2;
    
    // Tenta período atual primeiro
    $periodosParaTentar = [[$anoAtual, $periodoAtual]];
    
    // Se estamos no primeiro semestre, adiciona segundo semestre do ano anterior
    if ($periodoAtual == 1) {
        $periodosParaTentar[] = [$anoAtual - 1, 2];
    }
    
    // Adiciona anos anteriores (últimos 3 anos)
    for ($i = 1; $i <= 3; $i++) {
        $ano = $anoAtual - $i;
        $periodosParaTentar[] = [$ano, 2];
        $periodosParaTentar[] = [$ano, 1];
    }
    
    return $periodosParaTentar;
}
```

**Exemplo React Native:**
```javascript
const getBoletim = async (accessToken, ano, periodo) => {
  const response = await fetch(
    `https://suap.ifrn.edu.br/api/v2/minhas-informacoes/boletim/${ano}/${periodo}/`,
    {
      headers: {
        'Authorization': `Bearer ${accessToken}`,
      },
    }
  );
  
  if (response.status === 404) {
    // Período não encontrado
    return null;
  }
  
  return await response.json();
};

// Buscar período mais recente
const getCurrentBoletim = async (accessToken) => {
  const now = new Date();
  const ano = now.getFullYear();
  const periodo = now.getMonth() < 6 ? 1 : 2;
  
  // Tenta período atual
  let boletim = await getBoletim(accessToken, ano, periodo);
  
  if (!boletim || boletim.length === 0) {
    // Tenta período anterior
    const periodoAnterior = periodo === 1 ? 2 : 1;
    const anoAnterior = periodo === 1 ? ano - 1 : ano;
    boletim = await getBoletim(accessToken, anoAnterior, periodoAnterior);
  }
  
  return boletim;
};
```

**Resposta (Array de Disciplinas):**
```json
[
  {
    "disciplina": "TEC.0010 - Programação Orientada a Objetos",
    "codigo_diario": "12345",
    "carga_horaria": 80,
    "carga_horaria_cumprida": 40,
    "numero_faltas": 2,
    "percentual_carga_horaria_frequentada": 97.5,
    "nota_etapa_1": {
      "nota": 85.0,
      "numero_avaliacoes": 2
    },
    "nota_etapa_2": {
      "nota": 90.0,
      "numero_avaliacoes": 3
    },
    "nota_etapa_3": {
      "nota": null,
      "numero_avaliacoes": 0
    },
    "nota_etapa_4": {
      "nota": null,
      "numero_avaliacoes": 0
    },
    "media_final_disciplina": null,
    "situacao": "Cursando"
  }
]
```

**Estrutura de Notas por Sistema:**

**Sistema IF (2 Bimestres):**
- `nota_etapa_1`: N1
- `nota_etapa_2`: N2
- Média Direta (MD) = (2×N1 + 3×N2) ÷ 5

**Sistema Tradicional (4 Bimestres):**
- `nota_etapa_1`, `nota_etapa_2`, `nota_etapa_3`, `nota_etapa_4`
- Pode ter `primeiro_semestre` e `segundo_semestre` como objetos separados

### 3. Horários (Turmas Virtuais)

**Endpoint:**
```
GET /api/v2/minhas-informacoes/turmas-virtuais/{ano}/{periodo}/
```

**Exemplo:**
```
GET /api/v2/minhas-informacoes/turmas-virtuais/2024/1/
```

**Resposta:**
```json
[
  {
    "id": 123456,
    "sigla": "POO",
    "descricao": "Programação Orientada a Objetos",
    "periodo_letivo": 3,
    "horarios_de_aula": "2M12,4M34",
    "locais_de_aula": ["Lab 01", "Lab 02"],
    "codigo_turma": "20241.1.TEC.0010.1A"
  },
  {
    "id": 123457,
    "sigla": "BD",
    "descricao": "Banco de Dados",
    "periodo_letivo": 3,
    "horarios_de_aula": "3V12 / 5V34",
    "locais_de_aula": ["Lab 03"]
  }
]
```

**Formato do Campo `horarios_de_aula`:**

```
Padrão: {DIA}{TURNO}{AULAS}

DIA: 
  2 = Segunda-feira
  3 = Terça-feira
  4 = Quarta-feira
  5 = Quinta-feira
  6 = Sexta-feira

TURNO:
  M = Manhã
  V = Vespertino (Tarde)
  N = Noturno

AULAS: Sequência de números (1-6)
  Manhã:
    1 = 07:00 - 07:45
    2 = 07:45 - 08:30
    3 = 08:50 - 09:35
    4 = 09:35 - 10:20
    5 = 10:30 - 11:15
    6 = 11:15 - 12:00
  
  Tarde/Vespertino:
    1 = 13:00 - 13:45
    2 = 13:45 - 14:30
    3 = 14:50 - 15:35
    4 = 15:35 - 16:20
    5 = 16:30 - 17:15
    6 = 17:15 - 18:00

Exemplos:
  "2M12" = Segunda-feira, Manhã, aulas 1 e 2 (07:00 às 08:30)
  "3V34" = Terça-feira, Tarde, aulas 3 e 4 (14:50 às 16:20)
  "2M12 / 4M34" = Múltiplos horários separados por " / "
```

**Parser de Horários (horarios.php):**
```php
function parseHorario($horarioStr) {
    if (empty($horarioStr)) {
        return [];
    }
    
    // Separa múltiplos horários
    $horarios = preg_split('/\s*\/\s*/', $horarioStr);
    $result = [];
    
    foreach ($horarios as $horario) {
        $horario = trim($horario);
        
        // Padrão: 2M12 (dia, turno, aulas)
        if (preg_match('/^(\d)([MTV])(\d+)$/', $horario, $matches)) {
            $result[] = [
                'dia' => (int)$matches[1],      // 2-6
                'turno' => $matches[2],          // M, V ou N
                'aulas' => str_split($matches[3]) // ['1', '2']
            ];
        }
    }
    
    return $result;
}
```

**Exemplo React Native:**
```javascript
const parseHorario = (horarioStr) => {
  if (!horarioStr) return [];
  
  const horarios = horarioStr.split('/').map(h => h.trim());
  const result = [];
  
  horarios.forEach(horario => {
    const match = horario.match(/^(\d)([MTV])(\d+)$/);
    if (match) {
      result.push({
        dia: parseInt(match[1]),
        turno: match[2],
        aulas: match[3].split(''),
      });
    }
  });
  
  return result;
};

const getHorarioDetalhado = (turno, numeroAula) => {
  const horarios = {
    M: {
      '1': '07:00 - 07:45',
      '2': '07:45 - 08:30',
      '3': '08:50 - 09:35',
      '4': '09:35 - 10:20',
      '5': '10:30 - 11:15',
      '6': '11:15 - 12:00',
    },
    V: {
      '1': '13:00 - 13:45',
      '2': '13:45 - 14:30',
      '3': '14:50 - 15:35',
      '4': '15:35 - 16:20',
      '5': '16:30 - 17:15',
      '6': '17:15 - 18:00',
    },
  };
  
  return horarios[turno]?.[numeroAula] || 'Horário não definido';
};
```

---

## Estrutura de Dados

### Sistema de Cálculo de Notas IF

**Fórmula da Média Direta (MD):**
```
MD = (2×N1 + 3×N2) ÷ 5
```

**Critérios de Aprovação:**
- MD ≥ 60: **Aprovado Direto**
- 20 ≤ MD < 60: **Avaliação Final (AF)**
- MD < 20: **Reprovado por Nota**

**Cálculo da Nota Necessária:**

Se tiver apenas N1:
```
N2 necessário = (300 - 2×N1) ÷ 3
```

Se tiver apenas N2:
```
N1 necessário = (300 - 3×N2) ÷ 2
```

**Cálculo da Avaliação Final (3 fórmulas):**

O SUAP considera a MENOR nota necessária das 3 fórmulas:

```javascript
function calcularAvaliacaoFinal(n1, n2) {
  const md = (2 * n1 + 3 * n2) / 5;
  
  // Fórmula 1: MFD = (MD + NAF) / 2 >= 60
  const naf1 = 120 - md;
  
  // Fórmula 2: MFD = (2×NAF + 3×N2) / 5 >= 60
  const naf2 = (300 - 3 * n2) / 2;
  
  // Fórmula 3: MFD = (2×N1 + 3×NAF) / 5 >= 60
  const naf3 = (300 - 2 * n1) / 3;
  
  // Menor nota (mais favorável ao aluno)
  const nafNecessaria = Math.min(naf1, naf2, naf3);
  
  return {
    md,
    nafNecessaria: Math.max(0, Math.min(100, nafNecessaria)),
    podePassar: nafNecessaria <= 100,
  };
}
```

**Implementação Completa (index.php):**
```php
function calcularNotaNecessariaIF($disciplina) {
    $n1 = isset($disciplina['nota_etapa_1']['nota']) 
        ? floatval($disciplina['nota_etapa_1']['nota']) 
        : null;
    $n2 = isset($disciplina['nota_etapa_2']['nota']) 
        ? floatval($disciplina['nota_etapa_2']['nota']) 
        : null;
    
    $resultado = [
        'n1' => $n1,
        'n2' => $n2,
        'media_atual' => null,
        'nota_necessaria' => null,
        'situacao' => 'indefinida',
        'pode_passar_direto' => false,
        'precisa_af' => false,
        'ja_aprovado' => false,
        'ja_reprovado' => false
    ];
    
    // Tem as duas notas
    if ($n1 !== null && $n2 !== null) {
        $md = (2 * $n1 + 3 * $n2) / 5;
        $resultado['media_atual'] = $md;
        
        if ($md >= 60) {
            $resultado['situacao'] = 'aprovado_direto';
            $resultado['ja_aprovado'] = true;
        } elseif ($md >= 20) {
            $resultado['situacao'] = 'avaliacao_final';
            $resultado['precisa_af'] = true;
        } else {
            $resultado['situacao'] = 'reprovado_nota';
            $resultado['ja_reprovado'] = true;
        }
    }
    // Só tem N1
    elseif ($n1 !== null && $n2 === null) {
        $nota_necessaria = (300 - 2 * $n1) / 3;
        $resultado['nota_necessaria'] = max(0, min(100, $nota_necessaria));
        $resultado['situacao'] = 'aguardando_n2';
        $resultado['pode_passar_direto'] = $nota_necessaria <= 100;
    }
    // Só tem N2
    elseif ($n1 === null && $n2 !== null) {
        $nota_necessaria = (300 - 3 * $n2) / 2;
        $resultado['nota_necessaria'] = max(0, min(100, $nota_necessaria));
        $resultado['situacao'] = 'aguardando_n1';
    }
    // Nenhuma nota
    else {
        $resultado['situacao'] = 'aguardando_notas';
    }
    
    return $resultado;
}
```

### Cálculo de Frequência e Faltas

**Regra do IFRN:**
- Mínimo de 75% de frequência
- Máximo de 25% de faltas

**Cálculos:**
```javascript
function calcularImpactoFalta(disciplina) {
  const frequenciaAtual = disciplina.percentual_carga_horaria_frequentada;
  const totalAulas = disciplina.carga_horaria_cumprida;
  const totalFaltas = disciplina.numero_faltas;
  const cargaTotal = disciplina.carga_horaria;
  const maximoFaltas = Math.ceil(cargaTotal * 0.25);
  
  // Nova frequência com mais uma falta
  const novaFrequencia = ((totalAulas - totalFaltas - 1) / totalAulas) * 100;
  
  // Faltas restantes
  const faltasRestantes = maximoFaltas - totalFaltas;
  
  // Nível de risco
  let nivelRisco = 'baixo';
  if (faltasRestantes <= 3 && faltasRestantes > 0) {
    nivelRisco = 'medio';
  } else if (faltasRestantes <= 0) {
    nivelRisco = 'alto';
  }
  
  return {
    atual: frequenciaAtual,
    nova: Math.max(0, novaFrequencia),
    impacto: frequenciaAtual - novaFrequencia,
    faltas_atuais: totalFaltas,
    maximo_faltas: maximoFaltas,
    faltas_restantes: Math.max(0, faltasRestantes),
    nivel_risco: nivelRisco,
    proporcao_faltas: Math.min(100, (totalFaltas / maximoFaltas) * 100),
  };
}
```

---

## Implementação em React Native

### Estrutura de Pastas Sugerida

```
src/
├── services/
│   ├── suap/
│   │   ├── auth.js          // Autenticação OAuth
│   │   ├── api.js           // Chamadas API
│   │   ├── storage.js       // AsyncStorage
│   │   └── utils.js         // Utilitários
│   └── calculators/
│       ├── notas.js         // Cálculos de notas
│       └── frequencia.js    // Cálculos de frequência
├── screens/
│   ├── Login.js
│   ├── Dashboard.js
│   ├── Boletim.js
│   └── Horarios.js
└── components/
    ├── DisciplinaCard.js
    ├── HorarioCard.js
    └── StatusFrequencia.js
```

### 1. Configuração do Deep Linking

**app.json (Expo):**
```json
{
  "expo": {
    "scheme": "supaco",
    "android": {
      "intentFilters": [
        {
          "action": "VIEW",
          "data": [
            {
              "scheme": "supaco"
            }
          ],
          "category": [
            "BROWSABLE",
            "DEFAULT"
          ]
        }
      ]
    }
  }
}
```

### 2. Serviço de Autenticação

**services/suap/auth.js:**
```javascript
import { Linking } from 'react-native';
import * as WebBrowser from 'expo-web-browser';
import { saveToken, getToken, clearTokens } from './storage';

const SUAP_URL = 'https://suap.ifrn.edu.br';
const CLIENT_ID = 'seu_client_id';
const CLIENT_SECRET = 'seu_client_secret';
const REDIRECT_URI = 'supaco://callback';

// Autenticar com SUAP
export const authenticateWithSUAP = async () => {
  const authUrl = `${SUAP_URL}/o/authorize/?response_type=code&client_id=${CLIENT_ID}&redirect_uri=${REDIRECT_URI}`;
  
  // Abre o navegador
  const result = await WebBrowser.openAuthSessionAsync(authUrl, REDIRECT_URI);
  
  if (result.type === 'success') {
    const { url } = result;
    const code = extractCodeFromUrl(url);
    
    if (code) {
      return await exchangeCodeForToken(code);
    }
  }
  
  throw new Error('Autenticação cancelada');
};

// Extrair código da URL
const extractCodeFromUrl = (url) => {
  const match = url.match(/code=([^&]+)/);
  return match ? match[1] : null;
};

// Trocar código por token
const exchangeCodeForToken = async (code) => {
  try {
    const response = await fetch(`${SUAP_URL}/o/token/`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        grant_type: 'authorization_code',
        code: code,
        client_id: CLIENT_ID,
        client_secret: CLIENT_SECRET,
        redirect_uri: REDIRECT_URI,
      }).toString(),
    });
    
    if (!response.ok) {
      throw new Error('Falha ao obter token');
    }
    
    const tokenData = await response.json();
    
    // Salvar tokens
    await saveToken('access_token', tokenData.access_token);
    await saveToken('refresh_token', tokenData.refresh_token);
    await saveToken('token_expires', String(Date.now() + tokenData.expires_in * 1000));
    
    return tokenData;
  } catch (error) {
    console.error('Erro ao trocar código por token:', error);
    throw error;
  }
};

// Renovar token
export const refreshAccessToken = async () => {
  const refreshToken = await getToken('refresh_token');
  
  if (!refreshToken) {
    throw new Error('Refresh token não encontrado');
  }
  
  try {
    const response = await fetch(`${SUAP_URL}/o/token/`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        grant_type: 'refresh_token',
        refresh_token: refreshToken,
        client_id: CLIENT_ID,
        client_secret: CLIENT_SECRET,
      }).toString(),
    });
    
    if (!response.ok) {
      // Refresh token inválido, precisa fazer login novamente
      await clearTokens();
      throw new Error('Sessão expirada');
    }
    
    const tokenData = await response.json();
    
    // Atualizar tokens
    await saveToken('access_token', tokenData.access_token);
    if (tokenData.refresh_token) {
      await saveToken('refresh_token', tokenData.refresh_token);
    }
    await saveToken('token_expires', String(Date.now() + tokenData.expires_in * 1000));
    
    return tokenData;
  } catch (error) {
    console.error('Erro ao renovar token:', error);
    throw error;
  }
};

// Verificar se token é válido
export const isTokenValid = async () => {
  const expiresAt = await getToken('token_expires');
  
  if (!expiresAt) {
    return false;
  }
  
  // Considera inválido se falta menos de 5 minutos para expirar
  return Date.now() < (parseInt(expiresAt) - 5 * 60 * 1000);
};

// Fazer logout
export const logout = async () => {
  await clearTokens();
};
```

### 3. Serviço de API

**services/suap/api.js:**
```javascript
import { getToken } from './storage';
import { refreshAccessToken, isTokenValid } from './auth';

const SUAP_URL = 'https://suap.ifrn.edu.br';

// Fazer requisição autenticada
const authenticatedFetch = async (endpoint, options = {}) => {
  // Verificar se token é válido
  const valid = await isTokenValid();
  
  if (!valid) {
    // Tentar renovar token
    try {
      await refreshAccessToken();
    } catch (error) {
      throw new Error('Sessão expirada. Faça login novamente.');
    }
  }
  
  const accessToken = await getToken('access_token');
  
  const response = await fetch(`${SUAP_URL}${endpoint}`, {
    ...options,
    headers: {
      'Authorization': `Bearer ${accessToken}`,
      'Content-Type': 'application/json',
      ...options.headers,
    },
  });
  
  if (response.status === 401) {
    // Token inválido, tentar renovar
    try {
      await refreshAccessToken();
      // Tentar novamente
      return authenticatedFetch(endpoint, options);
    } catch (error) {
      throw new Error('Sessão expirada. Faça login novamente.');
    }
  }
  
  if (!response.ok) {
    throw new Error(`Erro ${response.status}: ${response.statusText}`);
  }
  
  return await response.json();
};

// Obter dados do usuário
export const getUserData = async () => {
  return await authenticatedFetch('/api/v2/minhas-informacoes/meus-dados/');
};

// Obter boletim
export const getBoletim = async (ano, periodo) => {
  try {
    return await authenticatedFetch(`/api/v2/minhas-informacoes/boletim/${ano}/${periodo}/`);
  } catch (error) {
    if (error.message.includes('404')) {
      return null; // Período não encontrado
    }
    throw error;
  }
};

// Obter boletim atual (com fallback)
export const getCurrentBoletim = async () => {
  const now = new Date();
  const ano = now.getFullYear();
  const periodo = now.getMonth() < 6 ? 1 : 2;
  
  // Tentar período atual
  let boletim = await getBoletim(ano, periodo);
  
  if (!boletim || boletim.length === 0) {
    // Tentar período anterior
    const periodoAnterior = periodo === 1 ? 2 : 1;
    const anoAnterior = periodo === 1 ? ano - 1 : ano;
    boletim = await getBoletim(anoAnterior, periodoAnterior);
  }
  
  return boletim;
};

// Obter horários
export const getHorarios = async (ano, periodo) => {
  try {
    return await authenticatedFetch(`/api/v2/minhas-informacoes/turmas-virtuais/${ano}/${periodo}/`);
  } catch (error) {
    if (error.message.includes('404')) {
      return null;
    }
    throw error;
  }
};

// Obter horários atuais
export const getCurrentHorarios = async () => {
  const now = new Date();
  const ano = now.getFullYear();
  const periodo = now.getMonth() < 6 ? 1 : 2;
  
  let horarios = await getHorarios(ano, periodo);
  
  if (!horarios || horarios.length === 0) {
    const periodoAnterior = periodo === 1 ? 2 : 1;
    const anoAnterior = periodo === 1 ? ano - 1 : ano;
    horarios = await getHorarios(anoAnterior, periodoAnterior);
  }
  
  return horarios;
};
```

### 4. Storage

**services/suap/storage.js:**
```javascript
import AsyncStorage from '@react-native-async-storage/async-storage';

const KEYS = {
  ACCESS_TOKEN: '@supaco:access_token',
  REFRESH_TOKEN: '@supaco:refresh_token',
  TOKEN_EXPIRES: '@supaco:token_expires',
  USER_DATA: '@supaco:user_data',
  BOLETIM: '@supaco:boletim',
  HORARIOS: '@supaco:horarios',
};

export const saveToken = async (key, value) => {
  await AsyncStorage.setItem(KEYS[key.toUpperCase()], value);
};

export const getToken = async (key) => {
  return await AsyncStorage.getItem(KEYS[key.toUpperCase()]);
};

export const clearTokens = async () => {
  await AsyncStorage.multiRemove([
    KEYS.ACCESS_TOKEN,
    KEYS.REFRESH_TOKEN,
    KEYS.TOKEN_EXPIRES,
  ]);
};

export const saveUserData = async (userData) => {
  await AsyncStorage.setItem(KEYS.USER_DATA, JSON.stringify(userData));
};

export const getUserData = async () => {
  const data = await AsyncStorage.getItem(KEYS.USER_DATA);
  return data ? JSON.parse(data) : null;
};

export const saveBoletim = async (boletim) => {
  await AsyncStorage.setItem(KEYS.BOLETIM, JSON.stringify(boletim));
};

export const getBoletim = async () => {
  const data = await AsyncStorage.getItem(KEYS.BOLETIM);
  return data ? JSON.parse(data) : null;
};

export const saveHorarios = async (horarios) => {
  await AsyncStorage.setItem(KEYS.HORARIOS, JSON.stringify(horarios));
};

export const getHorarios = async () => {
  const data = await AsyncStorage.getItem(KEYS.HORARIOS);
  return data ? JSON.parse(data) : null;
};

export const clearAll = async () => {
  await AsyncStorage.multiRemove(Object.values(KEYS));
};
```

### 5. Calculadoras

**services/calculators/notas.js:**
```javascript
// Calcular Média Direta (Sistema IF)
export const calcularMediaDireta = (n1, n2) => {
  if (n1 === null || n2 === null) {
    return null;
  }
  return (2 * n1 + 3 * n2) / 5;
};

// Calcular nota necessária
export const calcularNotaNecessaria = (disciplina) => {
  const n1 = disciplina.nota_etapa_1?.nota ?? null;
  const n2 = disciplina.nota_etapa_2?.nota ?? null;
  
  const resultado = {
    n1,
    n2,
    mediaAtual: null,
    notaNecessaria: null,
    situacao: 'indefinida',
    podePassarDireto: false,
    precisaAF: false,
    jaAprovado: false,
    jaReprovado: false,
  };
  
  // Tem as duas notas
  if (n1 !== null && n2 !== null) {
    const md = calcularMediaDireta(n1, n2);
    resultado.mediaAtual = md;
    
    if (md >= 60) {
      resultado.situacao = 'aprovado_direto';
      resultado.jaAprovado = true;
      resultado.podePassarDireto = true;
    } else if (md >= 20) {
      resultado.situacao = 'avaliacao_final';
      resultado.precisaAF = true;
    } else {
      resultado.situacao = 'reprovado_nota';
      resultado.jaReprovado = true;
    }
  }
  // Só tem N1
  else if (n1 !== null && n2 === null) {
    const notaNecessaria = (300 - 2 * n1) / 3;
    resultado.notaNecessaria = Math.max(0, Math.min(100, notaNecessaria));
    resultado.situacao = 'aguardando_n2';
    resultado.podePassarDireto = notaNecessaria <= 100;
  }
  // Só tem N2
  else if (n1 === null && n2 !== null) {
    const notaNecessaria = (300 - 3 * n2) / 2;
    resultado.notaNecessaria = Math.max(0, Math.min(100, notaNecessaria));
    resultado.situacao = 'aguardando_n1';
  }
  // Nenhuma nota
  else {
    resultado.situacao = 'aguardando_notas';
  }
  
  return resultado;
};

// Calcular Avaliação Final
export const calcularAvaliacaoFinal = (n1, n2) => {
  const md = calcularMediaDireta(n1, n2);
  
  const naf1 = 120 - md;
  const naf2 = (300 - 3 * n2) / 2;
  const naf3 = (300 - 2 * n1) / 3;
  
  const nafNecessaria = Math.min(naf1, naf2, naf3);
  
  return {
    md,
    nafNecessaria: Math.max(0, Math.min(100, nafNecessaria)),
    formula1: Math.max(0, Math.min(100, naf1)),
    formula2: Math.max(0, Math.min(100, naf2)),
    formula3: Math.max(0, Math.min(100, naf3)),
    melhorOpcao: nafNecessaria,
    podePassar: nafNecessaria <= 100,
  };
};
```

**services/calculators/frequencia.js:**
```javascript
export const calcularImpactoFalta = (disciplina) => {
  const frequenciaAtual = disciplina.percentual_carga_horaria_frequentada;
  const totalAulas = disciplina.carga_horaria_cumprida;
  const totalFaltas = disciplina.numero_faltas;
  const cargaTotal = disciplina.carga_horaria;
  const maximoFaltas = Math.ceil(cargaTotal * 0.25);
  
  if (totalAulas === 0) return null;
  
  const novaFrequencia = ((totalAulas - totalFaltas - 1) / totalAulas) * 100;
  const faltasRestantes = maximoFaltas - totalFaltas;
  
  let nivelRisco = 'baixo';
  if (faltasRestantes <= 3 && faltasRestantes > 0) {
    nivelRisco = 'medio';
  } else if (faltasRestantes <= 0) {
    nivelRisco = 'alto';
  }
  
  return {
    atual: frequenciaAtual,
    nova: Math.max(0, novaFrequencia),
    impacto: frequenciaAtual - novaFrequencia,
    faltasAtuais: totalFaltas,
    maximoFaltas,
    faltasRestantes: Math.max(0, faltasRestantes),
    nivelRisco,
    proporcaoFaltas: Math.min(100, (totalFaltas / maximoFaltas) * 100),
  };
};

export const podeFaltar = (disciplina) => {
  const impacto = calcularImpactoFalta(disciplina);
  
  if (!impacto) return 'danger';
  
  if (impacto.faltasRestantes > 3) {
    return 'success';
  } else if (impacto.faltasRestantes > 0) {
    return 'warning';
  } else {
    return 'danger';
  }
};
```

### 6. Exemplo de Tela de Login

**screens/Login.js:**
```javascript
import React from 'react';
import { View, TouchableOpacity, Text, StyleSheet } from 'react-native';
import { authenticateWithSUAP } from '../services/suap/auth';
import { getUserData } from '../services/suap/api';
import { saveUserData } from '../services/suap/storage';

export default function Login({ navigation }) {
  const handleLogin = async () => {
    try {
      // Autenticar
      await authenticateWithSUAP();
      
      // Buscar dados do usuário
      const userData = await getUserData();
      
      // Salvar localmente
      await saveUserData(userData);
      
      // Navegar para dashboard
      navigation.replace('Dashboard');
    } catch (error) {
      console.error('Erro no login:', error);
      alert('Erro ao fazer login. Tente novamente.');
    }
  };
  
  return (
    <View style={styles.container}>
      <Text style={styles.title}>SUPACO</Text>
      <Text style={styles.subtitle}>Sistema Útil Pra Aluno Cansado e Ocupado</Text>
      
      <TouchableOpacity style={styles.button} onPress={handleLogin}>
        <Text style={styles.buttonText}>Entrar com SUAP</Text>
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#000',
    padding: 20,
  },
  title: {
    fontSize: 48,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 10,
  },
  subtitle: {
    fontSize: 16,
    color: '#a1a1aa',
    marginBottom: 40,
    textAlign: 'center',
  },
  button: {
    backgroundColor: '#10b981',
    paddingVertical: 15,
    paddingHorizontal: 40,
    borderRadius: 10,
  },
  buttonText: {
    color: '#fff',
    fontSize: 18,
    fontWeight: '600',
  },
});
```

---

## Tratamento de Erros

### Erros Comuns

**1. Token Expirado (401 Unauthorized)**
```javascript
if (response.status === 401) {
  // Tentar renovar token
  await refreshAccessToken();
  // Tentar novamente
}
```

**2. Período Não Encontrado (404 Not Found)**
```javascript
if (response.status === 404) {
  // Tentar período anterior
  const periodoAnterior = getPeriodoAnterior();
  return await getBoletim(periodoAnterior.ano, periodoAnterior.periodo);
}
```

**3. Erro de Rede**
```javascript
try {
  const data = await getUserData();
} catch (error) {
  if (error.message.includes('Network')) {
    // Usar dados do cache
    const cachedData = await getCachedUserData();
    return cachedData;
  }
  throw error;
}
```

**4. Refresh Token Inválido**
```javascript
try {
  await refreshAccessToken();
} catch (error) {
  // Limpar tokens e redirecionar para login
  await clearTokens();
  navigation.replace('Login');
}
```

### Sistema de Retry

```javascript
const fetchWithRetry = async (fn, retries = 3, delay = 1000) => {
  try {
    return await fn();
  } catch (error) {
    if (retries > 0) {
      await new Promise(resolve => setTimeout(resolve, delay));
      return fetchWithRetry(fn, retries - 1, delay * 2);
    }
    throw error;
  }
};

// Uso
const userData = await fetchWithRetry(() => getUserData());
```

---

## Renovação de Token

### Implementação Automática

```javascript
import { getToken, saveToken } from './storage';

let refreshPromise = null;

export const refreshAccessToken = async () => {
  // Se já está renovando, aguardar
  if (refreshPromise) {
    return refreshPromise;
  }
  
  refreshPromise = (async () => {
    try {
      const refreshToken = await getToken('refresh_token');
      
      if (!refreshToken) {
        throw new Error('Refresh token não encontrado');
      }
      
      const response = await fetch('https://suap.ifrn.edu.br/o/token/', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          grant_type: 'refresh_token',
          refresh_token: refreshToken,
          client_id: CLIENT_ID,
          client_secret: CLIENT_SECRET,
        }).toString(),
      });
      
      if (!response.ok) {
        throw new Error('Falha ao renovar token');
      }
      
      const tokenData = await response.json();
      
      await saveToken('access_token', tokenData.access_token);
      if (tokenData.refresh_token) {
        await saveToken('refresh_token', tokenData.refresh_token);
      }
      await saveToken('token_expires', String(Date.now() + tokenData.expires_in * 1000));
      
      return tokenData;
    } finally {
      refreshPromise = null;
    }
  })();
  
  return refreshPromise;
};
```

### Renovação Preventiva

```javascript
// Renovar token se falta menos de 5 minutos para expirar
const ensureValidToken = async () => {
  const expiresAt = await getToken('token_expires');
  const fiveMinutes = 5 * 60 * 1000;
  
  if (Date.now() > (parseInt(expiresAt) - fiveMinutes)) {
    await refreshAccessToken();
  }
};
```

---

## Exemplos Práticos

### 1. Hook Customizado para Boletim

```javascript
import { useState, useEffect } from 'react';
import { getCurrentBoletim } from '../services/suap/api';
import { getBoletim as getCachedBoletim, saveBoletim } from '../services/suap/storage';

export const useBoletim = () => {
  const [boletim, setBoletim] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  
  useEffect(() => {
    loadBoletim();
  }, []);
  
  const loadBoletim = async () => {
    try {
      setLoading(true);
      
      // Tentar buscar da API
      const data = await getCurrentBoletim();
      setBoletim(data);
      
      // Salvar no cache
      await saveBoletim(data);
    } catch (err) {
      console.error('Erro ao carregar boletim:', err);
      
      // Usar cache em caso de erro
      const cached = await getCachedBoletim();
      if (cached) {
        setBoletim(cached);
      } else {
        setError(err.message);
      }
    } finally {
      setLoading(false);
    }
  };
  
  const refresh = () => {
    loadBoletim();
  };
  
  return { boletim, loading, error, refresh };
};
```

### 2. Componente de Disciplina

```javascript
import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { calcularNotaNecessaria } from '../services/calculators/notas';
import { calcularImpactoFalta } from '../services/calculators/frequencia';

export const DisciplinaCard = ({ disciplina }) => {
  const calculo = calcularNotaNecessaria(disciplina);
  const impactoFalta = calcularImpactoFalta(disciplina);
  
  return (
    <View style={styles.card}>
      <Text style={styles.nome}>{disciplina.disciplina}</Text>
      
      <View style={styles.notas}>
        <View style={styles.nota}>
          <Text style={styles.notaLabel}>N1</Text>
          <Text style={styles.notaValor}>
            {calculo.n1 !== null ? calculo.n1.toFixed(1) : '-'}
          </Text>
        </View>
        
        <View style={styles.nota}>
          <Text style={styles.notaLabel}>N2</Text>
          <Text style={styles.notaValor}>
            {calculo.n2 !== null ? calculo.n2.toFixed(1) : '-'}
          </Text>
        </View>
        
        <View style={styles.nota}>
          <Text style={styles.notaLabel}>Média</Text>
          <Text style={[
            styles.notaValor,
            calculo.mediaAtual >= 60 ? styles.aprovado : 
            calculo.mediaAtual >= 20 ? styles.final : styles.reprovado
          ]}>
            {calculo.mediaAtual !== null ? calculo.mediaAtual.toFixed(1) : '-'}
          </Text>
        </View>
      </View>
      
      {calculo.notaNecessaria !== null && (
        <Text style={styles.necessaria}>
          Precisa {calculo.notaNecessaria.toFixed(1)} no próximo bimestre
        </Text>
      )}
      
      {impactoFalta && (
        <View style={styles.frequencia}>
          <Text style={styles.frequenciaTexto}>
            Frequência: {impactoFalta.atual.toFixed(1)}%
          </Text>
          <Text style={styles.faltas}>
            {impactoFalta.faltasRestantes} faltas restantes
          </Text>
        </View>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  card: {
    backgroundColor: '#18181b',
    padding: 20,
    borderRadius: 12,
    marginBottom: 15,
    borderWidth: 1,
    borderColor: '#27272a',
  },
  nome: {
    fontSize: 16,
    fontWeight: '600',
    color: '#fff',
    marginBottom: 15,
  },
  notas: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    marginBottom: 15,
  },
  nota: {
    alignItems: 'center',
  },
  notaLabel: {
    fontSize: 12,
    color: '#a1a1aa',
    marginBottom: 5,
  },
  notaValor: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#fff',
  },
  aprovado: {
    color: '#10b981',
  },
  final: {
    color: '#3b82f6',
  },
  reprovado: {
    color: '#ef4444',
  },
  necessaria: {
    fontSize: 14,
    color: '#3b82f6',
    textAlign: 'center',
    marginBottom: 10,
  },
  frequencia: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingTop: 10,
    borderTopWidth: 1,
    borderTopColor: '#27272a',
  },
  frequenciaTexto: {
    fontSize: 14,
    color: '#a1a1aa',
  },
  faltas: {
    fontSize: 14,
    color: '#a1a1aa',
  },
});
```

### 3. Parser de Horários Completo

```javascript
export const parseHorarios = (horarioStr) => {
  if (!horarioStr) return [];
  
  const horarios = horarioStr.split('/').map(h => h.trim());
  const result = [];
  
  const horariosDetalhados = {
    M: {
      '1': '07:00 - 07:45',
      '2': '07:45 - 08:30',
      '3': '08:50 - 09:35',
      '4': '09:35 - 10:20',
      '5': '10:30 - 11:15',
      '6': '11:15 - 12:00',
    },
    V: {
      '1': '13:00 - 13:45',
      '2': '13:45 - 14:30',
      '3': '14:50 - 15:35',
      '4': '15:35 - 16:20',
      '5': '16:30 - 17:15',
      '6': '17:15 - 18:00',
    },
  };
  
  const diasSemana = {
    2: 'Segunda-feira',
    3: 'Terça-feira',
    4: 'Quarta-feira',
    5: 'Quinta-feira',
    6: 'Sexta-feira',
  };
  
  horarios.forEach(horario => {
    const match = horario.match(/^(\d)([MTV])(\d+)$/);
    if (match) {
      const dia = parseInt(match[1]);
      const turno = match[2];
      const aulas = match[3].split('');
      
      aulas.forEach(numeroAula => {
        result.push({
          dia,
          diaNome: diasSemana[dia],
          turno,
          numeroAula,
          horario: horariosDetalhados[turno]?.[numeroAula] || 'Não definido',
        });
      });
    }
  });
  
  return result;
};

// Uso
const disciplina = {
  sigla: 'POO',
  descricao: 'Programação Orientada a Objetos',
  horarios_de_aula: '2M12,4M34',
  locais_de_aula: ['Lab 01'],
};

const horarios = parseHorarios(disciplina.horarios_de_aula);
console.log(horarios);
/*
[
  { dia: 2, diaNome: 'Segunda-feira', turno: 'M', numeroAula: '1', horario: '07:00 - 07:45' },
  { dia: 2, diaNome: 'Segunda-feira', turno: 'M', numeroAula: '2', horario: '07:45 - 08:30' },
  { dia: 4, diaNome: 'Quarta-feira', turno: 'M', numeroAula: '3', horario: '08:50 - 09:35' },
  { dia: 4, diaNome: 'Quarta-feira', turno: 'M', numeroAula: '4', horario: '09:35 - 10:20' },
]
*/
```

---

## Dicas e Boas Práticas

### 1. Cache Offline

Sempre salve os dados localmente para permitir uso offline:

```javascript
const loadDataWithCache = async (apiCall, storageKey) => {
  try {
    // Buscar da API
    const data = await apiCall();
    
    // Salvar no cache
    await AsyncStorage.setItem(storageKey, JSON.stringify(data));
    
    return data;
  } catch (error) {
    // Em caso de erro, usar cache
    const cached = await AsyncStorage.getItem(storageKey);
    if (cached) {
      return JSON.parse(cached);
    }
    throw error;
  }
};
```

### 2. Loading States

Sempre mostre feedback visual ao usuário:

```javascript
const [loading, setLoading] = useState(false);
const [refreshing, setRefreshing] = useState(false);

const loadData = async () => {
  setLoading(true);
  try {
    const data = await getCurrentBoletim();
    setBoletim(data);
  } finally {
    setLoading(false);
  }
};
```

### 3. Pull to Refresh

```javascript
import { RefreshControl, ScrollView } from 'react-native';

<ScrollView
  refreshControl={
    <RefreshControl
      refreshing={refreshing}
      onRefresh={async () => {
        setRefreshing(true);
        await loadData();
        setRefreshing(false);
      }}
    />
  }
>
  {/* Conteúdo */}
</ScrollView>
```

### 4. Validação de Dados

Sempre valide os dados recebidos:

```javascript
const validateBoletim = (boletim) => {
  if (!Array.isArray(boletim)) {
    throw new Error('Boletim inválido');
  }
  
  return boletim.filter(disciplina => 
    disciplina.disciplina && 
    disciplina.carga_horaria
  );
};
```

### 5. Tratamento de Timeout

```javascript
const fetchWithTimeout = (url, options, timeout = 10000) => {
  return Promise.race([
    fetch(url, options),
    new Promise((_, reject) =>
      setTimeout(() => reject(new Error('Timeout')), timeout)
    ),
  ]);
};
```

---

## Checklist de Implementação

- [ ] Configurar aplicação no SUAP
- [ ] Implementar Deep Linking
- [ ] Criar serviço de autenticação OAuth2
- [ ] Implementar renovação automática de token
- [ ] Criar serviço de API com tratamento de erros
- [ ] Implementar cache offline com AsyncStorage
- [ ] Criar calculadoras de notas e frequência
- [ ] Implementar parser de horários
- [ ] Adicionar loading states e pull to refresh
- [ ] Tratar erros de rede
- [ ] Implementar logout
- [ ] Testar fluxo completo
- [ ] Adicionar testes unitários
- [ ] Documentar código

---

## Suporte e Recursos

**Documentação Oficial SUAP:**
- https://suap.ifrn.edu.br/api/docs/

**Repositório SUPACO:**
- https://github.com/kellyson71/SUAP

**Contato:**
- Email: Kellyson.medeiros.pdf@gmail.com
- GitHub: @Kellyson71

---

**Última Atualização:** Outubro 2024
**Versão:** 2.6.0


