<?php

/**
 * Módulo de gerenciamento de calendário e feriados
 * 
 * Este arquivo centraliza todas as funcionalidades relacionadas
 * à gestão de datas, feriados e manipulação do calendário acadêmico
 * 
 * @author Kellyson
 * @version 1.0.0
 */

// Require necessários
require_once 'config.php';

/**
 * Verifica se uma data é um feriado
 * 
 * @param DateTime $data Data a ser verificada
 * @return string|null Nome do feriado ou null se não for feriado
 */
function verificarFeriado($data)
{
    // Lista de feriados nacionais fixos (dia/mês)
    $feriadosFixos = [
        '01-01' => 'Ano Novo',
        '21-04' => 'Tiradentes',
        '01-05' => 'Dia do Trabalho',
        '07-09' => 'Independência do Brasil',
        '12-10' => 'Nossa Senhora Aparecida',
        '02-11' => 'Finados',
        '15-11' => 'Proclamação da República',
        '25-12' => 'Natal'
    ];

    // Verificar feriados fixos
    $dataFormatada = $data->format('d-m');
    if (isset($feriadosFixos[$dataFormatada])) {
        return $feriadosFixos[$dataFormatada];
    }

    // Obter o ano atual para calcular feriados móveis
    $ano = (int)$data->format('Y');

    // Calcular a data da Páscoa (algoritmo de Gauss/Computus)
    $feriadosMoveis = calcularFeriadosMoveis($ano);

    // Verificar se a data corresponde a algum feriado móvel
    $dataFormatadaCompleta = $data->format('Y-m-d');
    if (isset($feriadosMoveis[$dataFormatadaCompleta])) {
        return $feriadosMoveis[$dataFormatadaCompleta];
    }

    // TODO: Implementar lógica para feriados estaduais e municipais

    return null;
}

/**
 * Calcula feriados móveis com base na data da Páscoa
 * 
 * @param int $ano Ano para calcular os feriados
 * @return array Array associativo com as datas dos feriados móveis
 */
function calcularFeriadosMoveis($ano)
{
    // Algoritmo para calcular a data da Páscoa (Butcher/Meeus/Jones/Butcher)
    $a = $ano % 19;
    $b = floor($ano / 100);
    $c = $ano % 100;
    $d = floor($b / 4);
    $e = $b % 4;
    $f = floor(($b + 8) / 25);
    $g = floor(($b - $f + 1) / 3);
    $h = (19 * $a + $b - $d - $g + 15) % 30;
    $i = floor($c / 4);
    $k = $c % 4;
    $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
    $m = floor(($a + 11 * $h + 22 * $l) / 451);
    $mes = floor(($h + $l - 7 * $m + 114) / 31);
    $dia = (($h + $l - 7 * $m + 114) % 31) + 1;

    // Data da Páscoa
    $pascoa = new DateTime("$ano-$mes-$dia");

    // Cria objeto de retorno
    $feriados = [];

    // Adiciona a Páscoa e os feriados relacionados
    $feriados[$pascoa->format('Y-m-d')] = 'Páscoa';

    // Sexta-feira Santa (2 dias antes da Páscoa)
    $sextaSanta = clone $pascoa;
    $sextaSanta->modify('-2 days');
    $feriados[$sextaSanta->format('Y-m-d')] = 'Sexta-feira Santa';

    // Carnaval (47 dias antes da Páscoa)
    $carnaval = clone $pascoa;
    $carnaval->modify('-47 days');
    $feriados[$carnaval->format('Y-m-d')] = 'Carnaval';

    // Corpus Christi (60 dias após a Páscoa)
    $corpusChristi = clone $pascoa;
    $corpusChristi->modify('+60 days');
    $feriados[$corpusChristi->format('Y-m-d')] = 'Corpus Christi';

    return $feriados;
}

/**
 * Obtém a lista completa de feriados do ano especificado
 * 
 * @param int $ano Ano para obter os feriados (padrão: ano atual)
 * @return array Array associativo com as datas e nomes dos feriados
 */
function listarTodosFeriados($ano = null)
{
    if ($ano === null) {
        $ano = (int)date('Y');
    }

    // Inicializa o array de feriados
    $feriados = [];

    // Feriados fixos
    $feriadosFixos = [
        '01-01' => 'Ano Novo',
        '21-04' => 'Tiradentes',
        '01-05' => 'Dia do Trabalho',
        '07-09' => 'Independência do Brasil',
        '12-10' => 'Nossa Senhora Aparecida',
        '02-11' => 'Finados',
        '15-11' => 'Proclamação da República',
        '25-12' => 'Natal'
    ];

    // Adiciona os feriados fixos
    foreach ($feriadosFixos as $data => $nome) {
        list($dia, $mes) = explode('-', $data);
        $dataCompleta = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
        $feriados[$dataCompleta] = $nome;
    }

    // Adiciona os feriados móveis
    $feriadosMoveis = calcularFeriadosMoveis($ano);
    $feriados = array_merge($feriados, $feriadosMoveis);

    // Ordena os feriados por data
    ksort($feriados);

    return $feriados;
}
