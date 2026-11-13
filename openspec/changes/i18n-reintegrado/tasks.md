# Tasks

## 1. Infraestructura

- [x] 1.1 Añadir `load_theme_textdomain( 'silveira', ... )` en el setup del theme (design D1); verificar con un `.mo` de prueba en `languages/` que sobrescribe un texto del footer y retirarlo después
- [x] 1.2 Inyectar `window.silveiraI18n` junto a los puntos del mapa y hacer que `map.js` lea de él todos sus textos (design D2); verificar con `grep -n "Ver \|Acochar" src/js/map.js` que no quedan literales salvo fallbacks y el escenario "Translation file overrides a string"

## 2. Títulos

- [x] 2.1 Comprobar que el título de archivo sin prefijo de `consistent-templates` (design D3) se mantiene con WordPress en galego; verificar el escenario "Territory archive" y el archivo de proyectos
- [x] 2.2 Corregir el escapado del título de búsqueda (design D4); verificar el escenario "Search with ampersand"

## 3. Textos

- [x] 3.1 Generar el inventario con `wp i18n make-pot` fuera del repo y reescribir en la variante "umha"/"-çom"/"nom" todos los textos de CPTs, taxonomías, menús, `aria-label`, plantillas, hero y JS (design D5); verificar con `grep -rniE "não|ção\b|uma\b|no hay|pueda|ayudar|Main (Menu|navigation)|Footer navigation" --include=*.php --include=*.js` sobre el theme (excluyendo `vendor/`, `node_modules/`, `assets/` y la Kitchen Sink) que no quedan formas ajenas
- [x] 3.2 Revisar los escenarios de "Public interface text…" y "Admin labels…" navegando portada, archivos, fichas, búsqueda sin resultados y pantallas de admin de proyectos/eventos/recursos/territorios
- [x] 3.3 Guardar el inventario final de textos (con los dudosos marcados) en un lugar accesible para la revisión lingüística posterior; verificar que el enlace queda anotado en la PR

## 4. Accesibilidad de los filtros

- [x] 4.1 Dar a los botones de filtro el nombre accesible de design D6 (`aria-labelledby` con etiqueta + estado, `.screen-reader-text`, actualización en `map.js`, textos con `_n` vía `silveiraI18n`); verificar en el árbol de accesibilidad de Chrome que el nombre es "Modalidade educativa" sin nada marcado, "Modalidade educativa, 1 selecionada" con una y "…, 2 selecionadas" con dos, que se actualiza al desmarcar una localidade incompatible, y que el aspecto de la barra no cambia
- [x] 4.2 Añadir al smoke test una comprobación del nombre accesible del botón de Modalidade antes y después de marcar una opción (`getByRole('button', { name: … })`); verificar que pasa en local y en la CI
- [ ] 4.3 Comprobar con VoiceOver (Chrome, Cmd+F5) que se anuncia la etiqueta con el número de seleccionadas y sin "Que procuras?" (lo hace el propietario del repo)

## 5. Build

- [x] 5.1 Verificar que el commit incluye `assets/` regenerado por el hook y que la CI (incluido el smoke test de `map-smoke-tests`) pasa; verificar el escenario "Map list toggle" con `SILVEIRA_VITE_DEV=0`

## Workflow follow-up

- Revisión lingüística (no bloqueante): enviar el inventario a una persona con dominio de la variante cuando haya disponibilidad y abrir un change de seguimiento con sus correcciones.
