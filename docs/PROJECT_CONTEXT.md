# SUPACO - Sistema Útil Pra Aluno Cansado e Ocupado

## Visão Geral

SUPACO é um sistema de gestão acadêmica que se conecta à API do SUAP para fornecer uma visão consolidada do desempenho acadêmico dos alunos. Ele exibe dados de aulas, calcula notas e frequências, e oferece uma interface intuitiva para simulação de notas.

## Stack Tecnológica

- **Backend**: PHP 7+
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Framework CSS**: Bootstrap 5
- **Bibliotecas JS**: Chart.js, AOS, Toastify
- **PWA**: Service Worker, Manifest
- **Autenticação**: OAuth 2.0 (SUAP)

## Estrutura do Projeto

```
SUAP/
├── assets/
│   ├── css/          # Estilos CSS organizados
│   ├── js/           # Scripts JavaScript
│   └── images/       # Imagens e ícones
├── docs/             # Documentação
│   ├── changelog/    # Histórico de mudanças
│   ├── guides/       # Guias técnicos
│   └── stories/      # User stories
├── tests/            # Arquivos de teste
├── temp/             # Arquivos temporários
├── *.php            # Arquivos principais da aplicação
└── manifest.json    # Configuração PWA
```

## Convenções de Código

- **PHP**: PSR-12 (quando possível)
- **CSS**: BEM methodology
- **JavaScript**: ES6+
- **Comentários**: Em português, objetivos e claros
- **Nomes**: Em inglês para código, português para comentários

## Funcionalidades Principais

1. **Autenticação SUAP**: Integração OAuth 2.0
2. **Dashboard Acadêmico**: Visualização de notas e frequência
3. **Simulação de Notas**: Cálculo de médias
4. **Horários de Aulas**: Agenda organizada
5. **Modo Escuro**: Tema alternativo
6. **PWA**: Funcionalidade offline

## Configuração

### Requisitos
- PHP 7+
- Servidor web (Apache/Nginx)
- Credenciais SUAP válidas

### Instalação
1. Configure `config.php` com credenciais SUAP
2. Ajuste `REDIRECT_URI` para seu domínio
3. Acesse via navegador para autenticação

## Arquitetura

- **MVC**: Separação de responsabilidades
- **API**: Comunicação com SUAP via REST
- **Cache**: Service Worker para offline
- **Responsivo**: Mobile-first design
