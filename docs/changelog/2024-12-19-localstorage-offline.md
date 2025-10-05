# Sistema de Armazenamento LocalStorage para Dados Básicos

**Data:** 2024-12-19  
**Tipo:** Feature  
**Impacto:** Alto  

## Descrição

Implementado sistema de armazenamento no localStorage para evitar necessidade constante de login no SUAP. O sistema agora armazena dados básicos localmente e permite uso offline com aviso visual quando dados básicos estão sendo utilizados.

## Mudanças Implementadas

### 1. LocalStorage Manager (`assets/js/localStorage-manager.js`)
- **Novo arquivo** criado para gerenciar dados no localStorage
- Funcionalidades:
  - Salvamento de dados completos do usuário
  - Salvamento de dados básicos para uso offline
  - Verificação de expiração de dados (30 dias)
  - Criação automática de dados básicos
  - Aviso visual quando usando dados básicos

### 2. Sistema de Autenticação (`index.php`)
- **Modificado** para detectar falha de autenticação
- Implementado fallback para dados básicos quando não autenticado
- Verificação via JavaScript se há dados no localStorage
- Redirecionamento para login apenas se não houver dados básicos

### 3. Template Base (`base_dark.php`)
- **Modificado** para incluir LocalStorageManager
- Adicionado script para salvar dados reais no localStorage
- Implementada função para atualizar página com dados do localStorage
- Aviso visual quando usando dados básicos

### 4. Sistema de Login (`login.php`)
- **Modificado** para incluir LocalStorageManager
- Inicialização automática de dados básicos se não existirem
- Preparação para armazenamento após login bem-sucedido

### 5. Callback de Autenticação (`callback.php`)
- **Modificado** para salvar dados do usuário no localStorage após login
- Salvamento automático dos dados obtidos da API SUAP

## Funcionalidades

### Armazenamento Automático
- Dados completos salvos automaticamente após login bem-sucedido
- Dados básicos criados automaticamente para uso offline
- Expiração configurável (padrão: 30 dias)

### Uso Offline
- Sistema funciona sem conexão com SUAP
- Dados básicos exibidos quando não autenticado
- Aviso visual claro sobre uso de dados básicos

### Experiência do Usuário
- Redirecionamento para login apenas quando necessário
- Aviso não intrusivo sobre dados básicos
- Botão de login direto no aviso
- Substituição automática de dados básicos por dados reais quando disponível

## Dados Armazenados

### Dados Completos (após login)
```javascript
{
  meusDados: { /* dados do usuário da API SUAP */ },
  boletim: [ /* dados do boletim */ ],
  horarios: [ /* dados dos horários */ ],
  anoLetivo: 2024,
  periodoLetivo: 2,
  timestamp: 1640995200000,
  isBasic: false
}
```

### Dados Básicos (offline)
```javascript
{
  meusDados: {
    nome_usual: 'Usuário SUPACO',
    matricula: '2024000000',
    vinculo: { curso: 'Curso não informado' },
    url_foto_150x200: 'assets/images/perfil.png'
  },
  boletim: [ /* disciplinas de exemplo */ ],
  horarios: [ /* horários de exemplo */ ],
  timestamp: 1640995200000,
  isBasic: true
}
```

## Aviso Visual

Quando usando dados básicos, um aviso é exibido no canto superior direito:
- **Título:** "Dados Básicos"
- **Mensagem:** "Você está usando apenas os dados básicos. Faça o login novamente para atualizar os dados."
- **Ação:** Botão "Fazer Login" que redireciona para login.php
- **Auto-dismiss:** Desaparece automaticamente após 10 segundos

## Benefícios

1. **Redução de Logins:** Usuários não precisam fazer login constantemente
2. **Funcionalidade Offline:** Sistema funciona sem conexão com SUAP
3. **Experiência Melhorada:** Acesso mais rápido aos dados
4. **Transparência:** Usuário sabe quando está usando dados básicos
5. **Fallback Inteligente:** Sistema escolhe automaticamente a melhor fonte de dados

## Compatibilidade

- **Navegadores:** Todos os navegadores modernos com suporte a localStorage
- **Dispositivos:** Desktop e mobile
- **Fallback:** Redirecionamento para login se localStorage não disponível

## Configurações

- **Expiração de Dados:** 30 dias (configurável em CONFIG.DATA_EXPIRY)
- **Chaves de Armazenamento:** 
  - `supaco_user_data` - Dados completos
  - `supaco_basic_data` - Dados básicos

## Testes Realizados

- ✅ Sintaxe PHP validada em todos os arquivos modificados
- ✅ JavaScript sem erros de sintaxe
- ✅ Sistema de fallback funcionando
- ✅ Aviso visual exibido corretamente
- ✅ Dados salvos e recuperados corretamente

## Próximos Passos

1. Testes em ambiente de produção
2. Monitoramento de uso do localStorage
3. Possível implementação de sincronização automática
4. Melhorias na interface do aviso visual
