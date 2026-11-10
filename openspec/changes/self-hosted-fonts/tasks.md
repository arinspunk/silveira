# Tasks

## 1. Iconos y Google Fonts

- [ ] 1.1 Inventariar los iconos usados (`grep` de nombres en PHP/JS), generar y versionar el subconjunto `woff2` en `src/fonts/` con su `@font-face` en `_icons.scss` (design D1); verificar "Icons render" en portada, ficha y Kitchen Sink
- [ ] 1.2 Eliminar de `inc/assets.php` el encolado de Google Fonts, el `echo` de `preconnect` y `silveira_add_preconnect` (design D3); verificar el escenario "Any page" en la pestaña Red y en el código fuente
- [ ] 1.3 Documentar en el README cómo regenerar el subconjunto al añadir iconos; verificar siguiendo el procedimiento con un icono nuevo de prueba

## 2. Tipografías (pendiente de copiar los ficheros; licencias ya confirmadas)

- [ ] 2.1 Copiar a `src/fonts/` los ficheros de Isaac y Akkurat-Mono (convertirlos a `woff2` si hace falta, design D2) y anotar en el README su origen y dónde están guardadas las licencias web; verificar que solo hay `woff2` en `src/fonts/`
- [ ] 2.2 Corregir las rutas `@font-face` (un bloque por peso/estilo disponible) y añadir `font-display: swap` (design D2); verificar "Body text" y "Monospaced text" en las herramientas de desarrollo (Fuentes renderizadas) y que el build emite las fuentes con hash en `assets/`
- [ ] 2.3 Quitar de `tests/e2e/helpers.js` la excepción `KNOWN_MISSING` de los 404 de Isaac y Akkurat-Mono (añadida en `map-smoke-tests`); verificar que `npm run test:e2e` pasa sin ella

## 3. Cierre

- [ ] 3.1 Revisión visual de portada, ficha, archivo y Kitchen Sink con las nuevas fuentes (anotar cambios de métrica que afecten al layout); verificar que el commit incluye `assets/` regenerado por el hook y que la CI (incluido el smoke test de `map-smoke-tests`) pasa
