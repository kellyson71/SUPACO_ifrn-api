<!-- Lista de aulas do dia com indicadores visuais -->
<div class="aulas-list">
    <?php foreach ($aulasOrdenadas as $aula): ?>
        <?php
        // Encontra a disciplina correspondente no boletim para calcular frequência
        $disciplinaBoletim = null;
        $impactoFalta = null;
        $podeFaltar = 'danger';

        if (isset($boletim)) {
            foreach ($boletim as $item) {
                if (isset($aula['sigla']) && strpos($item['disciplina'], $aula['sigla']) !== false) {
                    $disciplinaBoletim = $item;
                    $impactoFalta = calcularImpactoFalta($item);
                    $podeFaltar = podeFaltarAmanha($item);
                    break;
                }
            }
        }

        // Obter informações da imagem de status
        $statusInfo = getStatusFaltaImagem($podeFaltar);
        ?>

        <div class="aula-item mb-3 p-3 border rounded-3 shadow-sm bg-white">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex align-items-center">
                    <div class="status-image-container me-3">
                        <img src="<?php echo $statusInfo['imagem']; ?>" alt="Status de falta"
                            class="status-image" style="width: 50px; height: 50px; object-fit: cover;">
                    </div>
                    <div>
                        <h5 class="mb-1"><?php echo isset($aula['descricao']) ? htmlspecialchars($aula['descricao']) : htmlspecialchars($aula['disciplina'] ?? 'Aula'); ?></h5>
                        <div class="aula-details">
                            <span class="badge bg-primary me-2">
                                <i class="fas fa-clock me-1"></i> <?php echo $aula['horario']['turno']; ?><?php echo implode(',', $aula['horario']['aulas']); ?>
                                (<?php echo isset($aula['horario_detalhado']) ? $aula['horario_detalhado'] : ''; ?>)
                            </span>
                            <span class="badge bg-secondary me-2">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                <?php
                                if (isset($aula['locais']) && is_array($aula['locais']) && !empty($aula['locais'])) {
                                    echo htmlspecialchars(implode(', ', $aula['locais']));
                                } else if (isset($aula['local']) && !empty($aula['local'])) {
                                    echo htmlspecialchars($aula['local']);
                                } else {
                                    echo 'Local não definido';
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="ms-2">
                    <span class="badge <?php echo $statusInfo['classe']; ?>">
                        <i class="fas <?php echo $statusInfo['icone']; ?> me-1"></i>
                        <?php echo $statusInfo['descricao']; ?>
                    </span>
                </div>
            </div>

            <?php if ($impactoFalta && isset($impactoFalta['faltas_restantes'])): ?>
                <div class="mt-2 small">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted">Faltas: <?php echo $impactoFalta['faltas_atuais']; ?> de <?php echo $impactoFalta['maximo_faltas']; ?> permitidas</span>
                        <span class="text-<?php echo $podeFaltar; ?>"><?php echo $impactoFalta['faltas_restantes']; ?> faltas restantes</span>
                    </div>
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar bg-<?php echo $podeFaltar; ?>" role="progressbar" style="width: <?php echo $impactoFalta['proporcao_faltas']; ?>%"></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>