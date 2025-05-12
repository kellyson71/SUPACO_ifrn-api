<?php
function parseHorario($horarioStr) {
    // Exemplo: "2M12" => ['dia' => 2, 'turno' => 'M', 'aulas' => ['1', '2']]
    // ou "2V12" => ['dia' => 2, 'turno' => 'V', 'aulas' => ['1', '2']]
    if (empty($horarioStr)) {
        return [];
    }
    
    $horarios = explode(' / ', $horarioStr);
    $result = [];
    
    foreach ($horarios as $horario) {
        if (preg_match('/(\d)([MTV])(\d+)/', $horario, $matches)) {
            $result[] = [
                'dia' => (int)$matches[1],
                'turno' => $matches[2],
                'aulas' => str_split($matches[3])
            ];
        }
    }
    
    return $result;
}

function mostrarHorarios($horarios) {
    $dias = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta'];

    // Definição dos horários de aulas para Manhã e Tarde/Vespertino
    $aulasManha = [
        ['hora' => '07:00 - 07:45', 'codigo' => '1', 'turno' => 'M'],
        ['hora' => '07:45 - 08:30', 'codigo' => '2', 'turno' => 'M'],
        ['hora' => '08:50 - 09:35', 'codigo' => '3', 'turno' => 'M'],
        ['hora' => '09:35 - 10:20', 'codigo' => '4', 'turno' => 'M'],
        ['hora' => '10:30 - 11:15', 'codigo' => '5', 'turno' => 'M'],
        ['hora' => '11:15 - 12:00', 'codigo' => '6', 'turno' => 'M']
    ];

    $aulasTarde = [
        ['hora' => '13:00 - 13:45', 'codigo' => '1', 'turno' => 'V'],
        ['hora' => '13:45 - 14:30', 'codigo' => '2', 'turno' => 'V'],
        ['hora' => '14:50 - 15:35', 'codigo' => '3', 'turno' => 'V'],
        ['hora' => '15:35 - 16:20', 'codigo' => '4', 'turno' => 'V'],
        ['hora' => '16:30 - 17:15', 'codigo' => '5', 'turno' => 'V'],
        ['hora' => '17:15 - 18:00', 'codigo' => '6', 'turno' => 'V']
    ];

    // Verificar quais turnos existem nas disciplinas
    $temAulasManha = false;
    $temAulasTarde = false;

    foreach ($horarios as $disciplina) {
        if (!empty($disciplina['horarios_de_aula'])) {
            $horariosArray = parseHorario($disciplina['horarios_de_aula']);
            foreach ($horariosArray as $h) {
                if ($h['turno'] == 'M') {
                    $temAulasManha = true;
                } elseif ($h['turno'] == 'V') {
                    $temAulasTarde = true;
                }
            }
        }
    }

    // Se não houver nenhum horário, mostrar ambos os turnos
    if (!$temAulasManha && !$temAulasTarde) {
        $temAulasManha = true;
        $temAulasTarde = true;
    }

    echo '<div class="table-responsive">';

    // Tabela para horários da Manhã
    if ($temAulasManha) {
        echo '<h4 class="mt-4 mb-3"><i class="fas fa-sun me-2"></i>Horários da Manhã</h4>';
        exibirTabelaHorarios($horarios, $dias, $aulasManha);
    }

    // Tabela para horários da Tarde
    if ($temAulasTarde) {
        echo '<h4 class="mt-4 mb-3"><i class="fas fa-sun me-2"></i>Horários da Tarde</h4>';
        exibirTabelaHorarios($horarios, $dias, $aulasTarde);
    }

    echo '</div>';
}

function exibirTabelaHorarios($horarios, $dias, $aulas)
{
    echo '<table class="table table-bordered table-horario">';
    echo '<thead><tr>';
    echo '<th width="15%">Horário</th>';
    foreach ($dias as $dia) {
        echo '<th width="17%" class="text-center">' . $dia . '</th>';
    }
    echo '</tr></thead><tbody>';
    
    foreach ($aulas as $aula) {
        echo '<tr>';
        echo '<td class="text-center"><strong>' . $aula['hora'] . '</strong></td>';
        
        // Alterado para começar do 2 (Segunda) até 6 (Sexta)
        for ($dia = 2; $dia <= 6; $dia++) {
            echo '<td>';
            foreach ($horarios as $disciplina) {
                if (!empty($disciplina['horarios_de_aula'])) {
                    $horariosArray = parseHorario($disciplina['horarios_de_aula']);
                    foreach ($horariosArray as $h) {
                        if ($h['dia'] == $dia && $h['turno'] == $aula['turno'] && in_array($aula['codigo'], $h['aulas'])) {
                            echo '<div class="disciplina-info">';
                            echo '<span class="disciplina-codigo">' . htmlspecialchars($disciplina['sigla']) . '</span>';
                            echo '<strong class="disciplina-nome">' . htmlspecialchars($disciplina['descricao']) . '</strong>';
                            if (!empty($disciplina['locais_de_aula'][0])) {
                                echo '<br><small class="text-muted">' . 
                                     htmlspecialchars($disciplina['locais_de_aula'][0]) . 
                                     '</small>';
                            }
                            echo '</div>';
                        }
                    }
                }
            }
            echo '</td>';
        }
        echo '</tr>';
    }

    echo '</tbody></table>';
}
?>

<style>
.table-horario {
    font-size: 0.9em;
}
.disciplina-info {
    padding: 5px;
    margin: 2px;
    border-radius: 4px;
    background-color: #f8f9fa;
    font-size: 0.85em;
}
.disciplina-info small {
    display: block;
    color: #6c757d;
}
</style>
