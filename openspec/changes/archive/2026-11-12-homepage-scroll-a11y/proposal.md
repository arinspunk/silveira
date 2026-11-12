# Proposal

## Why

La portada usa una interacción propia: al bajar, el contenido se desliza, el mapa queda desbloqueado y, al llegar abajo, se revela el footer. Se ha decidido **mantenerla**, pero hoy solo funciona con ratón, rueda o deslizando el dedo:

- **Teclado:** no hay forma de expandir el mapa ni de llegar al footer. El scroll nativo está bloqueado (`body { overflow: hidden }`).
- **Móvil:** el footer solo se abre con `mouseenter`, así que en móvil nunca se ve.
- **Movimiento reducido:** con `prefers-reduced-motion` las transiciones CSS ya son instantáneas, pero el JS bloquea la interacción 800 ms igualmente.
- **Panel de la lista cerrado:** queda fuera de pantalla pero sus enlaces siguen siendo alcanzables con Tab.
- **Código frágil:** el estado se maneja con estilos en línea y `setTimeout` acoplados a la duración CSS, y hay un desplazamiento fijo de `-231px` que depende de la altura del hero.

## What Changes

- **Mapa con teclado:** se expande al entrar el foco en los filtros o en el mapa, y con `PageDown`/`Space`/flecha abajo. Se contrae con `PageUp`/`Home`.
- **Footer accesible:**
  - se abre al entrar el foco en sus enlaces o con `End`, y se cierra con `PageUp` o al salir el foco;
  - en táctil, una franja visible en la parte inferior lo abre al tocarla o al deslizar hacia arriba sobre ella.
- **Movimiento reducido:** con `prefers-reduced-motion`, los cambios de estado son inmediatos y no hay periodo de bloqueo.
- **Transiciones según su fin real:** el estado se coordina con `transitionend` (con un tiempo máximo de seguridad), no con `setTimeout(800)`.
- **Panel de la lista:**
  - el botón expone `aria-expanded`/`aria-controls`;
  - el panel cerrado es `inert`, sin foco ni presencia para lectores de pantalla.
- **Estado de la portada en clases y atributos**, sin estilos en línea. El desplazamiento se calcula con variables CSS a partir de la altura real del hero y del footer.
- **Sin JS**, la portada se puede recorrer con el scroll nativo.

## Capabilities

### New Capabilities
- `homepage-navigation`: interacción de navegación de la portada (estado contraído, mapa expandido, footer revelado) y la forma de pasar entre estados con ratón, táctil y teclado.

### Modified Capabilities
<!-- Ninguna -->

## Impact

- `src/js/map.js`: la parte de scroll/footer se extrae a `src/js/homepage.js`.
- SCSS: `src/scss/5-objects/_main.scss`, `6-components/_map.scss`, `_footer.scss`, `_buttons.scss`.
- `front-page.php`: quitar estilos en línea, ARIA del botón y del panel. `footer.php`: franja táctil, si es un elemento nuevo.
- Rebuild de `assets/`.
- Orden recomendado: después de `harden-map-output`, `map-filters-fixes` y `consistent-templates` (que mueve `wp_footer()`).
