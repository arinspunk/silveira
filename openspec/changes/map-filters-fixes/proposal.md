# Proposal

## Why

Los filtros del mapa de la portada tienen varios fallos:

- **Comarcas en la lista de localidades:** el filtro "Localidade" incluye comarcas sin localidades hijas, porque usa `childless`.
- **Dos scripts deciden qué opciones se ven:** `forms.js` y `map.js` cambian a la vez `style.display` de las mismas opciones, con reglas distintas.
- **Desplegables inaccesibles con teclado:** son `div` sin foco, sin rol ni `aria-expanded`.
- **Sin estado vacío:** cuando una combinación de filtros no devuelve proyectos, la lista lateral queda en blanco sin explicación.

A esto se suman código muerto y consultas de términos sin caché por cada proyecto.

## What Changes

- **Opciones de cada filtro:**
  - "Localidade" solo lista territorios con comarca padre;
  - "Comarca" solo lista territorios de primer nivel.
- **Un único responsable de mostrar u ocultar opciones** (la lógica de filtrado del mapa), usando el atributo `hidden` en vez de estilos en línea. Una localidad marcada que deja de ser compatible con las comarcas elegidas se desmarca.
- **Desplegables operables con teclado y lector de pantalla:**
  - se abren con un botón con `aria-expanded`;
  - `Escape` los cierra;
  - el foco va a la primera opción.
- **Mensaje cuando no hay resultados** en la lista lateral.
- **Limpieza:** se elimina el código muerto (`matchesM/C/L`, `$territorios`) y se usan las funciones de términos con caché al generar los puntos.

## Capabilities

### New Capabilities
- `project-map-filters`: filtrado de los proyectos del mapa por modalidade, comarca y localidade — qué opciones se ofrecen, cómo se combinan y cómo se interactúa con ellas.

### Modified Capabilities
<!-- Ninguna -->

## Impact

- `front-page.php` (marcado de filtros) o el fichero de `inc/` que lo contenga tras `theme-robustness`; `inc/map-data.php` (de `harden-map-output`).
- `src/js/forms.js`, `src/js/map.js`, `src/scss/6-components/_forms.scss` / `_filter-bar.scss` (estado de foco y `hidden`).
- Rebuild de `assets/`.
- Depende de `harden-map-output` (estructura de `map.js` y `inc/map-data.php`).
