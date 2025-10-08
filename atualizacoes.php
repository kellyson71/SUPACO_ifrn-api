<?php
require_once 'config.php';
require_once 'get_changelog.php';
session_start();

$pageTitle = 'Atualizações - SUPACO';

function getGitCommits($limit = 50) {
    $commits = [];
    
    try {
        $command = "git log --oneline -{$limit} --pretty=format:\"%h|%ad|%s\" --date=short";
        $output = shell_exec($command);
        
        if ($output) {
            $lines = explode("\n", trim($output));
            foreach ($lines as $line) {
                if (empty($line)) continue;
                
                $parts = explode('|', $line, 3);
                if (count($parts) >= 3) {
                    $commits[] = [
                        'hash' => $parts[0],
                        'date' => $parts[1],
                        'message' => $parts[2]
                    ];
                }
            }
        }
    } catch (Exception $e) {
        error_log("Erro ao obter commits: " . $e->getMessage());
    }
    
    return $commits;
}

function parseVersionFromMessage($message) {
    if (preg_match('/v?(\d+)\.(\d+)/i', $message, $matches)) {
        return $matches[1] . '.' . $matches[2];
    }
    return null;
}

function categorizeCommit($message) {
    $message = strtolower($message);
    
    if (strpos($message, 'fix') !== false || strpos($message, 'corrige') !== false || strpos($message, 'correção') !== false) {
        return 'fix';
    } elseif (strpos($message, 'feat') !== false || strpos($message, 'adiciona') !== false || strpos($message, 'implementa') !== false) {
        return 'feature';
    } elseif (strpos($message, 'update') !== false || strpos($message, 'atualiza') !== false || strpos($message, 'melhora') !== false) {
        return 'improvement';
    } elseif (strpos($message, 'style') !== false || strpos($message, 'ui') !== false || strpos($message, 'interface') !== false) {
        return 'style';
    } elseif (strpos($message, 'refactor') !== false || strpos($message, 'refatora') !== false) {
        return 'refactor';
    }
    
    return 'other';
}

function getCategoryIcon($category) {
    $icons = [
        'fix' => 'fas fa-bug',
        'feature' => 'fas fa-plus-circle',
        'improvement' => 'fas fa-arrow-up',
        'style' => 'fas fa-palette',
        'refactor' => 'fas fa-code',
        'other' => 'fas fa-code-branch'
    ];
    
    return $icons[$category] ?? 'fas fa-code-branch';
}

function getCategoryColor($category) {
    $colors = [
        'fix' => 'danger',
        'feature' => 'success',
        'improvement' => 'info',
        'style' => 'warning',
        'refactor' => 'secondary',
        'other' => 'primary'
    ];
    
    return $colors[$category] ?? 'primary';
}

function formatCommitMessage($message) {
    $message = trim($message);
    
    if (preg_match('/^[a-f0-9]{7}\s+(.+)$/', $message, $matches)) {
        $message = $matches[1];
    }
    
    $message = ucfirst($message);
    
    return $message;
}

function formatDate($date) {
    $months = [
        '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
        '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
        '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro',
        '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
    ];
    
    $parts = explode('-', $date);
    if (count($parts) >= 2) {
        $year = $parts[0];
        $month = $parts[1];
        return $months[$month] . ' ' . $year;
    }
    
    return $date;
}

$commits = getGitCommits(100);
$versions = [];
$currentVersion = null;
$versionCommits = [];

foreach ($commits as $commit) {
    $version = parseVersionFromMessage($commit['message']);
    
    if ($version) {
        if ($currentVersion && !empty($versionCommits)) {
            $versions[$currentVersion] = [
                'commits' => $versionCommits,
                'date' => $versionCommits[0]['date']
            ];
        }
        $currentVersion = $version;
        $versionCommits = [];
    }
    
    if ($currentVersion) {
        $versionCommits[] = $commit;
    }
}

if ($currentVersion && !empty($versionCommits)) {
    $versions[$currentVersion] = [
        'commits' => $versionCommits,
        'date' => $versionCommits[0]['date']
    ];
}

$currentVersionNumber = '3.8';

ob_start();
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="mb-0">
                                <i class="fas fa-rocket me-2"></i>
                                Histórico de Atualizações
                            </h2>
                            <p class="mb-0 opacity-75">Acompanhe todas as melhorias e correções</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-light text-dark fs-6">v<?php echo $currentVersionNumber; ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="timeline">
                        <?php if (!empty($versions)): ?>
                            <?php 
                            $versionColors = ['primary', 'success', 'info', 'warning', 'secondary', 'dark'];
                            $versionIcons = ['fas fa-star', 'fas fa-mobile-alt', 'fas fa-palette', 'fas fa-calculator', 'fas fa-graduation-cap', 'fas fa-rocket'];
                            $colorIndex = 0;
                            $isFirst = true;
                            ?>
                            
                            <?php foreach ($versions as $versionNumber => $versionData): ?>
                                <?php 
                                $isCurrent = $versionNumber === $currentVersionNumber;
                                $color = $versionColors[$colorIndex % count($versionColors)];
                                $icon = $versionIcons[$colorIndex % count($versionIcons)];
                                $colorIndex++;
                                ?>
                                
                                <div class="timeline-item <?php echo $isCurrent ? 'current' : ''; ?>">
                                    <div class="timeline-marker bg-<?php echo $color; ?>">
                                        <i class="<?php echo $icon; ?>"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-header">
                                            <h4 class="version-title">
                                                Versão <?php echo $versionNumber; ?><?php echo $isCurrent ? ' - Atual' : ''; ?>
                                            </h4>
                                            <span class="version-date"><?php echo formatDate($versionData['date']); ?></span>
                                        </div>
                                        <div class="version-features">
                                            <?php 
                                            $commits = array_slice($versionData['commits'], 0, 8);
                                            foreach ($commits as $commit): 
                                                $category = categorizeCommit($commit['message']);
                                                $categoryIcon = getCategoryIcon($category);
                                                $categoryColor = getCategoryColor($category);
                                            ?>
                                                <div class="feature-item">
                                                    <i class="<?php echo $categoryIcon; ?> text-<?php echo $categoryColor; ?>"></i>
                                                    <span><?php echo formatCommitMessage($commit['message']); ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                            
                                            <?php if (count($versionData['commits']) > 8): ?>
                                                <div class="feature-item">
                                                    <i class="fas fa-ellipsis-h text-muted"></i>
                                                    <span class="text-muted">E mais <?php echo count($versionData['commits']) - 8; ?> alterações...</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Fallback quando não há commits -->
                            <div class="timeline-item current">
                                <div class="timeline-marker bg-primary">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <h4 class="version-title">Versão <?php echo $currentVersionNumber; ?> - Atual</h4>
                                        <span class="version-date"><?php echo date('F Y'); ?></span>
                                    </div>
                                    <div class="version-features">
                                        <div class="feature-item">
                                            <i class="fas fa-check-circle text-success"></i>
                                            <span>Sistema de changelog implementado</span>
                                        </div>
                                        <div class="feature-item">
                                            <i class="fas fa-check-circle text-success"></i>
                                            <span>Integração com histórico do Git</span>
                                        </div>
                                        <div class="feature-item">
                                            <i class="fas fa-check-circle text-success"></i>
                                            <span>Interface de atualizações moderna</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-footer bg-light">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle text-info me-2"></i>
                                <small class="text-muted">
                                    Este histórico é atualizado automaticamente com base nos commits do repositório.
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-outline-primary btn-sm" onclick="location.reload()">
                                <i class="fas fa-sync-alt me-1"></i>
                                Atualizar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding: 2rem 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 2rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #007bff, #28a745, #17a2b8, #ffc107, #6c757d, #343a40);
}

.timeline-item {
    position: relative;
    margin-bottom: 3rem;
    padding-left: 5rem;
}

.timeline-item.current {
    background: linear-gradient(135deg, rgba(0, 123, 255, 0.1), rgba(40, 167, 69, 0.1));
    border-radius: 1rem;
    padding: 1.5rem 1.5rem 1.5rem 5rem;
    margin: -1rem -1rem 2rem -1rem;
}

.timeline-marker {
    position: absolute;
    left: 1.25rem;
    top: 0.5rem;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.75rem;
    z-index: 2;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.timeline-content {
    background: white;
    border-radius: 0.75rem;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
}

.timeline-item.current .timeline-content {
    background: rgba(255, 255, 255, 0.95);
    border: 2px solid #007bff;
    box-shadow: 0 4px 20px rgba(0, 123, 255, 0.15);
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e9ecef;
}

.version-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.timeline-item.current .version-title {
    color: #007bff;
}

.version-date {
    background: #f8f9fa;
    color: #6c757d;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.version-features {
    display: grid;
    gap: 0.75rem;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
}

.feature-item:hover {
    background: #f8f9fa;
    transform: translateX(4px);
}

.feature-item i {
    font-size: 1rem;
    width: 1.25rem;
    text-align: center;
}

.feature-item span {
    font-size: 0.95rem;
    color: #495057;
    font-weight: 500;
}

@media (max-width: 768px) {
    .timeline::before {
        left: 1.5rem;
    }
    
    .timeline-item {
        padding-left: 3.5rem;
    }
    
    .timeline-item.current {
        padding-left: 3.5rem;
    }
    
    .timeline-marker {
        left: 0.75rem;
        width: 1.25rem;
        height: 1.25rem;
        font-size: 0.7rem;
    }
    
    .timeline-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .version-title {
        font-size: 1.1rem;
    }
}
</style>

<?php
$pageContent = ob_get_clean();
require_once 'base.php';
?>
