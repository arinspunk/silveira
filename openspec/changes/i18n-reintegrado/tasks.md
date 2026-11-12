# Tasks

## 1. Infraestructura

- [ ] 1.1 Añadir `load_theme_textdomain( 'silveira', ... )` en el setup del theme (design D1); verificar con un `.mo` de prueba en `languages/` que sobrescribe un texto del footer y retirarlo después
- [ ] 1.2 Inyectar `window.silveiraI18n` junto a los puntos del mapa y hacer que `map.js` lea de él todos sus textos (design D2); verificar con `grep -n "Ver \|Acochar" src/js/map.js` que no quedan literales salvo fallbacks y el escenario "Translation file overrides a string"

## 2. Títulos

- [ ] 2.1 Comprobar que el título de archivo sin prefijo de `consistent-templates` (design D3) se mantiene con WordPress en galego; verificar el escenario "Territory archive" y el archivo de proyectos
- [ ] 2.2 Corregir el escapado del título de búsqueda (design D4); verificar el escenario "Search with ampersand"

## 3. Textos

- [ ] 3.1 Generar el inventario con `wp i18n make-pot` fuera del repo y reescribir en la variante "umha"/"-çom"/"nom" todos los textos de CPTs, taxonomías, menús, `aria-label`, plantillas, hero y JS (design D5); verificar con `grep -rniE "não|ção\b|uma\b|no hay|pueda|ayudar|Main (Menu|navigation)|Footer navigation" --include=*.php --include=*.js` sobre el theme (excluyendo `vendor/`, `node_modules/`, `assets/` y la Kitchen Sink) que no quedan formas ajenas
- [ ] 3.2 Revisar los escenarios de "Public interface text…" y "Admin labels…" navegando portada, archivos, fichas, búsqueda sin resultados y pantallas de admin de proyectos/eventos/recursos/territorios
- [ ] 3.3 Guardar el inventario final de textos (con los dudosos marcados) en un lugar accesible para la revisión lingüística posterior; verificar que el enlace queda anotado en la PR

## 4. Build

- [ ] 4.1 Verificar que el commit incluye `assets/` regenerado por el hook y que la CI (incluido el smoke test de `map-smoke-tests`) pasa; verificar el escenario "Map list toggle" con `SILVEIRA_VITE_DEV=0`

## Workflow follow-up

- Revisión lingüística (no bloqueante): enviar el inventario a una persona con dominio de la variante cuando haya disponibilidad y abrir un change de seguimiento con sus correcciones.
