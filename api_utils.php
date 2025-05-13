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
