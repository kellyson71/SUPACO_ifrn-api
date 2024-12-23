<?php
function parseHorario($horarioStr) {
    // Exemplo: "2M12" => ['dia' => 2, 'turno' => 'M', 'aulas' => ['1', '2']]
    if (empty($horarioStr)) {
        return [];
    }
    
    $horarios = explode(' / ', $horarioStr);
    $result = [];
    
    foreach ($horarios as $horario) {
        if (preg_match('/(\d)([MT])(\d+)/', $horario, $matches)) {
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
    $aulas = [
        ['hora' => '07:00 - 07:45', 'codigo' => '1'],
        ['hora' => '07:45 - 08:30', 'codigo' => '2'],
        ['hora' => '08:50 - 09:35', 'codigo' => '3'],
        ['hora' => '09:35 - 10:20', 'codigo' => '4'],
        ['hora' => '10:30 - 11:15', 'codigo' => '5'],
        ['hora' => '11:15 - 12:00', 'codigo' => '6']
    ];
    
    echo '<div class="table-responsive">';
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
                        if ($h['dia'] == $dia && in_array($aula['codigo'], $h['aulas'])) {
                            echo '<div class="disciplina-info">';
                            echo '<strong>' . htmlspecialchars($disciplina['sigla']) . '</strong><br>';
                            echo '<small>' . htmlspecialchars($disciplina['descricao']) . '</small>';
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
    
    echo '</tbody></table></div>';
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
