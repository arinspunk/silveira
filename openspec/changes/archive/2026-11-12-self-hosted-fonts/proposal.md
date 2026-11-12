# Proposal

## Why

Todas las páginas cargan Material Symbols desde Google Fonts. Eso transfiere la IP de cada visitante a Google, con implicaciones de RGPD y precedente judicial en la UE, y además añade dos `preconnect` que se imprimen dos veces (uno con `echo` dentro de `wp_enqueue_scripts`).

La tipografía del diseño, **Isaac**, se declara en `_typography.scss` con una ruta a `assets/fonts/` que no existe, así que el sitio se muestra siempre con la fuente de reserva. También se declara **Akkurat-Mono**, que ningún componente usa y el diseño no va a usar: solo genera un 404.

Este problema es independiente del mapa, así que se separa de `third-party-assets` y se aplica pronto.

## What Changes

- **Material Symbols servido desde el theme:** un subconjunto `woff2` con los iconos usados, generado con el parámetro `icon_names` de Google Fonts y versionado en `src/fonts/`.
- **Sin Google Fonts:** se elimina el encolado de la hoja de Google, los `preconnect` (el `echo` y `silveira_add_preconnect`) y cualquier petición a `fonts.googleapis.com`/`fonts.gstatic.com`.
- **Isaac servida desde el theme**: Regular, Italic, Bold y Bold Italic (los estilos que usa el CSS) en `woff2`, en `src/fonts/` con rutas que Vite resuelve y emite con hash, y `font-display: swap`.
- **Sin Akkurat-Mono**: se eliminan su `@font-face`, `$font-mono`, el mixin `font-mono` y `--sil-font-mono`.

## Capabilities

### New Capabilities
- `external-resources`: qué recursos de terceros puede pedir el navegador al visitar el sitio y cómo se sirven los recursos propios. Este change cubre iconos y tipografías; `third-party-assets` añade después librerías y teselas.

### Modified Capabilities
<!-- Ninguna -->

## Impact

- **Ficheros nuevos:** `src/fonts/material-symbols-subset.woff2`, `src/fonts/Isaac-{Regular,Italic,Bold,BoldItalic}.woff2`.
- **Código:** `inc/assets.php` (sin Google Fonts ni `preconnect`), `src/scss/1-settings/_typography.scss`, `_tokens.scss`, `2-tools/_mixins.scss`, `src/scss/4-elements/_icons.scss`, `tests/e2e/helpers.js`.
- **Documentación:** README (cómo regenerar el subconjunto de iconos y qué estilos de Isaac se incluyen).
- `assets/` lo regenera el hook de `enforce-build-and-lint`.
- **Orden recomendado:** después de `map-smoke-tests` y antes de `theme-robustness`, que ya no tiene que tratar los `preconnect`.
