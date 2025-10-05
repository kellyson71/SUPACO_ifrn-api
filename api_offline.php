<?php
// Configurações do arquivo
header('Content-Type: application/json');
session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['access_token'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autenticado']);
    exit;
}

// Inclui funções de utilidade da API
require_once 'api_utils.php';

// Endpoint solicitado
$action = isset($_GET['action']) ? $_GET['action'] : '';
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';

// Manter compatibilidade
if ($endpoint && !$action) {
    $action = $endpoint;
}

// Verifica se é um teste simples (sem autenticação necessária)
if (isset($_GET['test'])) {
    $response = handleTestEndpoint();
    echo json_encode($response);
    exit;
}

// Verifica qual endpoint foi solicitado
switch ($action) {
    case 'boletim':
        $response = handleBoletimRequest();
        echo json_encode($response);
        break;

    case 'horarios':
        $response = handleHorariosRequest();
        echo json_encode($response);
        break;

    case 'meusdados':
        $response = handleMeusDadosRequest();
        echo json_encode($response);
        break;

    case 'atualizar_todos':
        $response = handleAtualizarTodosRequest();
        echo json_encode($response);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Endpoint não encontrado']);
        break;
}

// ===========================
// FUNÇÕES DOS ENDPOINTS
// ===========================

function handleBoletimRequest()
{
    // Para modo offline/teste, sempre usar dados de exemplo
    if (!isset($_SESSION['boletim'])) {
        $_SESSION['boletim'] = getDadosBoletimExemplo();
    }

    return [
        'dados' => $_SESSION['boletim'],
        'timestamp' => time(),
        'status' => 'success',
        'source' => 'cache'
    ];
}

function handleHorariosRequest()
{
    // Para modo offline/teste, sempre usar dados de exemplo
    if (!isset($_SESSION['horarios'])) {
        $_SESSION['horarios'] = getDadosHorariosExemplo();
    }

    return [
        'dados' => $_SESSION['horarios'],
        'timestamp' => time(),
        'status' => 'success',
        'source' => 'cache'
    ];
}

function handleMeusDadosRequest()
{
    // Para modo offline/teste, sempre usar dados de exemplo
    if (!isset($_SESSION['meusdados'])) {
        $_SESSION['meusdados'] = getDadosUsuarioExemplo();
    }

    return [
        'dados' => $_SESSION['meusdados'],
        'timestamp' => time(),
        'status' => 'success'
    ];
}

function handleAtualizarTodosRequest()
{
    // Força atualização de todos os dados
    unset($_SESSION['boletim']);
    unset($_SESSION['horarios']);
    unset($_SESSION['meusdados']);

    $boletim = handleBoletimRequest();
    $horarios = handleHorariosRequest();
    $meusdados = handleMeusDadosRequest();

    return [
        'boletim' => $boletim,
        'horarios' => $horarios,
        'meusdados' => $meusdados,
        'timestamp' => time(),
        'status' => 'success'
    ];
}

// ===========================
// DADOS DE EXEMPLO
// ===========================

function getDadosBoletimExemplo()
{
    return [
        [
            'disciplina' => 'Programação Web',
            'periodo' => ['periodo' => '2024.2'],
            'nota_1' => 8.5,
            'nota_2' => 9.0,
            'nota_3' => 7.8,
            'nota_4' => 8.7,
            'media' => 8.5,
            'situacao' => 'Aprovado'
        ],
        [
            'disciplina' => 'Banco de Dados',
            'periodo' => ['periodo' => '2024.2'],
            'nota_1' => 7.5,
            'nota_2' => 8.2,
            'nota_3' => 8.8,
            'nota_4' => 9.1,
            'media' => 8.4,
            'situacao' => 'Aprovado'
        ],
        [
            'disciplina' => 'Engenharia de Software',
            'periodo' => ['periodo' => '2024.2'],
            'nota_1' => 9.0,
            'nota_2' => 8.5,
            'nota_3' => 9.2,
            'nota_4' => 8.8,
            'media' => 8.9,
            'situacao' => 'Aprovado'
        ],
        [
            'disciplina' => 'Redes de Computadores',
            'periodo' => ['periodo' => '2024.2'],
            'nota_1' => 6.5,
            'nota_2' => 7.0,
            'nota_3' => 7.8,
            'nota_4' => 8.2,
            'media' => 7.4,
            'situacao' => 'Aprovado'
        ]
    ];
}

function getDadosHorariosExemplo()
{
    return [
        [
            'dia_semana' => 'Segunda-feira',
            'horario' => '07:00 - 08:40',
            'disciplina' => 'Programação Web',
            'professor' => 'Prof. Dr. João Silva',
            'sala' => 'Lab. 01'
        ],
        [
            'dia_semana' => 'Segunda-feira',
            'horario' => '08:50 - 10:30',
            'disciplina' => 'Banco de Dados',
            'professor' => 'Prof. Maria Santos',
            'sala' => 'Sala 201'
        ],
        [
            'dia_semana' => 'Terça-feira',
            'horario' => '07:00 - 08:40',
            'disciplina' => 'Engenharia de Software',
            'professor' => 'Prof. Pedro Costa',
            'sala' => 'Sala 105'
        ],
        [
            'dia_semana' => 'Terça-feira',
            'horario' => '08:50 - 10:30',
            'disciplina' => 'Redes de Computadores',
            'professor' => 'Prof. Ana Oliveira',
            'sala' => 'Lab. 02'
        ],
        [
            'dia_semana' => 'Quarta-feira',
            'horario' => '07:00 - 08:40',
            'disciplina' => 'Programação Web',
            'professor' => 'Prof. Dr. João Silva',
            'sala' => 'Lab. 01'
        ],
        [
            'dia_semana' => 'Quinta-feira',
            'horario' => '08:50 - 10:30',
            'disciplina' => 'Banco de Dados',
            'professor' => 'Prof. Maria Santos',
            'sala' => 'Sala 201'
        ]
    ];
}

function getDadosUsuarioExemplo()
{
    return [
        'nome_usual' => 'Estudante SUPACO',
        'tipo_vinculo' => 'Aluno',
        'vinculo' => [
            'curriculo_lattes' => 'https://lattes.cnpq.br/exemplo'
        ],
        'url_foto_150x200' => 'assets/images/perfil.png'
    ];
}

// Verificar se há dados de sessão antigos
