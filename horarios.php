<?php
function parseHorario($horarioStr) {
    // Exemplo: "2M12" => ['dia' => 2, 'turno' => 'M', 'aulas' => ['1', '2']]
    // ou "2V12" => ['dia' => 2, 'turno' => 'V', 'aulas' => ['1', '2']]
    // ou "2V12 / 3V34" => dois horários diferentes

    if (empty($horarioStr)) {
        return [];
    }

    // Limpa possíveis caracteres problemáticos
    $horarioStr = trim($horarioStr);

    // Verifica se há múltiplos horários separados por /
    $horarios = preg_split('/\s*\/\s*/', $horarioStr);
    $result = [];
    
    foreach ($horarios as $horario) {
        $horario = trim($horario);
        if (empty($horario)) {
            continue;
        }

        // Padrão principal: 2V12 (dia, turno, aulas)
        if (preg_match('/^(\d)([MTV])(\d+)$/', $horario, $matches)) {
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
}

function criarTabelaHorariosCompleta($horarios = null) {
    $dias = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta'];
    
    // Definição dos horários de aulas para Manhã e Tarde
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

    echo '<div class="horarios-completos">';
    echo '<h3 class="titulo-horarios"><i class="fas fa-calendar-alt"></i> Grade de Horários Completa</h3>';
    
    // Tabela da Manhã
    echo '<div class="turno-horarios">';
    echo '<h4 class="titulo-turno"><i class="fas fa-sun"></i> Manhã</h4>';
    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered table-horario-completo">';
    echo '<thead><tr>';
    echo '<th class="hora-col" width="15%">Horário</th>';
    foreach ($dias as $dia) {
        echo '<th width="17%" class="text-center">' . $dia . '</th>';
    }
    echo '</tr></thead><tbody>';
    
    foreach ($aulasManha as $aula) {
        echo '<tr>';
        echo '<td class="text-center hora-celula"><strong>' . $aula['hora'] . '</strong></td>';
        
        for ($dia = 2; $dia <= 6; $dia++) {
            echo '<td class="celula-aula">';
            echo '<div class="celula-conteudo">';
            
            // Procurar disciplina para este horário e dia
            $disciplinaEncontrada = false;
            if ($horarios && is_array($horarios)) {
                foreach ($horarios as $disciplina) {
                    if (!empty($disciplina['horarios_de_aula'])) {
                        $horariosArray = parseHorario($disciplina['horarios_de_aula']);
                        foreach ($horariosArray as $h) {
                            if ($h['dia'] == $dia && $h['turno'] == $aula['turno'] && in_array($aula['codigo'], $h['aulas'])) {
                                echo '<div class="disciplina-celula">';
                                echo '<span class="disciplina-sigla">' . htmlspecialchars($disciplina['sigla']) . '</span>';
                                echo '<span class="disciplina-nome-celula">' . htmlspecialchars($disciplina['descricao']) . '</span>';
                                if (!empty($disciplina['locais_de_aula'][0])) {
                                    echo '<small class="disciplina-local">' . htmlspecialchars($disciplina['locais_de_aula'][0]) . '</small>';
                                }
                                echo '</div>';
                                $disciplinaEncontrada = true;
                                break 2;
                            }
                        }
                    }
                }
            }
            
            if (!$disciplinaEncontrada) {
                echo '<span class="text-muted">Livre</span>';
            }
            
            echo '</div>';
            echo '</td>';
        }
        echo '</tr>';
    }
    
    echo '</tbody></table>';
    echo '</div>';
    echo '</div>';
    
    // Tabela da Tarde
    echo '<div class="turno-horarios">';
    echo '<h4 class="titulo-turno"><i class="fas fa-sun"></i> Tarde</h4>';
    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered table-horario-completo">';
    echo '<thead><tr>';
    echo '<th class="hora-col" width="15%">Horário</th>';
    foreach ($dias as $dia) {
        echo '<th width="17%" class="text-center">' . $dia . '</th>';
    }
    echo '</tr></thead><tbody>';
    
    foreach ($aulasTarde as $aula) {
        echo '<tr>';
        echo '<td class="text-center hora-celula"><strong>' . $aula['hora'] . '</strong></td>';
        
        for ($dia = 2; $dia <= 6; $dia++) {
            echo '<td class="celula-aula">';
            echo '<div class="celula-conteudo">';
            
            // Procurar disciplina para este horário e dia
            $disciplinaEncontrada = false;
            if ($horarios && is_array($horarios)) {
                foreach ($horarios as $disciplina) {
                    if (!empty($disciplina['horarios_de_aula'])) {
                        $horariosArray = parseHorario($disciplina['horarios_de_aula']);
                        foreach ($horariosArray as $h) {
                            if ($h['dia'] == $dia && $h['turno'] == $aula['turno'] && in_array($aula['codigo'], $h['aulas'])) {
                                echo '<div class="disciplina-celula">';
                                echo '<span class="disciplina-sigla">' . htmlspecialchars($disciplina['sigla']) . '</span>';
                                echo '<span class="disciplina-nome-celula">' . htmlspecialchars($disciplina['descricao']) . '</span>';
                                if (!empty($disciplina['locais_de_aula'][0])) {
                                    echo '<small class="disciplina-local">' . htmlspecialchars($disciplina['locais_de_aula'][0]) . '</small>';
                                }
                                echo '</div>';
                                $disciplinaEncontrada = true;
                                break 2;
                            }
                        }
                    }
                }
            }
            
            if (!$disciplinaEncontrada) {
                echo '<span class="text-muted">Livre</span>';
            }
            
            echo '</div>';
            echo '</td>';
        }
        echo '</tr>';
    }
    
    echo '</tbody></table>';
    echo '</div>';
    echo '</div>';
    
    echo '</div>';
}

function criarCardsHorariosSemana($horarios, $boletim = null) {
    
    $dias = ['Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira'];
    $diasSuap = [2, 3, 4, 5, 6]; // Correspondência SUAP
    
    echo '<div class="horarios-semana">';
    echo '<h3 class="titulo-horarios-semana"><i class="fas fa-calendar-week"></i> Horários da Semana</h3>';
    
    echo '<div class="cards-dias-container">';
    
    $diasComAulas = 0;
    
    // Iterando pelos dias da semana
    foreach ($dias as $index => $dia) {
        $diaSuap = $diasSuap[$index];
        
        // Usar getAulasDoDia para buscar aulas de cada dia
        $aulasDoDia = getAulasDoDia($horarios, $diaSuap);
        
        if (!empty($aulasDoDia)) {
            $diasComAulas++;
            
            // Ordena as aulas por horário
            $aulasOrdenadas = ordenarAulasPorHorario($aulasDoDia);
            
            // Agrupa aulas consecutivas da mesma disciplina
            $aulasAgrupadas = agruparAulasConsecutivas($aulasOrdenadas);
            
            echo '<div class="card-dia">';
            echo '<div class="card-dia-header">';
            echo '<h4 class="dia-titulo">' . $dia . '</h4>';
            echo '<span class="aulas-count">' . count($aulasOrdenadas) . ' aula(s)</span>';
            echo '</div>';
            
            echo '<div class="aulas-dia">';
            foreach ($aulasAgrupadas as $grupo) {
                $aula = $grupo[0]; // Primeira aula do grupo
                $quantidadeAulas = count($grupo);
                
                // Encontra a disciplina correspondente no boletim para status
                $disciplinaBoletim = null;
                $podeFaltar = 'success';
                
                if ($boletim && is_array($boletim)) {
                    foreach ($boletim as $item) {
                        if (isset($aula['sigla']) && strpos($item['disciplina'], $aula['sigla']) !== false) {
                            $disciplinaBoletim = $item;
                            $podeFaltar = podeFaltarAmanha($item);
                            break;
                        }
                    }
                }
                
                // Determina status visual
                $statusClass = '';
                switch ($podeFaltar) {
                    case 'success':
                        $statusClass = 'can-skip';
                        break;
                    case 'warning':
                        $statusClass = 'be-careful';
                        break;
                    default:
                        $statusClass = 'avoid-skip';
                        break;
                }
                
                echo '<div class="aula-card-resumida ' . $statusClass . '">';
                echo '<div class="aula-info-resumida">';
                
                // Título da disciplina
                echo '<div class="aula-disciplina">';
                echo htmlspecialchars($aula['descricao'] ?? $aula['disciplina'] ?? 'Aula');
                if ($quantidadeAulas > 1) {
                    echo ' <span class="aula-count-badge">' . $quantidadeAulas . ' aulas</span>';
                }
                echo '</div>';
                
                // Horário
                if ($quantidadeAulas > 1) {
                    // Combina os horários de todas as aulas do grupo
                    $horariosGrupo = array();
                    foreach ($grupo as $aulaGrupo) {
                        if (isset($aulaGrupo['horario_detalhado'])) {
                            $horariosGrupo[] = $aulaGrupo['horario_detalhado'];
                        }
                    }
                    $horarioCombinado = implode(' + ', $horariosGrupo);
                    echo '<div class="aula-horario">' . $horarioCombinado . '</div>';
                } else {
                    if (isset($aula['horario_detalhado'])) {
                        echo '<div class="aula-horario">' . $aula['horario_detalhado'] . '</div>';
                    }
                }
                
                // Local
                if (isset($aula['locais']) && is_array($aula['locais']) && !empty($aula['locais'])) {
                    echo '<div class="aula-local"><i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars(implode(', ', $aula['locais'])) . '</div>';
                } else if (isset($aula['local']) && !empty($aula['local'])) {
                    echo '<div class="aula-local"><i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars($aula['local']) . '</div>';
                }
                
                // Status de frequência (se disponível)
                if ($disciplinaBoletim && isset($disciplinaBoletim['percentual_carga_horaria_frequentada'])) {
                    $frequencia = $disciplinaBoletim['percentual_carga_horaria_frequentada'];
                    echo '<div class="aula-frequencia">';
                    echo '<i class="fas fa-user-check"></i> ';
                    echo '<span>' . number_format($frequencia, 1) . '%</span>';
                    echo '</div>';
                }
                
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
            
            echo '</div>';
        }
    }
    
    // Se nenhum dia tem aulas, mostrar mensagem
    if ($diasComAulas === 0) {
        echo '<div class="empty-horarios">';
        echo '<i class="fas fa-calendar-times"></i>';
        echo '<h4>Nenhum horário encontrado</h4>';
        echo '<p>Não há aulas programadas para esta semana.</p>';
        echo '</div>';
    }
    
    echo '</div>';
    echo '</div>';
}
?>
