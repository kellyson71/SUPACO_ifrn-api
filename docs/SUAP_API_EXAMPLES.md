# Exemplos de Respostas da API SUAP

## Sumário
1. [Respostas Completas da API](#respostas-completas-da-api)
2. [Casos de Uso Específicos](#casos-de-uso-específicos)
3. [Variações de Dados](#variações-de-dados)
4. [Cenários de Erro](#cenários-de-erro)

---

## Respostas Completas da API

### 1. Meus Dados (Usuário Completo)

**Request:**
```http
GET /api/v2/minhas-informacoes/meus-dados/
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Response (200 OK):**
```json
{
  "id": 123456,
  "matricula": "20241234567",
  "nome_usual": "João Silva",
  "cpf": "123.456.789-00",
  "rg": "1234567",
  "filiacao": "Maria Silva e José Silva",
  "data_nascimento": "2005-03-15",
  "naturalidade": "Natal",
  "tipo_sanguineo": "O+",
  "email": "joao.silva@escolar.ifrn.edu.br",
  "url_foto_75x100": "https://suap.ifrn.edu.br/media/fotos/12345_75x100.jpg",
  "url_foto_150x200": "https://suap.ifrn.edu.br/media/fotos/12345_150x200.jpg",
  "tipo_usuario": "Aluno",
  "vinculo": {
    "matricula": "20241234567",
    "nome": "João Silva dos Santos",
    "curso": "Tecnologia em Análise e Desenvolvimento de Sistemas",
    "campus": "Campus Natal - Central",
    "codigoCurso": "987654",
    "modalidade": "Integrado",
    "situacao": "Ativo",
    "cota_sistec": null,
    "cota_mec": null,
    "situacao_sistemica": "Matriculado",
    "linha_pesquisa": null,
    "curriculo_lattes": null
  }
}
```

### 2. Boletim - Sistema IF (2 Bimestres)

**Request:**
```http
GET /api/v2/minhas-informacoes/boletim/2024/1/
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Response (200 OK):**
```json
[
  {
    "disciplina": "TEC.0010 - Programação Orientada a Objetos",
    "codigo_diario": "123456",
    "ano_letivo": 2024,
    "periodo_letivo": 1,
    "carga_horaria": 80,
    "aulas": 40,
    "faltas": 0,
    "carga_horaria_cumprida": 40,
    "numero_faltas": 2,
    "percentual_carga_horaria_frequentada": 97.5,
    "situacao": "Cursando",
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
    "media_disciplina": null,
    "media_final_disciplina": null
  },
  {
    "disciplina": "TEC.0011 - Banco de Dados",
    "codigo_diario": "123457",
    "ano_letivo": 2024,
    "periodo_letivo": 1,
    "carga_horaria": 80,
    "aulas": 40,
    "faltas": 0,
    "carga_horaria_cumprida": 40,
    "numero_faltas": 5,
    "percentual_carga_horaria_frequentada": 87.5,
    "situacao": "Cursando",
    "nota_etapa_1": {
      "nota": 70.0,
      "numero_avaliacoes": 2
    },
    "nota_etapa_2": {
      "nota": 55.0,
      "numero_avaliacoes": 2
    },
    "nota_etapa_3": {
      "nota": null,
      "numero_avaliacoes": 0
    },
    "nota_etapa_4": {
      "nota": null,
      "numero_avaliacoes": 0
    },
    "media_disciplina": null,
    "media_final_disciplina": null
  },
  {
    "disciplina": "TEC.0012 - Redes de Computadores",
    "codigo_diario": "123458",
    "ano_letivo": 2024,
    "periodo_letivo": 1,
    "carga_horaria": 60,
    "aulas": 30,
    "faltas": 0,
    "carga_horaria_cumprida": 30,
    "numero_faltas": 15,
    "percentual_carga_horaria_frequentada": 50.0,
    "situacao": "Reprovado por Falta",
    "nota_etapa_1": {
      "nota": 40.0,
      "numero_avaliacoes": 1
    },
    "nota_etapa_2": {
      "nota": 10.0,
      "numero_avaliacoes": 1
    },
    "nota_etapa_3": {
      "nota": null,
      "numero_avaliacoes": 0
    },
    "nota_etapa_4": {
      "nota": null,
      "numero_avaliacoes": 0
    },
    "media_disciplina": null,
    "media_final_disciplina": 18.0
  }
]
```

### 3. Boletim - Sistema Tradicional (4 Bimestres)

Algumas instituições ainda usam 4 bimestres com estrutura diferente:

```json
[
  {
    "disciplina": "MAT.0001 - Matemática I",
    "codigo_diario": "123459",
    "ano_letivo": 2024,
    "periodo_letivo": 1,
    "carga_horaria": 120,
    "carga_horaria_cumprida": 60,
    "numero_faltas": 3,
    "percentual_carga_horaria_frequentada": 95.0,
    "situacao": "Cursando",
    "primeiro_semestre": {
      "nota_etapa_1": {
        "nota": 75.0,
        "numero_avaliacoes": 2
      },
      "nota_etapa_2": {
        "nota": 80.0,
        "numero_avaliacoes": 2
      },
      "media_etapa_1_2": 77.5
    },
    "segundo_semestre": {
      "nota_etapa_3": {
        "nota": null,
        "numero_avaliacoes": 0
      },
      "nota_etapa_4": {
        "nota": null,
        "numero_avaliacoes": 0
      },
      "media_etapa_3_4": null
    },
    "nota_etapa_1": {
      "nota": 75.0,
      "numero_avaliacoes": 2
    },
    "nota_etapa_2": {
      "nota": 80.0,
      "numero_avaliacoes": 2
    },
    "nota_etapa_3": {
      "nota": null,
      "numero_avaliacoes": 0
    },
    "nota_etapa_4": {
      "nota": null,
      "numero_avaliacoes": 0
    },
    "media_disciplina": null,
    "media_final_disciplina": null
  }
]
```

### 4. Horários (Turmas Virtuais)

**Request:**
```http
GET /api/v2/minhas-informacoes/turmas-virtuais/2024/1/
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Response (200 OK):**
```json
[
  {
    "id": 123456,
    "ano_letivo": 2024,
    "periodo_letivo": 1,
    "codigo": "TEC.0010",
    "sigla": "POO",
    "descricao": "Programação Orientada a Objetos",
    "disciplina": "Programação Orientada a Objetos",
    "periodo_matriz": 3,
    "horarios_de_aula": "2M12,4M34",
    "locais_de_aula": ["Lab 01"],
    "qtd_avaliacoes": 2,
    "codigo_turma": "20241.1.TEC.0010.1A",
    "professores": [
      {
        "nome": "Prof. Carlos Silva",
        "email": "carlos.silva@ifrn.edu.br"
      }
    ]
  },
  {
    "id": 123457,
    "ano_letivo": 2024,
    "periodo_letivo": 1,
    "codigo": "TEC.0011",
    "sigla": "BD",
    "descricao": "Banco de Dados",
    "disciplina": "Banco de Dados",
    "periodo_matriz": 3,
    "horarios_de_aula": "3V12 / 5V34",
    "locais_de_aula": ["Lab 03"],
    "qtd_avaliacoes": 2,
    "codigo_turma": "20241.1.TEC.0011.1A",
    "professores": [
      {
        "nome": "Prof. Ana Santos",
        "email": "ana.santos@ifrn.edu.br"
      }
    ]
  },
  {
    "id": 123458,
    "ano_letivo": 2024,
    "periodo_letivo": 1,
    "codigo": "TEC.0012",
    "sigla": "REDES",
    "descricao": "Redes de Computadores",
    "disciplina": "Redes de Computadores",
    "periodo_matriz": 3,
    "horarios_de_aula": "2V56,3M12",
    "locais_de_aula": ["Lab 02", "Sala 205"],
    "qtd_avaliacoes": 2,
    "codigo_turma": "20241.1.TEC.0012.1A",
    "professores": [
      {
        "nome": "Prof. Roberto Lima",
        "email": "roberto.lima@ifrn.edu.br"
      }
    ]
  }
]
```

---

## Casos de Uso Específicos

### Caso 1: Aluno Aprovado Direto

**Disciplina:**
```json
{
  "disciplina": "TEC.0010 - Programação Orientada a Objetos",
  "nota_etapa_1": { "nota": 85.0 },
  "nota_etapa_2": { "nota": 90.0 }
}
```

**Cálculo:**
```javascript
MD = (2×85 + 3×90) ÷ 5 = (170 + 270) ÷ 5 = 88.0
Status: APROVADO DIRETO (MD >= 60)
```

### Caso 2: Aluno em Avaliação Final

**Disciplina:**
```json
{
  "disciplina": "TEC.0011 - Banco de Dados",
  "nota_etapa_1": { "nota": 70.0 },
  "nota_etapa_2": { "nota": 55.0 }
}
```

**Cálculo:**
```javascript
MD = (2×70 + 3×55) ÷ 5 = (140 + 165) ÷ 5 = 61.0

// Mas vamos considerar MD = 59.0 para exemplo de AF
MD = 59.0
Status: AVALIAÇÃO FINAL (20 <= MD < 60)

// Cálculo da NAF necessária (3 fórmulas):
NAF1 = 120 - 59 = 61.0
NAF2 = (300 - 3×55) ÷ 2 = (300 - 165) ÷ 2 = 67.5
NAF3 = (300 - 2×70) ÷ 3 = (300 - 140) ÷ 3 = 53.3

NAF Necessária = min(61.0, 67.5, 53.3) = 53.3
```

### Caso 3: Aluno Reprovado por Nota

**Disciplina:**
```json
{
  "disciplina": "TEC.0012 - Redes de Computadores",
  "nota_etapa_1": { "nota": 40.0 },
  "nota_etapa_2": { "nota": 10.0 }
}
```

**Cálculo:**
```javascript
MD = (2×40 + 3×10) ÷ 5 = (80 + 30) ÷ 5 = 22.0

// Vamos considerar MD = 18.0 para exemplo
MD = 18.0
Status: REPROVADO POR NOTA (MD < 20)
```

### Caso 4: Aguardando N2

**Disciplina:**
```json
{
  "disciplina": "TEC.0013 - Desenvolvimento Web",
  "nota_etapa_1": { "nota": 75.0 },
  "nota_etapa_2": { "nota": null }
}
```

**Cálculo:**
```javascript
N2_necessário = (300 - 2×75) ÷ 3 = (300 - 150) ÷ 3 = 50.0

Mensagem: "Precisa 50.0 no N2 para aprovação direta"
```

### Caso 5: Aluno no Limite de Faltas

**Disciplina:**
```json
{
  "disciplina": "TEC.0014 - Sistemas Operacionais",
  "carga_horaria": 80,
  "carga_horaria_cumprida": 40,
  "numero_faltas": 18,
  "percentual_carga_horaria_frequentada": 77.5
}
```

**Cálculo de Faltas:**
```javascript
const cargaTotal = 80;
const maximoFaltas = Math.ceil(cargaTotal * 0.25); // 20 faltas
const faltasAtuais = 18;
const faltasRestantes = maximoFaltas - faltasAtuais; // 2 faltas

Status: CUIDADO (faltasRestantes <= 3)
Mensagem: "Você só pode faltar mais 2 vezes!"
```

### Caso 6: Múltiplos Horários no Mesmo Dia

**Disciplina:**
```json
{
  "sigla": "EDF",
  "descricao": "Educação Física",
  "horarios_de_aula": "2M56 / 2V12",
  "locais_de_aula": ["Quadra Poliesportiva"]
}
```

**Parsing:**
```javascript
const horarios = parseHorarios("2M56 / 2V12");
/*
[
  { dia: 2, diaNome: 'Segunda-feira', turno: 'M', numeroAula: '5', horario: '10:30 - 11:15' },
  { dia: 2, diaNome: 'Segunda-feira', turno: 'M', numeroAula: '6', horario: '11:15 - 12:00' },
  { dia: 2, diaNome: 'Segunda-feira', turno: 'V', numeroAula: '1', horario: '13:00 - 13:45' },
  { dia: 2, diaNome: 'Segunda-feira', turno: 'V', numeroAula: '2', horario: '13:45 - 14:30' }
]
*/
```

### Caso 7: Disciplina com Múltiplos Locais

**Disciplina:**
```json
{
  "sigla": "LAB-BD",
  "descricao": "Laboratório de Banco de Dados",
  "horarios_de_aula": "3V12,5V34",
  "locais_de_aula": ["Lab 01", "Lab 03"]
}
```

**Interpretação:**
- Terça (3) V12: Pode ser no Lab 01 ou Lab 03
- Quinta (5) V34: Pode ser no Lab 01 ou Lab 03

---

## Variações de Dados

### Variação 1: Aluno Novo (Sem Notas)

```json
{
  "disciplina": "TEC.0015 - Desenvolvimento Mobile",
  "nota_etapa_1": { "nota": null, "numero_avaliacoes": 0 },
  "nota_etapa_2": { "nota": null, "numero_avaliacoes": 0 },
  "numero_faltas": 0,
  "percentual_carga_horaria_frequentada": 100.0
}
```

### Variação 2: Disciplina Concluída (Aprovado)

```json
{
  "disciplina": "TEC.0016 - Lógica de Programação",
  "nota_etapa_1": { "nota": 90.0, "numero_avaliacoes": 2 },
  "nota_etapa_2": { "nota": 95.0, "numero_avaliacoes": 3 },
  "media_final_disciplina": 93.0,
  "situacao": "Aprovado",
  "numero_faltas": 1,
  "percentual_carga_horaria_frequentada": 98.75
}
```

### Variação 3: Disciplina com Avaliação Final Realizada

```json
{
  "disciplina": "TEC.0017 - Estrutura de Dados",
  "nota_etapa_1": { "nota": 55.0, "numero_avaliacoes": 2 },
  "nota_etapa_2": { "nota": 50.0, "numero_avaliacoes": 2 },
  "nota_avaliacao_final": { "nota": 70.0, "numero_avaliacoes": 1 },
  "media_final_disciplina": 62.0,
  "situacao": "Aprovado",
  "numero_faltas": 3,
  "percentual_carga_horaria_frequentada": 96.25
}
```

### Variação 4: Trancamento

```json
{
  "disciplina": "TEC.0018 - Inteligência Artificial",
  "nota_etapa_1": { "nota": 60.0, "numero_avaliacoes": 1 },
  "nota_etapa_2": { "nota": null, "numero_avaliacoes": 0 },
  "situacao": "Trancado",
  "numero_faltas": 5,
  "percentual_carga_horaria_frequentada": 87.5
}
```

### Variação 5: Horário Noturno

```json
{
  "sigla": "ADM",
  "descricao": "Administração de Sistemas",
  "horarios_de_aula": "2N12,4N34",
  "locais_de_aula": ["Lab 04"]
}
```

**Horários Noturnos (Adicionar ao parser):**
```javascript
N: {
  '1': '18:45 - 19:30',
  '2': '19:30 - 20:15',
  '3': '20:25 - 21:10',
  '4': '21:10 - 21:55'
}
```

---

## Cenários de Erro

### Erro 1: Token Expirado

**Request:**
```http
GET /api/v2/minhas-informacoes/meus-dados/
Authorization: Bearer token_expirado
```

**Response (401 Unauthorized):**
```json
{
  "detail": "Token inválido ou expirado"
}
```

**Ação:** Renovar token com refresh_token

### Erro 2: Período Não Encontrado

**Request:**
```http
GET /api/v2/minhas-informacoes/boletim/2024/2/
```

**Response (404 Not Found):**
```json
{
  "detail": "Não encontrado."
}
```

**Ação:** Tentar período anterior

### Erro 3: Acesso Negado (Scope Insuficiente)

**Request:**
```http
GET /api/v2/minhas-informacoes/boletim/2024/1/
Authorization: Bearer token_sem_scope_adequado
```

**Response (403 Forbidden):**
```json
{
  "detail": "Você não tem permissão para executar essa ação."
}
```

**Ação:** Verificar scopes na autorização OAuth

### Erro 4: Código de Autorização Inválido

**Request:**
```http
POST /o/token/
Content-Type: application/x-www-form-urlencoded

grant_type=authorization_code&code=codigo_invalido&client_id=...
```

**Response (400 Bad Request):**
```json
{
  "error": "invalid_grant",
  "error_description": "Invalid authorization code"
}
```

**Ação:** Reiniciar fluxo de autenticação

### Erro 5: Client Secret Incorreto

**Request:**
```http
POST /o/token/
Content-Type: application/x-www-form-urlencoded

grant_type=authorization_code&code=...&client_secret=errado
```

**Response (401 Unauthorized):**
```json
{
  "error": "invalid_client",
  "error_description": "Invalid client credentials"
}
```

**Ação:** Verificar CLIENT_SECRET nas configurações

### Erro 6: Refresh Token Inválido

**Request:**
```http
POST /o/token/
Content-Type: application/x-www-form-urlencoded

grant_type=refresh_token&refresh_token=token_invalido
```

**Response (400 Bad Request):**
```json
{
  "error": "invalid_grant",
  "error_description": "Invalid refresh token"
}
```

**Ação:** Fazer login novamente

---

## Dados de Teste

Para desenvolvimento e testes, você pode usar estes dados de exemplo:

### Mock de Boletim

```javascript
export const mockBoletim = [
  {
    disciplina: "TEC.0010 - Programação Orientada a Objetos",
    codigo_diario: "123456",
    carga_horaria: 80,
    carga_horaria_cumprida: 40,
    numero_faltas: 2,
    percentual_carga_horaria_frequentada: 97.5,
    nota_etapa_1: { nota: 85.0, numero_avaliacoes: 2 },
    nota_etapa_2: { nota: 90.0, numero_avaliacoes: 3 },
    nota_etapa_3: { nota: null, numero_avaliacoes: 0 },
    nota_etapa_4: { nota: null, numero_avaliacoes: 0 },
    situacao: "Cursando",
  },
  {
    disciplina: "TEC.0011 - Banco de Dados",
    codigo_diario: "123457",
    carga_horaria: 80,
    carga_horaria_cumprida: 40,
    numero_faltas: 5,
    percentual_carga_horaria_frequentada: 87.5,
    nota_etapa_1: { nota: 70.0, numero_avaliacoes: 2 },
    nota_etapa_2: { nota: 55.0, numero_avaliacoes: 2 },
    nota_etapa_3: { nota: null, numero_avaliacoes: 0 },
    nota_etapa_4: { nota: null, numero_avaliacoes: 0 },
    situacao: "Cursando",
  },
];

export const mockHorarios = [
  {
    id: 123456,
    sigla: "POO",
    descricao: "Programação Orientada a Objetos",
    horarios_de_aula: "2M12,4M34",
    locais_de_aula: ["Lab 01"],
  },
  {
    id: 123457,
    sigla: "BD",
    descricao: "Banco de Dados",
    horarios_de_aula: "3V12 / 5V34",
    locais_de_aula: ["Lab 03"],
  },
];

export const mockUserData = {
  id: 123456,
  matricula: "20241234567",
  nome_usual: "João Silva",
  email: "joao.silva@escolar.ifrn.edu.br",
  url_foto_150x200: "https://via.placeholder.com/150x200",
  tipo_usuario: "Aluno",
  vinculo: {
    curso: "Tecnologia em Análise e Desenvolvimento de Sistemas",
    campus: "Campus Natal - Central",
    situacao: "Ativo",
  },
};
```

### Interceptor para Testes (React Native)

```javascript
// services/suap/mockInterceptor.js
const MOCK_MODE = __DEV__; // Só em desenvolvimento

export const mockFetch = (originalFetch) => {
  return async (url, options) => {
    if (!MOCK_MODE) {
      return originalFetch(url, options);
    }
    
    // Simular delay de rede
    await new Promise(resolve => setTimeout(resolve, 500));
    
    if (url.includes('/meus-dados/')) {
      return {
        ok: true,
        status: 200,
        json: async () => mockUserData,
      };
    }
    
    if (url.includes('/boletim/')) {
      return {
        ok: true,
        status: 200,
        json: async () => mockBoletim,
      };
    }
    
    if (url.includes('/turmas-virtuais/')) {
      return {
        ok: true,
        status: 200,
        json: async () => mockHorarios,
      };
    }
    
    // Fallback para requisição real
    return originalFetch(url, options);
  };
};

// Uso
global.fetch = mockFetch(global.fetch);
```

---

## Fluxo Completo de Exemplo

### Sequência de Chamadas na Inicialização

```javascript
// 1. Login
const token = await authenticateWithSUAP();

// 2. Buscar dados do usuário
const userData = await getUserData();
await saveUserData(userData);

// 3. Buscar boletim atual
const boletim = await getCurrentBoletim();
await saveBoletim(boletim);

// 4. Buscar horários
const horarios = await getCurrentHorarios();
await saveHorarios(horarios);

// 5. Processar dados
const notasProcessadas = boletim.map(calcularNotaNecessaria);
const horariosProcessados = horarios.map(disciplina => ({
  ...disciplina,
  horariosParsed: parseHorarios(disciplina.horarios_de_aula),
}));

// 6. Calcular estatísticas
const mediaGeral = calcularMediaGeral(boletim);
const frequenciaGeral = calcularFrequenciaGeral(boletim);
const proximaAula = getProximaAula(horariosProcessados);
```

---

## Dicas de Debug

### 1. Logar Respostas Completas

```javascript
const debugFetch = async (url, options) => {
  console.log('📤 Request:', url, options);
  
  const response = await fetch(url, options);
  const data = await response.json();
  
  console.log('📥 Response:', {
    status: response.status,
    ok: response.ok,
    data,
  });
  
  return { ...response, json: async () => data };
};
```

### 2. Verificar Estrutura de Dados

```javascript
const validateDisciplina = (disciplina) => {
  const required = [
    'disciplina',
    'carga_horaria',
    'numero_faltas',
    'percentual_carga_horaria_frequentada',
  ];
  
  const missing = required.filter(field => !(field in disciplina));
  
  if (missing.length > 0) {
    console.warn('⚠️ Campos faltando:', missing, disciplina);
  }
  
  return missing.length === 0;
};
```

### 3. Monitor de Token

```javascript
const monitorToken = async () => {
  const expiresAt = await getToken('token_expires');
  const now = Date.now();
  const timeLeft = expiresAt - now;
  
  console.log('🔐 Token info:', {
    expiresAt: new Date(parseInt(expiresAt)).toLocaleString(),
    timeLeft: `${Math.floor(timeLeft / 1000 / 60)} minutos`,
    isValid: timeLeft > 0,
  });
};
```

---

**Última Atualização:** Outubro 2024
**Versão:** 2.6.0

