# IF-calc

IF-calc é um sistema de gestão acadêmica que se conecta à API do SUAP para fornecer uma visão consolidada do desempenho acadêmico dos alunos. Ele exibe dados de aulas, calcula notas e frequências, e oferece uma interface intuitiva para simulação de notas.

## Funcionalidades

- **Autenticação via SUAP**: Integração com o sistema SUAP para autenticação segura.
- **Consulta de Dados Acadêmicos**: Exibe informações detalhadas sobre disciplinas, notas e frequência.
- **Simulação de Notas**: Permite simular notas para prever o impacto no desempenho acadêmico.
- **Horários de Aulas**: Mostra os horários das aulas de forma organizada e visualmente agradável.
- **Modo Escuro**: Alterna entre temas claro e escuro para melhor conforto visual.

## Pré-requisitos

- PHP 7+ e servidor local (ex: Wamp, XAMPP)
- Credenciais adequadas no arquivo `config.php`

## Instalação

1. Clone ou baixe este repositório:
    ```sh
    git clone https://github.com/seu-usuario/IF-calc.git
    ```

2. Configure as credenciais do SUAP no arquivo `config.php`:
    ```php
    define('SUAP_CLIENT_ID', 'seu_client_id');
    define('SUAP_CLIENT_SECRET', 'seu_client_secret');
    define('SUAP_URL', 'https://suap.ifrn.edu.br');
    define('REDIRECT_URI', 'http://localhost/IF%20calc/callback.php'); // URL exatamente como registrada no SUAP
    ```

3. Inicie seu servidor local e acesse `index.php` em seu navegador para realizar a autenticação e usar o sistema.

## Uso

### Autenticação

Ao acessar o sistema pela primeira vez, você será redirecionado para a página de login do SUAP. Após a autenticação, o sistema armazenará o token de acesso na sessão e redirecionará de volta para o dashboard.

### Dashboard

O dashboard exibe uma visão geral do desempenho acadêmico, incluindo:

- **Boletim**: Notas e frequência de cada disciplina.
- **Simulação de Notas**: Insira notas simuladas para prever a média final.
- **Horários de Aulas**: Visualize os horários das aulas de amanhã.

### Modo Escuro

Para alternar entre o modo claro e escuro, clique no botão de tema no canto inferior direito da tela. A preferência de tema será salva no navegador.

## Estrutura do Projeto

- `index.php`: Dashboard principal que exibe dados acadêmicos.
- `config.php`: Configurações de credenciais do SUAP.
- `horarios.php`: Funções para manipulação e exibição de horários de aulas.
- `callback.php`: Lida com o retorno da autenticação do SUAP.
- `logout.php`: Encerra a sessão do usuário.
- `base.php`: Template base para todas as páginas, incluindo estilos e scripts comuns.

## Contribuição

Contribuições são bem-vindas! Sinta-se à vontade para abrir issues e pull requests.

## Licença

Este projeto está licenciado sob a [MIT License](LICENSE).

## Contato

Para mais informações, entre em contato com [Seu Nome](mailto:kellyson.medeiros.pdf@gmail.com).

