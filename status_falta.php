<?php

/**
 * Determina a imagem a ser exibida com base no status de falta
 * 
 * @param string $podeFaltar Status da possibilidade de faltar ('success', 'warning' ou 'danger')
 * @return array Array associativo com caminho da imagem, classe CSS e descrição
 */
function getStatusFaltaImagem($podeFaltar)
{
    switch ($podeFaltar) {
        case 'success':
            return [
                'imagem' => 'assets/tranquilo.png',
                'classe' => 'bg-success',
                'descricao' => 'Pode faltar com tranquilidade',
                'icone' => 'fa-check-circle',
                'color' => '#28a745',
                'detalhes' => 'Você ainda tem faltas disponíveis nesta disciplina.'
            ];
        case 'warning':
            return [
                'imagem' => 'assets/mais ou menos.png',
                'classe' => 'bg-warning',
                'descricao' => 'Fique atento às faltas',
                'icone' => 'fa-exclamation-triangle',
                'color' => '#ffc107',
                'detalhes' => 'Você está chegando no limite de faltas permitidas.'
            ];
        case 'danger':
        default:
            return [
                'imagem' => 'assets/perigo.png',
                'classe' => 'bg-danger',
                'descricao' => 'Evite faltar',
                'icone' => 'fa-times-circle',
                'color' => '#dc3545',
                'detalhes' => 'Você está próximo de ser reprovado por faltas!'
            ];
    }
}
