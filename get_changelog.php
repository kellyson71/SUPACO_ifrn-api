<?php

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

function generateChangelogData() {
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
    
    return $versions;
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

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_changelog') {
    header('Content-Type: application/json');
    
    $changelog = generateChangelogData();
    
    echo json_encode([
        'success' => true,
        'versions' => $changelog,
        'total_versions' => count($changelog)
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

?>
