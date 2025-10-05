# Guia de Estilo de Código - SUPACO

## PHP

### Estrutura
```php
<?php
/**
 * Descrição clara da função/classe
 * 
 * @param string $parametro Descrição do parâmetro
 * @return array Dados retornados
 */
function nomeFuncao($parametro) {
    // Lógica aqui
    return $resultado;
}
```

### Convenções
- **Nomes de variáveis**: camelCase (`$nomeUsuario`)
- **Nomes de funções**: camelCase (`obterDadosSuap()`)
- **Nomes de classes**: PascalCase (`ClasseExemplo`)
- **Constantes**: UPPER_SNAKE_CASE (`SUAP_CLIENT_ID`)

### Comentários
- Use comentários em português
- Explique o "porquê", não o "como"
- Documente funções públicas

## CSS

### Estrutura
```css
/* Componente: Nome do componente */
.componente {
    /* Propriedades organizadas */
    display: flex;
    color: var(--text-white);
}

/* Modificadores */
.componente--variante {
    background: var(--bg-primary);
}

/* Estados */
.componente:hover {
    transform: scale(1.05);
}
```

### Convenções
- **Classes**: BEM methodology (`bloco__elemento--modificador`)
- **Variáveis CSS**: kebab-case (`--bg-primary`)
- **Organização**: Por componente, depois utilitários

## JavaScript

### Estrutura
```javascript
/**
 * Descrição da função
 * @param {string} parametro - Descrição
 * @returns {Promise<Object>} Resultado
 */
async function nomeFuncao(parametro) {
    try {
        // Lógica aqui
        return resultado;
    } catch (error) {
        console.error('Erro:', error);
        throw error;
    }
}
```

### Convenções
- **Variáveis**: camelCase (`nomeUsuario`)
- **Funções**: camelCase (`obterDados()`)
- **Constantes**: UPPER_SNAKE_CASE (`API_URL`)
- **Async/Await**: Preferir sobre Promises

## HTML

### Estrutura
```html
<!-- Seção: Nome da seção -->
<section class="secao">
    <div class="container">
        <!-- Conteúdo -->
    </div>
</section>
```

### Convenções
- **Indentação**: 4 espaços
- **Atributos**: Ordem alfabética
- **Comentários**: Em português para seções importantes

## Organização de Arquivos

### Assets
- **CSS**: `assets/css/` por funcionalidade
- **JS**: `assets/js/` por módulo
- **Images**: `assets/images/` por tipo

### PHP
- **Core**: Arquivos principais na raiz
- **Utilities**: Funções auxiliares em `api_utils.php`
- **Templates**: `base*.php` para layouts

## Boas Práticas

1. **Semantic Versioning**: Para releases
2. **Git Commits**: Mensagens claras em português
3. **Error Handling**: Try-catch em operações críticas
4. **Performance**: Lazy loading e cache
5. **Security**: Validação de entrada e sanitização
6. **Accessibility**: ARIA labels e contraste adequado
