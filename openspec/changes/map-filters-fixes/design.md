# Design

## Context

Ver proposal.md. Hoy:

```
change checkbox
  -> forms.js updateVisibility (solo comarca -> localidade, display none/flex, desmarca y re-dispara change)
  -> map.js updateFilters -> renderMarkers -> syncFilterOptions (display none/flex de todo)
```
Ambos escriben `label.style.display`; el resultado depende del orden de listeners (forms.js se registra antes porque se importa antes en `main.js`). El `.c-select` es un `div` con `click`; las opciones son `<label><input type=checkbox>` (ya accesibles una vez visibles).

## Goals / Non-Goals

**Goals:** una sola función decide la visibilidad; desplegables accesibles; opciones correctas por jerarquía.

**Non-Goals:** cambiar el aspecto visual de los filtros; sincronizar filtros con la URL (posible change futuro); filtrar por eventos.

## Decisions

### D1. Opciones desde PHP
Comarcas: `get_terms( taxonomy=territorio, parent=0, hide_empty=true )` (con `hierarchical` por defecto incluye comarcas con proyectos solo en sus hijas). Localidades: `get_terms( hide_empty=true )` filtrado a `parent > 0`. Cada opción de localidade lleva `data-parent-comarca` (ya existe).

### D2. Estado y visibilidad en `map.js`
`syncFilterOptions` pasa a ser la única que toca visibilidad, usando `label.hidden`. Regla extra para localidades: si hay comarcas marcadas y la localidade no pertenece a ninguna, se desmarca (sin re-disparar `change`, se recalcula en la misma pasada). `initDependentFilters` se elimina de `forms.js`.
- Se extrae un predicado `matches(point, group)` para no repetir las cinco expresiones `some/includes` actuales.

### D3. Desplegable accesible
Marcado: el `div.c-select` mantiene su clase; dentro, la zona de etiqueta/valor/icono pasa a ser `<button type="button" class="c-select__trigger" aria-expanded="false" aria-controls="filter-comarca-panel">`; el panel `div.c-select__dropdown` recibe `id` y `role="group"` con `aria-labelledby`. JS: Enter/Espacio nativos del botón; `Escape` en el panel cierra y devuelve foco; clic fuera cierra (ya existe). Abrir el desplegable sigue disparando la expansión del mapa (comportamiento de `homepage-scroll-a11y`).
- *Alternativa*: `<details>/<summary>` — gestiona apertura nativa, pero cerrar los otros y el cierre por clic fuera requieren JS igualmente, y el estilado del marcador varía entre navegadores.

### D4. Estado vacío
Si `filteredPoints.length === 0`, `renderMarkers` añade un `<li class="c-project-list__empty">` con el texto (traducible; el texto final lo fija `i18n-reintegrado`).

### D5. Términos con caché
En `silveira_get_map_points()`: `get_the_terms()` (usa caché de objeto cargada por `WP_Query` con `update_post_term_cache`) en lugar de `wp_get_post_terms()`; `get_term()` del padre usa la caché de términos.

## Risks / Trade-offs

- [Orden de los puntos no determinista] → `silveira_get_map_points()` no fija orden secundario: los proyectos con la misma fecha (p. ej. los del seed, creados en el mismo segundo) salen en un orden que MySQL puede variar según el contenido de la tabla, y el smoke test pulsa el "primer" marcador, que a veces queda tapado por otro (falló al añadir 12 recursos en local durante `theme-robustness`). Se fija el orden (tarea 1.3) y el test pulsa un marcador concreto (tarea 4.2).
- [Cambiar `div` por `button` altera estilos del `.c-select`] → resetear estilos del botón en `_forms.scss` y comparar con Kitchen Sink.
- [El clic en `.c-select` que expande el mapa (map.js) puede romperse al mover el listener] → mantener el listener en el contenedor `.c-select`; verificar en tarea.
