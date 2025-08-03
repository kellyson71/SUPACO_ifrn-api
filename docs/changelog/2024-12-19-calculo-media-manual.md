# Cálculo Manual da Média Geral

**Data:** 2024-12-19  
**Tipo:** Melhoria  
**Arquivo:** `index.php`

## Mudanças Realizadas

### Cálculo Manual da Média
- **Antes:** A média geral era calculada usando apenas o campo `media_final_disciplina` de cada disciplina
- **Depois:** Implementado cálculo manual que coleta todas as notas disponíveis de cada disciplina

### Detalhes da Implementação

1. **Coleta de Notas por Disciplina:**
   - Verifica notas do primeiro semestre (etapas 1-4)
   - Verifica notas do segundo semestre (etapas 1-4)
   - Se não encontrar notas específicas, usa a média final da disciplina como fallback

2. **Cálculo da Média:**
   - Para cada disciplina, calcula a média aritmética de todas as notas disponíveis
   - Soma todas as médias das disciplinas
   - Divide pelo número total de disciplinas com notas

3. **Validação de Dados:**
   - Verifica se as notas não são null ou vazias
   - Converte valores para float para garantir precisão
   - Ignora disciplinas sem notas disponíveis

## Impacto

- **Mais Precisão:** O cálculo agora considera todas as notas individuais em vez de apenas a média final
- **Melhor Representação:** A média geral reflete melhor o desempenho real do aluno
- **Compatibilidade:** Mantém fallback para casos onde apenas a média final está disponível

## Código Modificado

```php
// Antes (linhas 932-933):
if (isset($disciplina['media_final_disciplina']) && $disciplina['media_final_disciplina'] !== null) {
    $somaNotas += $disciplina['media_final_disciplina'];
    $countNotas++;
}

// Depois (linhas 932-970):
// Calcular média manualmente pegando todas as notas disponíveis
$notasDisciplina = array();

// Verificar notas do primeiro semestre
if (isset($disciplina['primeiro_semestre'])) {
    for ($i = 1; $i <= 4; $i++) {
        $notaKey = "nota_etapa_{$i}";
        if (isset($disciplina['primeiro_semestre'][$notaKey]['nota']) && 
            $disciplina['primeiro_semestre'][$notaKey]['nota'] !== null && 
            $disciplina['primeiro_semestre'][$notaKey]['nota'] !== '') {
            $notasDisciplina[] = floatval($disciplina['primeiro_semestre'][$notaKey]['nota']);
        }
    }
}

// Verificar notas do segundo semestre
if (isset($disciplina['segundo_semestre'])) {
    for ($i = 1; $i <= 4; $i++) {
        $notaKey = "nota_etapa_{$i}";
        if (isset($disciplina['segundo_semestre'][$notaKey]['nota']) && 
            $disciplina['segundo_semestre'][$notaKey]['nota'] !== null && 
            $disciplina['segundo_semestre'][$notaKey]['nota'] !== '') {
            $notasDisciplina[] = floatval($disciplina['segundo_semestre'][$notaKey]['nota']);
        }
    }
}

// Se não encontrou notas específicas, usar a média final da disciplina
if (empty($notasDisciplina) && isset($disciplina['media_final_disciplina']) && $disciplina['media_final_disciplina'] !== null) {
    $notasDisciplina[] = $disciplina['media_final_disciplina'];
}

// Calcular média da disciplina se houver notas
if (!empty($notasDisciplina)) {
    $mediaDisciplina = array_sum($notasDisciplina) / count($notasDisciplina);
    $somaNotas += $mediaDisciplina;
    $countNotas++;
}
``` 