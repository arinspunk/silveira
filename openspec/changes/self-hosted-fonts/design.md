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

### D2. Tipografías propias
Ficheros en `src/fonts/` referenciados con ruta relativa desde el SCSS (`../../fonts/…`) para que Vite los procese, les dé hash y los emita en `assets/`. Un `@font-face` por peso/estilo disponible, con `font-display: swap`. Preload opcional de Isaac Regular desde PHP leyendo el manifest.
- Si los ficheros recibidos no son `woff2` (p. ej. `otf`/`ttf`), se convierten con `fonttools` (`pyftsubset --flavor=woff2` o `woff2_compress`) siempre que la licencia permita la conversión.
- Las licencias no se versionan si son confidenciales; el README indica dónde están guardadas.

### D3. Sin Google Fonts
Se eliminan de `inc/assets.php` el `wp_enqueue_style( 'silveira-google-icons', … )`, el `echo` de `preconnect` y `silveira_add_preconnect`. Si el fichero queda vacío, se elimina y se quita su `require`.

## Risks / Trade-offs

- [Los ficheros de tipografía tardan en copiarse] → el grupo de iconos se aplica igualmente; el change no se archiva hasta tener las tipografías (es solo copiar ficheros, la licencia ya está resuelta).
- [El subconjunto de iconos se queda corto] → ver D1.
