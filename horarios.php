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
<<<<<<< HEAD

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

=======
    
    // Versão Desktop
    echo '<div class="d-none d-md-block">';
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
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
    echo '<th class="hora-col" width="15%">Horário</th>';
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
<<<<<<< HEAD
                            echo '<span class="disciplina-codigo">' . htmlspecialchars($disciplina['sigla']) . '</span>';
                            echo '<strong class="disciplina-nome">' . htmlspecialchars($disciplina['descricao']) . '</strong>';
=======
                            echo '<strong class="sigla-mobile">' . htmlspecialchars($disciplina['sigla']) . '</strong>';
                            echo '<small class="descricao-completa">' . htmlspecialchars($disciplina['descricao']) . '</small>';
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
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
<<<<<<< HEAD

    echo '</tbody></table>';
=======
    
    echo '</tbody></table></div>';
    echo '</div>';
    
    // Versão Mobile
    echo '<div class="d-md-none">';
    echo '<div class="horario-mobile">';
    
    // Tabs para os dias
    echo '<ul class="nav nav-pills mb-3 justify-content-between" role="tablist">';
    foreach ($dias as $index => $dia) {
        $diaId = strtolower(removeAcentos($dia));
        $active = $index == 0 ? 'active' : '';
        echo "<li class='nav-item' role='presentation'>";
        echo "<button class='nav-link {$active}' data-bs-toggle='pill' data-bs-target='#tab-{$diaId}' type='button'>";
        echo substr($dia, 0, 3);
        echo "</button>";
        echo "</li>";
    }
    echo '</ul>';
    
    // Conteúdo das tabs
    echo '<div class="tab-content">';
    foreach ($dias as $index => $dia) {
        $diaId = strtolower(removeAcentos($dia));
        $active = $index == 0 ? 'show active' : '';
        $diaSUAP = $index + 2; // Converte para o formato do SUAP (2-6)
        
        echo "<div class='tab-pane fade {$active}' id='tab-{$diaId}'>";
        echo "<div class='list-group'>";
        
        foreach ($aulas as $aula) {
            echo "<div class='horario-item mb-2'>";
            echo "<div class='horario-header'>";
            echo "<small class='text-muted'>{$aula['hora']}</small>";
            echo "</div>";
            
            $temAula = false;
            foreach ($horarios as $disciplina) {
                if (!empty($disciplina['horarios_de_aula'])) {
                    $horariosArray = parseHorario($disciplina['horarios_de_aula']);
                    foreach ($horariosArray as $h) {
                        if ($h['dia'] == $diaSUAP && in_array($aula['codigo'], $h['aulas'])) {
                            $temAula = true;
                            echo "<div class='card card-aula mb-2'>";
                            echo "<div class='card-body p-2'>";
                            echo "<h6 class='mb-1'>{$disciplina['sigla']}</h6>";
                            echo "<small class='d-block text-muted'>{$disciplina['descricao']}</small>";
                            if (!empty($disciplina['locais_de_aula'][0])) {
                                echo "<small class='d-block mt-1'>";
                                echo "<i class='fas fa-map-marker-alt me-1'></i>";
                                echo htmlspecialchars($disciplina['locais_de_aula'][0]);
                                echo "</small>";
                            }
                            echo "</div>";
                            echo "</div>";
                        }
                    }
                }
            }
            
            if (!$temAula) {
                echo "<div class='text-center p-2 text-muted'>";
                echo "<small>Sem aula</small>";
                echo "</div>";
            }
            
            echo "</div>";
        }
        
        echo "</div>";
        echo "</div>";
    }
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

function removeAcentos($string) {
    return strtolower(preg_replace(array("/(á|à|ã|â|ä)/","/(é|è|ê|ë)/","/(í|ì|î|ï)/","/(ó|ò|õ|ô|ö)/","/(ú|ù|û|ü)/","/(ñ)/","/(ç)/"),
                                  array("a","e","i","o","u","n","c"), $string));
>>>>>>> 48799c664a6dadedc72a3088dd6c3fa874c6dc30
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

/* Novos estilos para mobile */
.horario-mobile .nav-pills {
    gap: 0.5rem;
    padding: 0.5rem;
    background: rgba(255,255,255,0.5);
    border-radius: 10px;
}

.horario-mobile .nav-link {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.9rem;
    color: var(--primary-color);
}

.horario-mobile .nav-link.active {
    background: var(--primary-color);
}

.horario-item {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}

.horario-header {
    padding: 0.5rem;
    background: rgba(0,0,0,0.02);
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.card-aula {
    margin: 0.5rem;
    border: none;
    background: rgba(26, 115, 232, 0.03);
    border-left: 3px solid var(--primary-color);
}

.card-aula:hover {
    background: rgba(26, 115, 232, 0.05);
}

/* Ajustes responsivos */
@media (max-width: 768px) {
    .table-horario {
        font-size: 0.8em;
    }

    .table-horario th {
        padding: 0.5rem !important;
    }

    .table-horario td {
        padding: 0.5rem !important;
    }

    .disciplina-info {
        padding: 0.5rem;
        margin: 0.25rem 0;
    }

    .disciplina-info strong {
        font-size: 0.9em;
    }

    .disciplina-info small {
        font-size: 0.75em;
        line-height: 1.2;
    }

    /* Esconde descrição longa em mobile */
    .disciplina-info .descricao-completa {
        display: none;
    }

    /* Mostra apenas sigla em mobile */
    .disciplina-info .sigla-mobile {
        display: block;
    }

    /* Ajustes para horários */
    .hora-col {
        width: 80px !important;
        white-space: nowrap;
        font-size: 0.75em;
    }
    
    .horario-mobile {
        margin: -0.5rem;
    }
    
    .horario-item {
        margin-bottom: 0.75rem;
    }
    
    .card-aula .card-body {
        padding: 0.75rem;
    }
    
    .card-aula h6 {
        font-size: 0.95rem;
    }
    
    .card-aula small {
        font-size: 0.8rem;
    }
}
</style>
