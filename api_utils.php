<?php

/**
 * Funções auxiliares para analisar e processar os dados da API SUAP,
 * especialmente para lidar com possíveis problemas de formato dos dados
 */

/**
 * Limpa e corrige o formato dos dados retornados pela API
 * 
 * @param array $dados Dados da API que podem precisar de correção
 * @return array Dados formatados e corrigidos
 */
function sanitizarDadosAPI($dados)
{
    if (!is_array($dados)) {
        return $dados;
    }

    $resultado = [];

    foreach ($dados as $item) {
        // Verifica se o horário de aulas está corrompido
        if (isset($item['horarios_de_aula'])) {
            // Remove possíveis caracteres inválidos ou mal formatados
            $item['horarios_de_aula'] = preg_replace('/{.*?}/s', '', $item['horarios_de_aula']);
            $item['horarios_de_aula'] = trim($item['horarios_de_aula']);
        }

        // Verifica se os locais de aula estão corretos
        if (isset($item['locais_de_aula']) && !is_array($item['locais_de_aula'])) {
            // Se não for um array, tenta converter
            if (is_string($item['locais_de_aula'])) {
                $item['locais_de_aula'] = [$item['locais_de_aula']];
            } else {
                $item['locais_de_aula'] = [];
            }
        }

        $resultado[] = $item;
    }

    return $resultado;
}

/**
 * Converte o formato de data da API para o padrão usado no sistema
 * 
 * @param string $dataStr Data no formato da API
 * @return DateTime Objeto DateTime formatado
 */
function converterDataAPI($dataStr)
{
    try {
        return new DateTime($dataStr);
    } catch (Exception $e) {
        error_log("Erro ao converter data: " . $e->getMessage());
        return new DateTime();
    }
}

/**
 * Faz requisições para a API do SUAP
 * 
 * @param string $endpoint Endpoint da API
 * @param string $token Token de acesso
 * @return array Dados retornados pela API
 */
function fetchApi($endpoint, $token)
{
    $url = 'https://suap.ifrn.edu.br/api/v2/' . $endpoint;

    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log("Erro cURL: " . $error);
        return [];
    }

    if ($httpCode !== 200) {
        error_log("Erro HTTP: " . $httpCode);
        return [];
    }

    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Erro JSON: " . json_last_error_msg());
        return [];
    }

    return $data;
}

/**
 * Endpoint específico para testes do PWA
 * Retorna dados básicos de teste
 */
function handleTestEndpoint()
{
    return [
        'status' => 'success',
        'message' => 'API offline funcionando',
        'timestamp' => time(),
        'version' => '2.0',
        'endpoints' => [
            'boletim',
            'horarios',
            'meusdados',
            'atualizar_todos',
            'test'
        ]
    ];
}
