# Design

## Context

Ver proposal.md (Why). Estado actual relevante:

```
front-page.php (tras get_header)
  WP_Query projeto -> $map_points[] (get_the_title, get_the_excerpt, get_field...)
  <script> var silveiraMapPoints = json_encode($map_points) </script>   <- en el <body>
        |
        v
map.js (módulo ES "silveira-main", cargado en footer)
  L.marker().bindPopup(`...${point.title}...`)        <- string HTML
  li.innerHTML = `...${point.title}${point.lema}...`  <- string HTML
```

- `get_the_title()` devuelve HTML texturizado (`&#8217;`, `&amp;`), por lo que cambiar a `textContent` sin normalizar en PHP mostraría entidades literales.
- `silveira-main` existe como handle tanto en modo dev (`silveira_enqueue_vite_dev`) como en prod (`silveira_enqueue_vite_prod`), siempre con `in_footer = true`.
- No hay tests automatizados de JS ni PHP en el repo; la verificación es lint + comprobación manual en el entorno Docker.

## Goals / Non-Goals

**Goals:**
- Que ningún dato editable de un proyecto pueda convertirse en HTML/JS ejecutable en la portada.
- Mantener idéntico el aspecto visual de popup y lista (mismas clases BEM y estructura).
- Dejar `assets/` sincronizado con `src/`.

**Non-Goals:**
- Reescribir la lógica de filtros, el scroll-jacking o el footer.
- Optimizar las consultas de términos (N+1) más allá de mover el código tal cual.
- Añadir CI o hooks que verifiquen el build.
- Content Security Policy.

## Decisions

### D1. Entrega de datos con `wp_add_inline_script` en lugar de `<script>` en la plantilla
Una nueva función `silveira_get_map_points()` en `inc/map-data.php` contiene la consulta y normalización que hoy vive en `front-page.php`. Un callback en `wp_enqueue_scripts` (prioridad posterior a `silveira_enqueue_vite_assets`, p. ej. 30), solo si `is_front_page()`, hace:

```php
wp_add_inline_script(
    'silveira-main',
    'window.silveiraMapPoints = ' . wp_json_encode( $points, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ) . ';',
    'before'
);
```

- `JSON_HEX_TAG` convierte `<` y `>` en sus escapes Unicode (`\u003C` y `\u003E`), imposibilitando cerrar el `<script>`.
- Liga los datos al script que los consume y saca lógica de la plantilla.
- *Alternativas*: `wp_localize_script` (pensado para i18n, convierte escalares a string y anida bajo un objeto; no aporta nada); endpoint REST (petición extra y más superficie; innecesario para ~decenas de puntos); mantener el `<script>` en la plantilla con `wp_json_encode` (arregla el XSS pero deja la lógica en la vista).
- El script inline "before" es clásico (no módulo); se ejecuta antes que el módulo diferido, así que `window.silveiraMapPoints` está disponible cuando `map.js` lo lee en `DOMContentLoaded`.

### D2. Normalizar a texto plano en PHP
Para cada campo de texto (`title`, `lema`, `excerpt`, `overline`): `html_entity_decode( wp_strip_all_tags( $value ), ENT_QUOTES | ENT_HTML5, 'UTF-8' )`. URLs (`url`, `image`): `esc_url_raw()` (descarta esquemas no permitidos; devuelve `''` si no es válida). El contrato PHP→JS pasa a ser "texto plano y URLs crudas", y el escape es responsabilidad exclusiva de quien pinta.
- *Alternativa*: decodificar entidades en JS (con un `<textarea>` o `DOMParser`) — mueve lógica de contenido al cliente y es fácil de olvidar.

### D3. Construir el DOM con nodos, no con strings
En `map.js` se añade un pequeño helper `el(tag, { className, attrs }, children)` que usa `document.createElement`, `textContent` para strings y `setAttribute` para atributos. `buildPopup(point)` devuelve un `HTMLElement` (Leaflet `bindPopup` acepta elementos) y `buildListItem(point)` devuelve el `<li>`.
- *Alternativa*: función `escapeHtml()` aplicada a cada interpolación de los template literals. Diff menor, pero cada nueva interpolación es un posible olvido; el helper hace el camino seguro el único camino.
- Los `innerHTML` restantes con contenido estático (texto del botón "Ver a lista"/"Acochar a lista", icono del marcador) no usan datos de proyecto y se dejan como están.

### D4. Guardia de URL en el cliente (defensa en profundidad)
`safeUrl(value)` usa `new URL(value, window.location.origin)` y devuelve la URL solo si `protocol` es `http:` o `https:`; si no, `null` y no se renderiza el enlace/imagen. Cubre el caso de que los datos lleguen de otra fuente en el futuro.

### D5. Regenerar y versionar el build
Tras los cambios, `npm run build` (que ejecuta `validate` vía `prebuild`) en el theme y se commitean `assets/js`, `assets/css` y `assets/.vite/manifest.json`. Los ficheros con hash antiguo se eliminan (Vite usa `emptyOutDir: true`).

## Risks / Trade-offs

- [El hash de CSS/JS cambia y quedan cacheados ficheros viejos en CDN/navegador] → los nombres con hash y la versión por `filemtime` ya invalidan; no requiere acción.
- [Si el manifest falta en prod, `silveira-main` no se encola y el inline script se descarta silenciosamente] → mismo comportamiento que hoy (sin JS no hay mapa); se documenta.
- [`html_entity_decode` + `textContent` puede mostrar caracteres que antes se veían "raros" de otra forma] → es el comportamiento deseado; se valida con el escenario de apóstrofo/ampersand.
- [El build incorpora también cambios de `src/` no revisados desde abril (p. ej. `stacked-card`)] → revisar visualmente portada, ficha de proyecto y kitchen sink tras el build.

## Migration Plan

Despliegue normal del theme (sin cambios de BD). Rollback: revertir el commit; los assets anteriores vuelven con él.
