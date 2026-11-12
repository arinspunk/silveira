# Tasks

## 1. Estado y CSS

- [x] 1.1 Crear `src/js/homepage.js` con la máquina de estados (`data-home`, `go()`, `transitionend` + tiempo máximo, movimiento reducido) (design D1) y moverle la lógica de scroll/footer de `map.js`, manteniendo de momento las mismas entradas de ratón/rueda/táctil; verificar que con ratón la portada se comporta exactamente como antes
- [x] 1.2 Reescribir `_main.scss`/`_map.scss` para reaccionar a `[data-home]` y usar `--sil-hero-height`/`--sil-footer-height` medidas por JS con `ResizeObserver` (design D5, D7); verificar a 1440, 1024 y 400 px de ancho que los desplazamientos encajan y que no queda `-231px` en el SCSS; incluye el arreglo del layout móvil de filtros y mapa (design D5)
- [x] 1.3 Verificar el escenario "Script blocked" desactivando JavaScript en el navegador

## 2. Teclado y foco

- [x] 2.1 Añadir las transiciones por teclado de design D2 con las exclusiones de campos y mapa; verificar los escenarios "PageDown", "Home key" y que las flechas dentro del mapa siguen moviéndolo
- [x] 2.2 Añadir la gestión de `focusin` (design D3); verificar "Keyboard user tabs into filters", "Keyboard focus enters footer", "Footer closes" (por foco) y "Shift+Tab from map to header" recorriendo toda la portada solo con Tab/Shift+Tab

## 3. Táctil y footer

- [x] 3.1 Añadir `c-footer__reveal` con `aria-label` (texto en galego reintegrado, variante "umha"/"-çom"/"nom", con `esc_attr__( …, 'silveira' )` para que lo recoja el inventario de `i18n-reintegrado`) y `aria-expanded`, y sus gestos (design D4); verificar el escenario "Touch device" en un móvil real o en el emulador de dispositivos de Chrome y Safari iOS

## 4. Panel de lista

- [x] 4.1 Aplicar `inert`, `aria-controls`/`aria-expanded` y quitar estilos en línea de `front-page.php` y `innerHTML` del botón (design D6); verificar "Tabbing with closed panel" y "Toggle state announced" con VoiceOver

## 5. Movimiento reducido y cierre

- [x] 5.1 Verificar el escenario "Rapid keyboard navigation with reduced motion" activando "Reducir movimiento" en el sistema
- [x] 5.2 Pasar axe DevTools en los tres estados de la portada sin errores nuevos; actualizar el smoke test si cambia cómo se expande el mapa; verificar que el commit incluye `assets/` regenerado por el hook y que la CI (incluido el smoke test de `map-smoke-tests`) pasa
