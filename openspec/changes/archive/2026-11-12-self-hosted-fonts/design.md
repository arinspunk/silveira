# Design

## Context

Ver proposal.md. `inc/assets.php` encola Material Symbols (fuente variable completa) desde Google Fonts en todas las páginas e imprime los `preconnect` dos veces (con `echo` en `wp_enqueue_scripts` y con `silveira_add_preconnect` en `wp_head`). `_typography.scss` declara `url("../../assets/fonts/Isaac-Regular.woff2")` y `Akkurat-Mono.woff2`, rutas que no existen (y `assets/` se vacía en cada build por `emptyOutDir`). Vite copia `public/` a `assets/`.

## Goals / Non-Goals

**Goals:** cero peticiones a servicios de fuentes de terceros; iconos y tipografías del diseño servidos desde el sitio.

**Non-Goals:** Leaflet y teselas (`third-party-assets`); cambiar el conjunto de iconos o el diseño tipográfico.

## Decisions

### D1. Material Symbols subconjunto
Descargar una vez `https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=<lista ordenada>` y su `woff2`, guardarlo en `src/fonts/material-symbols-subset.woff2` con un `@font-face` en `_icons.scss`. La lista de iconos se obtiene con un `grep` de nombres usados en PHP/JS y se documenta en un comentario y en el README (procedimiento para regenerar al añadir iconos).
- *Alternativa*: paquete npm `material-symbols` — incluye la fuente completa (~3 MB por estilo).
- Riesgo de olvido al añadir un icono nuevo → la Kitchen Sink muestra todos los iconos usados; un icono no incluido se ve como texto, fácil de detectar.

### D2. Isaac: cuatro estilos en `woff2`
La familia se recibe en `otf` (16 estilos, de Thin a ExtraBold). El CSS solo usa 400, 400 cursiva (lema de la ficha, `<em>`), 700 (etiquetas, `<strong>`) y, en un único sitio, 500, que no existe en la familia y el navegador resuelve con la de 400. Se incluyen solo **Regular, Regular Italic, Bold y Bold Italic** (`IsaacRegular*.otf`, `IsaacBold*.otf`; *Book*, también de peso 400, no se usa).
- Conversión con `fonttools` (Docker `python:3.12-slim`, `pyftsubset --flavor=woff2`), conservando todos los glifos (sin subconjunto: los textos en galego reintegrado necesitan los diacríticos) y quitando solo tablas de hinting innecesarias en web si reducen el tamaño sin cambiar el render.
- Nombres: `src/fonts/Isaac-Regular.woff2`, `Isaac-Italic.woff2`, `Isaac-Bold.woff2`, `Isaac-BoldItalic.woff2`, referenciados con ruta relativa desde el SCSS (`../../fonts/…`) para que Vite los procese, les dé hash y los emita en `assets/`. Un `@font-face` por fichero, misma `font-family: Isaac`, con `font-weight`/`font-style` y `font-display: swap`.
- Los `otf` originales no se versionan: en `src/fonts/` solo quedan `woff2`.
- Preload de Isaac Regular desde PHP leyendo el manifest: opcional, solo si la revisión visual muestra un salto de fuente molesto.

### D2c. Rutas de las fuentes en el build (añadido al aplicar)
El `base` de Vite era `/wp-content/themes/silveira/`, sin `assets/`: hasta ahora no importaba porque el CSS no tenía `url()` y el PHP lee el manifest, pero las fuentes salían con 404. El build pasa a usar `base: './'` (rutas relativas: `assets/css/` → `../fonts/…`, independiente de la ruta de instalación) y el modo dev mantiene la ruta del theme, que usa `functions.php`. Los `url()` del SCSS son absolutos desde la raíz del proyecto (`/src/fonts/…`), porque Vite no reescribe las rutas relativas de los parciales de Sass. Las fuentes se emiten en `assets/fonts/` en lugar de `assets/css/`. El contenido del JS generado no cambia.

Material Symbols usa `font-display: block` (no `swap`): con `swap` se verían los nombres de los iconos como texto mientras carga la fuente.

### D2b. Sin Akkurat-Mono
Ningún componente la usa y el diseño no la va a usar. Se eliminan su `@font-face`, `$font-mono`, el mixin `font-mono` y `--sil-font-mono`, y su entrada en `KNOWN_MISSING` de `tests/e2e/helpers.js`.

### D3. Sin Google Fonts
Se eliminan de `inc/assets.php` el `wp_enqueue_style( 'silveira-google-icons', … )`, el `echo` de `preconnect` y `silveira_add_preconnect`. Si el fichero queda vacío, se elimina y se quita su `require`.

## Risks / Trade-offs

- [Cambio de métricas al pasar de la fuente de reserva a Isaac] → puede mover saltos de línea y alturas; se revisa visualmente en 3.1.
- [El subconjunto de iconos se queda corto] → ver D1.
