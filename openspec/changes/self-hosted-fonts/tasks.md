# Tasks

## 1. Iconos y Google Fonts

- [ ] 1.1 Inventariar los iconos usados (`grep` de nombres en PHP/JS), generar y versionar el subconjunto `woff2` en `src/fonts/` con su `@font-face` en `_icons.scss` (design D1); verificar "Icons render" en portada, ficha y Kitchen Sink
- [ ] 1.2 Eliminar de `inc/assets.php` el encolado de Google Fonts, el `echo` de `preconnect` y `silveira_add_preconnect` (design D3); verificar el escenario "Any page" en la pestaña Red y en el código fuente
- [ ] 1.3 Documentar en el README cómo regenerar el subconjunto al añadir iconos; verificar siguiendo el procedimiento con un icono nuevo de prueba

## 2. Tipografías

- [ ] 2.1 Convertir a `woff2` Isaac Regular, Regular Italic, Bold y Bold Italic desde los `otf` (design D2) y dejar en `src/fonts/` solo esos cuatro `woff2` (los `otf` fuera del repo); verificar con `fonttools` que cada `woff2` conserva peso, estilo y número de glifos del `otf` y que `git status` no muestra ningún `otf`
- [ ] 2.2 Escribir los cuatro `@font-face` de Isaac con rutas a `src/fonts/` y `font-display: swap`, y eliminar Akkurat-Mono (`@font-face`, `$font-mono`, mixin `font-mono`, `--sil-font-mono`) (design D2, D2b); verificar "Body text" y "Emphasis" (fuentes renderizadas en Chrome) y que el build emite las cuatro fuentes con hash en `assets/` y ninguna referencia a Akkurat
- [ ] 2.3 Quitar de `tests/e2e/helpers.js` la excepción `KNOWN_MISSING` de los 404 de fuentes (añadida en `map-smoke-tests`); verificar que `npm run test:e2e` pasa sin ella
- [ ] 2.4 Documentar en el README qué estilos de Isaac se incluyen y cómo añadir otro (convertir el `otf` a `woff2` y añadir su `@font-face`)

## 3. Cierre

- [ ] 3.1 Revisión visual de portada, ficha, archivo y Kitchen Sink con las nuevas fuentes (anotar cambios de métrica que afecten al layout); verificar que el commit incluye `assets/` regenerado por el hook y que la CI (incluido el smoke test de `map-smoke-tests`) pasa
