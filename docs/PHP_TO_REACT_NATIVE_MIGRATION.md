# Guia de Migração: PHP para React Native

## Comparação Lado a Lado

Este guia mostra como migrar o código atual do SUPACO (PHP) para React Native.

---

## 1. Autenticação OAuth

### PHP (login.php + callback.php)

**login.php:**
```php
<?php
$auth_url = SUAP_URL . "/o/authorize/?" . http_build_query([
    'response_type' => 'code',
    'client_id' => SUAP_CLIENT_ID,
    'redirect_uri' => REDIRECT_URI
]);

header("Location: " . $auth_url);
exit;
?>
```

**callback.php:**
```php
<?php
// Receber código
$code = $_GET['code'];

// Trocar por token
$token_request = [
    'grant_type' => 'authorization_code',
    'code' => $code,
    'client_id' => SUAP_CLIENT_ID,
    'client_secret' => SUAP_CLIENT_SECRET,
    'redirect_uri' => REDIRECT_URI
];

$ch = curl_init(SUAP_URL . "/o/token/");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($token_request)
]);

$response = curl_exec($ch);
$token_data = json_decode($response, true);
curl_close($ch);

// Salvar na sessão
$_SESSION['access_token'] = $token_data['access_token'];
$_SESSION['refresh_token'] = $token_data['refresh_token'];
$_SESSION['access_token_expires'] = time() + $token_data['expires_in'];
?>
```

### React Native

**services/auth.js:**
```javascript
import * as WebBrowser from 'expo-web-browser';
import AsyncStorage from '@react-native-async-storage/async-storage';

const SUAP_URL = 'https://suap.ifrn.edu.br';
const CLIENT_ID = 'seu_client_id';
const CLIENT_SECRET = 'seu_client_secret';
const REDIRECT_URI = 'supaco://callback';

export const login = async () => {
  // 1. Abrir navegador para autorização
  const authUrl = `${SUAP_URL}/o/authorize/?response_type=code&client_id=${CLIENT_ID}&redirect_uri=${REDIRECT_URI}`;
  const result = await WebBrowser.openAuthSessionAsync(authUrl, REDIRECT_URI);
  
  if (result.type === 'success') {
    // 2. Extrair código
    const code = extractCodeFromUrl(result.url);
    
    // 3. Trocar código por token
    const response = await fetch(`${SUAP_URL}/o/token/`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        grant_type: 'authorization_code',
        code,
        client_id: CLIENT_ID,
        client_secret: CLIENT_SECRET,
        redirect_uri: REDIRECT_URI,
      }).toString(),
    });
    
    const tokenData = await response.json();
    
    // 4. Salvar no AsyncStorage
    await AsyncStorage.multiSet([
      ['access_token', tokenData.access_token],
      ['refresh_token', tokenData.refresh_token],
      ['token_expires', String(Date.now() + tokenData.expires_in * 1000)],
    ]);
    
    return tokenData;
  }
  
  throw new Error('Login cancelado');
};

const extractCodeFromUrl = (url) => {
  const match = url.match(/code=([^&]+)/);
  return match ? match[1] : null;
};
```

---

## 2. Buscar Dados da API

### PHP (index.php)

```php
<?php
function getSuapData($endpoint) {
    $ch = curl_init(SUAP_URL . "/api/v2/" . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $_SESSION['access_token']
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Usar
$meusDados = getSuapData("minhas-informacoes/meus-dados/");
$boletim = getSuapData("minhas-informacoes/boletim/2024/1/");
$horarios = getSuapData("minhas-informacoes/turmas-virtuais/2024/1/");
?>
```

### React Native

**services/api.js:**
```javascript
import AsyncStorage from '@react-native-async-storage/async-storage';

const SUAP_URL = 'https://suap.ifrn.edu.br';

const authenticatedFetch = async (endpoint) => {
  const accessToken = await AsyncStorage.getItem('access_token');
  
  const response = await fetch(`${SUAP_URL}${endpoint}`, {
    headers: {
      'Authorization': `Bearer ${accessToken}`,
      'Content-Type': 'application/json',
    },
  });
  
  if (!response.ok) {
    throw new Error(`Erro ${response.status}`);
  }
  
  return await response.json();
};

// Usar
export const getUserData = () => 
  authenticatedFetch('/api/v2/minhas-informacoes/meus-dados/');

export const getBoletim = (ano, periodo) => 
  authenticatedFetch(`/api/v2/minhas-informacoes/boletim/${ano}/${periodo}/`);

export const getHorarios = (ano, periodo) => 
  authenticatedFetch(`/api/v2/minhas-informacoes/turmas-virtuais/${ano}/${periodo}/`);
```

**Uso em Componente:**
```javascript
import { useState, useEffect } from 'react';
import { getUserData, getBoletim } from './services/api';

export default function Dashboard() {
  const [userData, setUserData] = useState(null);
  const [boletim, setBoletim] = useState([]);
  const [loading, setLoading] = useState(true);
  
  useEffect(() => {
    loadData();
  }, []);
  
  const loadData = async () => {
    try {
      const [user, grades] = await Promise.all([
        getUserData(),
        getBoletim(2024, 1),
      ]);
      
      setUserData(user);
      setBoletim(grades);
    } catch (error) {
      console.error('Erro:', error);
    } finally {
      setLoading(false);
    }
  };
  
  if (loading) return <Loading />;
  
  return (
    <View>
      <Text>{userData.nome_usual}</Text>
      {boletim.map(disciplina => (
        <DisciplinaCard key={disciplina.codigo_diario} data={disciplina} />
      ))}
    </View>
  );
}
```

---

## 3. Cálculo de Notas

### PHP (index.php)

```php
<?php
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
    } elseif ($n1 !== null && $n2 === null) {
        $nota_necessaria = (300 - 2 * $n1) / 3;
        $resultado['nota_necessaria'] = max(0, min(100, $nota_necessaria));
        $resultado['situacao'] = 'aguardando_n2';
    }
    
    return $resultado;
}

function calcularAvaliacaoFinal($n1, $n2) {
    $md = (2 * $n1 + 3 * $n2) / 5;
    
    $naf1 = 120 - $md;
    $naf2 = (300 - 3 * $n2) / 2;
    $naf3 = (300 - 2 * $n1) / 3;
    
    $naf_necessaria = min($naf1, $naf2, $naf3);
    
    return [
        'md' => $md,
        'naf_necessaria' => max(0, min(100, $naf_necessaria)),
        'pode_passar' => $naf_necessaria <= 100
    ];
}
?>
```

### React Native

**services/calculators/notas.js:**
```javascript
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
  
  if (n1 !== null && n2 !== null) {
    const md = (2 * n1 + 3 * n2) / 5;
    resultado.mediaAtual = md;
    
    if (md >= 60) {
      resultado.situacao = 'aprovado_direto';
      resultado.jaAprovado = true;
    } else if (md >= 20) {
      resultado.situacao = 'avaliacao_final';
      resultado.precisaAF = true;
    } else {
      resultado.situacao = 'reprovado_nota';
      resultado.jaReprovado = true;
    }
  } else if (n1 !== null && n2 === null) {
    const notaNecessaria = (300 - 2 * n1) / 3;
    resultado.notaNecessaria = Math.max(0, Math.min(100, notaNecessaria));
    resultado.situacao = 'aguardando_n2';
  }
  
  return resultado;
};

export const calcularAvaliacaoFinal = (n1, n2) => {
  const md = (2 * n1 + 3 * n2) / 5;
  
  const naf1 = 120 - md;
  const naf2 = (300 - 3 * n2) / 2;
  const naf3 = (300 - 2 * n1) / 3;
  
  const nafNecessaria = Math.min(naf1, naf2, naf3);
  
  return {
    md,
    nafNecessaria: Math.max(0, Math.min(100, nafNecessaria)),
    podePassar: nafNecessaria <= 100,
  };
};
```

---

## 4. Cálculo de Frequência

### PHP (index.php)

```php
<?php
function calcularImpactoFalta($disciplina) {
    $frequenciaAtual = $disciplina['percentual_carga_horaria_frequentada'];
    $totalAulas = $disciplina['carga_horaria_cumprida'];
    $totalFaltas = $disciplina['numero_faltas'];
    $cargaTotal = $disciplina['carga_horaria'];
    $maximoFaltas = ceil($cargaTotal * 0.25);
    
    if ($totalAulas == 0) return null;
    
    $novaFrequencia = (($totalAulas - $totalFaltas - 1) / $totalAulas) * 100;
    $faltasRestantes = $maximoFaltas - $totalFaltas;
    
    $nivelRisco = 'baixo';
    if ($faltasRestantes <= 3 && $faltasRestantes > 0) {
        $nivelRisco = 'medio';
    } else if ($faltasRestantes <= 0) {
        $nivelRisco = 'alto';
    }
    
    return [
        'atual' => $frequenciaAtual,
        'nova' => max(0, $novaFrequencia),
        'impacto' => $frequenciaAtual - $novaFrequencia,
        'faltas_atuais' => $totalFaltas,
        'maximo_faltas' => $maximoFaltas,
        'faltas_restantes' => max(0, $faltasRestantes),
        'nivel_risco' => $nivelRisco,
        'proporcao_faltas' => min(100, ($totalFaltas / $maximoFaltas) * 100)
    ];
}

function podeFaltarAmanha($disciplina) {
    $frequenciaAtual = $disciplina['percentual_carga_horaria_frequentada'];
    $totalFaltas = $disciplina['numero_faltas'];
    $maximoFaltas = ceil($disciplina['carga_horaria'] * 0.25);
    
    if (($maximoFaltas - $totalFaltas) <= 3 && ($maximoFaltas - $totalFaltas) > 0) {
        return 'warning';
    }
    
    if (($maximoFaltas - $totalFaltas) > 3) {
        return 'success';
    }
    
    return 'danger';
}
?>
```

### React Native

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
  const totalFaltas = disciplina.numero_faltas;
  const maximoFaltas = Math.ceil(disciplina.carga_horaria * 0.25);
  const faltasRestantes = maximoFaltas - totalFaltas;
  
  if (faltasRestantes > 3) return 'success';
  if (faltasRestantes > 0) return 'warning';
  return 'danger';
};
```

---

## 5. Parser de Horários

### PHP (horarios.php)

```php
<?php
function parseHorario($horarioStr) {
    if (empty($horarioStr)) {
        return [];
    }
    
    $horarios = preg_split('/\s*\/\s*/', $horarioStr);
    $result = [];
    
    foreach ($horarios as $horario) {
        $horario = trim($horario);
        
        if (preg_match('/^(\d)([MTV])(\d+)$/', $horario, $matches)) {
            $result[] = [
                'dia' => (int)$matches[1],
                'turno' => $matches[2],
                'aulas' => str_split($matches[3])
            ];
        }
    }
    
    return $result;
}

function ordenarAulasPorHorario($aulasAmanha) {
    $horarios = [
        'M1' => ['turno' => 'M', 'aula' => '1', 'hora' => '07:00 - 07:45'],
        'M2' => ['turno' => 'M', 'aula' => '2', 'hora' => '07:45 - 08:30'],
        'M3' => ['turno' => 'M', 'aula' => '3', 'hora' => '08:50 - 09:35'],
        'M4' => ['turno' => 'M', 'aula' => '4', 'hora' => '09:35 - 10:20'],
        'M5' => ['turno' => 'M', 'aula' => '5', 'hora' => '10:30 - 11:15'],
        'M6' => ['turno' => 'M', 'aula' => '6', 'hora' => '11:15 - 12:00'],
        'V1' => ['turno' => 'V', 'aula' => '1', 'hora' => '13:00 - 13:45'],
        'V2' => ['turno' => 'V', 'aula' => '2', 'hora' => '13:45 - 14:30'],
        'V3' => ['turno' => 'V', 'aula' => '3', 'hora' => '14:50 - 15:35'],
        'V4' => ['turno' => 'V', 'aula' => '4', 'hora' => '15:35 - 16:20'],
        'V5' => ['turno' => 'V', 'aula' => '5', 'hora' => '16:30 - 17:15'],
        'V6' => ['turno' => 'V', 'aula' => '6', 'hora' => '17:15 - 18:00']
    ];
    
    $aulasOrdenadas = [];
    
    foreach ($aulasAmanha as $aula) {
        if (isset($aula['horario']['aulas'])) {
            foreach ($aula['horario']['aulas'] as $numeroAula) {
                $chave = $aula['horario']['turno'] . $numeroAula;
                if (isset($horarios[$chave])) {
                    $aulaCompleta = $aula;
                    $aulaCompleta['horario_detalhado'] = $horarios[$chave]['hora'];
                    $aulaCompleta['ordem'] = $chave;
                    $aulasOrdenadas[] = $aulaCompleta;
                }
            }
        }
    }
    
    usort($aulasOrdenadas, function ($a, $b) {
        return strcmp($a['ordem'], $b['ordem']);
    });
    
    return $aulasOrdenadas;
}
?>
```

### React Native

**services/utils/horarios.js:**
```javascript
const HORARIOS_DETALHADOS = {
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

const DIAS_SEMANA = {
  2: 'Segunda-feira',
  3: 'Terça-feira',
  4: 'Quarta-feira',
  5: 'Quinta-feira',
  6: 'Sexta-feira',
};

export const parseHorario = (horarioStr) => {
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

export const expandirHorarios = (horarioStr) => {
  const parsed = parseHorario(horarioStr);
  const expandidos = [];
  
  parsed.forEach(({ dia, turno, aulas }) => {
    aulas.forEach(numeroAula => {
      expandidos.push({
        dia,
        diaNome: DIAS_SEMANA[dia],
        turno,
        numeroAula,
        horario: HORARIOS_DETALHADOS[turno]?.[numeroAula] || 'Não definido',
        ordem: `${turno}${numeroAula}`,
      });
    });
  });
  
  // Ordenar
  expandidos.sort((a, b) => a.ordem.localeCompare(b.ordem));
  
  return expandidos;
};

export const getProximaAula = (horarios) => {
  const now = new Date();
  const diaAtual = now.getDay(); // 0 = Domingo, 1 = Segunda...
  const horaAtual = now.getHours() * 60 + now.getMinutes();
  
  // Converter dia da semana (0-6) para formato SUAP (2-6)
  const diaSuap = diaAtual === 0 ? null : diaAtual + 1;
  
  // ... implementar lógica similar ao PHP
  
  return {
    tipo: 'hoje' | 'amanha' | 'futuro' | 'nenhuma',
    diaNome: 'Segunda-feira',
    aulas: [],
  };
};
```

---

## 6. Renovação de Token

### PHP (index.php)

```php
<?php
if ($_SESSION['access_token_expires'] - time() < 300 && isset($_SESSION['refresh_token'])) {
    $refresh_request = [
        'grant_type' => 'refresh_token',
        'refresh_token' => $_SESSION['refresh_token'],
        'client_id' => SUAP_CLIENT_ID,
        'client_secret' => SUAP_CLIENT_SECRET
    ];
    
    $ch = curl_init(SUAP_URL . "/o/token/");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($refresh_request)
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $token_data = json_decode($response, true);
    
    if ($token_data && isset($token_data['access_token'])) {
        $_SESSION['access_token'] = $token_data['access_token'];
        $_SESSION['access_token_expires'] = time() + $token_data['expires_in'];
        
        if (isset($token_data['refresh_token'])) {
            $_SESSION['refresh_token'] = $token_data['refresh_token'];
        }
    }
}
?>
```

### React Native

**services/auth.js:**
```javascript
import AsyncStorage from '@react-native-async-storage/async-storage';

let refreshPromise = null;

export const refreshAccessToken = async () => {
  // Evitar múltiplas renovações simultâneas
  if (refreshPromise) {
    return refreshPromise;
  }
  
  refreshPromise = (async () => {
    try {
      const refreshToken = await AsyncStorage.getItem('refresh_token');
      
      if (!refreshToken) {
        throw new Error('Refresh token não encontrado');
      }
      
      const response = await fetch('https://suap.ifrn.edu.br/o/token/', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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
      
      await AsyncStorage.multiSet([
        ['access_token', tokenData.access_token],
        ['refresh_token', tokenData.refresh_token || refreshToken],
        ['token_expires', String(Date.now() + tokenData.expires_in * 1000)],
      ]);
      
      return tokenData;
    } finally {
      refreshPromise = null;
    }
  })();
  
  return refreshPromise;
};

export const ensureValidToken = async () => {
  const expiresAt = await AsyncStorage.getItem('token_expires');
  const fiveMinutes = 5 * 60 * 1000;
  
  if (Date.now() > (parseInt(expiresAt) - fiveMinutes)) {
    await refreshAccessToken();
  }
};
```

---

## 7. Detecção de Período Atual

### PHP (index.php)

```php
<?php
function detectarPeriodoMaisRecente() {
    $anoAtual = date('Y');
    $mesAtual = date('n');
    
    $periodoAtual = ($mesAtual <= 6) ? 1 : 2;
    
    $periodosParaTentar = [];
    $periodosParaTentar[] = [$anoAtual, $periodoAtual];
    
    if ($periodoAtual == 1) {
        $periodosParaTentar[] = [$anoAtual - 1, 2];
    }
    
    for ($i = 1; $i <= 3; $i++) {
        $ano = $anoAtual - $i;
        $periodosParaTentar[] = [$ano, 2];
        $periodosParaTentar[] = [$ano, 1];
    }
    
    return $periodosParaTentar;
}

function carregarDadosPeriodo($ano, $periodo) {
    $boletim = getSuapData("minhas-informacoes/boletim/{$ano}/{$periodo}/");
    $horarios = getSuapData("minhas-informacoes/turmas-virtuais/{$ano}/{$periodo}/");
    
    return [
        'boletim' => is_array($boletim) ? $boletim : [],
        'horarios' => is_array($horarios) ? $horarios : [],
        'sucesso' => !empty($boletim) && is_array($boletim)
    ];
}
?>
```

### React Native

**services/utils/periodo.js:**
```javascript
export const detectarPeriodoAtual = () => {
  const now = new Date();
  const ano = now.getFullYear();
  const mes = now.getMonth() + 1; // getMonth() retorna 0-11
  
  return {
    ano,
    periodo: mes <= 6 ? 1 : 2,
  };
};

export const getPeriodosParaTentar = () => {
  const { ano, periodo } = detectarPeriodoAtual();
  const periodos = [];
  
  // Período atual
  periodos.push({ ano, periodo });
  
  // Se primeiro semestre, adicionar segundo do ano anterior
  if (periodo === 1) {
    periodos.push({ ano: ano - 1, periodo: 2 });
  }
  
  // Últimos 3 anos
  for (let i = 1; i <= 3; i++) {
    const anoAnterior = ano - i;
    periodos.push({ ano: anoAnterior, periodo: 2 });
    periodos.push({ ano: anoAnterior, periodo: 1 });
  }
  
  return periodos;
};

export const carregarPeriodoComFallback = async (getBoletim, getHorarios) => {
  const periodos = getPeriodosParaTentar();
  
  for (const { ano, periodo } of periodos) {
    try {
      const [boletim, horarios] = await Promise.all([
        getBoletim(ano, periodo),
        getHorarios(ano, periodo),
      ]);
      
      if (boletim && boletim.length > 0) {
        return { boletim, horarios, ano, periodo };
      }
    } catch (error) {
      console.log(`Período ${ano}.${periodo} não encontrado, tentando próximo...`);
      continue;
    }
  }
  
  throw new Error('Nenhum período com dados encontrado');
};
```

---

## 8. Cache Offline

### PHP (callback.php)

```php
<?php
// Salva dados no cache após login
echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof AppCacheManager !== "undefined") {
            const appData = {
                meusDados: ' . json_encode($user_data) . ',
                timestamp: Date.now(),
                version: "1.0.0",
                isBasic: false
            };
            
            AppCacheManager.saveAppData(appData);
        }
    });
</script>';
?>
```

### React Native

**services/cache.js:**
```javascript
import AsyncStorage from '@react-native-async-storage/async-storage';

const CACHE_KEYS = {
  USER_DATA: '@supaco:user_data',
  BOLETIM: '@supaco:boletim',
  HORARIOS: '@supaco:horarios',
  LAST_UPDATE: '@supaco:last_update',
};

export const saveToCache = async (key, data) => {
  try {
    await AsyncStorage.setItem(key, JSON.stringify({
      data,
      timestamp: Date.now(),
    }));
  } catch (error) {
    console.error('Erro ao salvar cache:', error);
  }
};

export const getFromCache = async (key, maxAge = 24 * 60 * 60 * 1000) => {
  try {
    const cached = await AsyncStorage.getItem(key);
    
    if (!cached) return null;
    
    const { data, timestamp } = JSON.parse(cached);
    
    // Verificar se ainda é válido
    if (Date.now() - timestamp > maxAge) {
      return null;
    }
    
    return data;
  } catch (error) {
    console.error('Erro ao ler cache:', error);
    return null;
  }
};

export const clearCache = async () => {
  try {
    await AsyncStorage.multiRemove(Object.values(CACHE_KEYS));
  } catch (error) {
    console.error('Erro ao limpar cache:', error);
  }
};

// Hook para usar cache com API
import { useState, useEffect } from 'react';

export const useDataWithCache = (fetchFn, cacheKey) => {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [fromCache, setFromCache] = useState(false);
  
  useEffect(() => {
    loadData();
  }, []);
  
  const loadData = async () => {
    try {
      // Tentar cache primeiro
      const cached = await getFromCache(cacheKey);
      
      if (cached) {
        setData(cached);
        setFromCache(true);
        setLoading(false);
      }
      
      // Buscar dados atualizados
      const fresh = await fetchFn();
      setData(fresh);
      setFromCache(false);
      
      // Salvar no cache
      await saveToCache(cacheKey, fresh);
    } catch (error) {
      console.error('Erro ao carregar dados:', error);
      
      // Se falhou, usar cache mesmo se antigo
      const cached = await AsyncStorage.getItem(cacheKey);
      if (cached) {
        setData(JSON.parse(cached).data);
        setFromCache(true);
      }
    } finally {
      setLoading(false);
    }
  };
  
  return { data, loading, fromCache, refresh: loadData };
};
```

**Uso:**
```javascript
import { useDataWithCache } from './services/cache';
import { getBoletim } from './services/api';

export default function Boletim() {
  const { data: boletim, loading, fromCache, refresh } = useDataWithCache(
    () => getBoletim(2024, 1),
    '@supaco:boletim_2024_1'
  );
  
  return (
    <View>
      {fromCache && <Text style={styles.cacheWarning}>Dados do cache</Text>}
      <RefreshControl refreshing={loading} onRefresh={refresh} />
      {/* Renderizar boletim */}
    </View>
  );
}
```

---

## Resumo das Diferenças Principais

| Aspecto | PHP | React Native |
|---------|-----|--------------|
| **Armazenamento** | `$_SESSION` | `AsyncStorage` |
| **HTTP Requests** | `curl_*` | `fetch()` |
| **Autenticação** | Redirect com header() | WebBrowser.openAuthSessionAsync() |
| **Arrays** | `array()`, `[]` | `[]` sempre |
| **Objetos** | Arrays associativos | Objetos literais |
| **Null Check** | `isset()`, `empty()` | `?.`, `??` |
| **JSON** | `json_encode()`, `json_decode()` | `JSON.stringify()`, `JSON.parse()` |
| **Loops** | `foreach`, `for` | `map()`, `forEach()`, `for...of` |
| **Funções** | `function nome() {}` | `const nome = () => {}` |
| **Assíncrono** | Síncrono (curl) | `async/await` |
| **Cache** | LocalStorage JS | AsyncStorage |

---

**Última Atualização:** Outubro 2024
**Versão:** 2.6.0

