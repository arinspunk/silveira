# Design

## Context

Ver proposal.md. Implementación actual (`map.js` ~líneas 218-411):

```
            wheel/touch down, click filtro/marcador
 [LOCKED] ---------------------------------------> [EXPANDED]
    ^      <-- wheel/touch up sobre filtros/header      |
    |                                                  | mouseenter footer
    |                                                  v
    +--------------------------------------------- [FOOTER]
                (no hay camino directo)        mouseleave por arriba / wheel up
```
Estados expresados como `.l-site-content.is-scrolled`, `.is-footer-open`, `.c-map.is-locked`, booleanos `isMapLocked/isFooterOpen/isAnimating` y `setTimeout(800)`. `_main.scss` anima `transform` 0.8 s y desplaza `-231px - (footer-height - 40px)`. `_reset.scss` ya reduce las transiciones a 0.01 ms con `prefers-reduced-motion`. `body.style.overflow = 'hidden'` lo pone el JS.

## Goals / Non-Goals

**Goals:** mismos tres estados y misma sensación visual con ratón; caminos equivalentes para teclado y táctil; estado único y explícito; sin dependencias de tiempos fijos.

**Non-Goals:** rediseñar la portada; cambiar la interacción del propio mapa Leaflet (zoom/arrastre); comportamiento de otras páginas.

## Decisions

### D1. Máquina de estados explícita en `src/js/homepage.js`
`state ∈ {collapsed, expanded, footer}` en `document.documentElement.dataset.home`; CSS reacciona a `[data-home="expanded"]` etc. Una sola función `go(next)` aplica la transición, marca `busy` y la libera en `transitionend` del `.l-site-content` (filtrando `propertyName === 'transform'`) o tras un tiempo máximo de seguridad (1 s). Con `matchMedia('(prefers-reduced-motion: reduce)')` no se marca `busy`.
- *Alternativa*: Scroll nativo con `scroll-snap` — cambiaría la sensación de la interacción que se ha decidido mantener.

### D2. Mapa de entradas → transiciones
```
collapsed --(wheel↓ | swipe↑ | PageDown/Space/↓ | focusin filtros/mapa | click filtro/marcador)--> expanded
expanded  --(wheel↑/swipe↓ sobre filtros|header | PageUp/Home [no en input ni mapa])--> collapsed
expanded  --(mouseenter footer | tap/swipe↑ franja footer | focusin footer | End)--> footer
footer    --(wheel↑ | swipe↓ | PageUp | focusout hacia arriba | mouseleave por arriba)--> expanded
footer    --(Home)--> collapsed
```
Las teclas se ignoran si el foco está en `input`, `select`, `textarea`, `[contenteditable]` o dentro de `.leaflet-container` (las flechas mueven el mapa).

### D3. Foco
`focusin` en el documento: se calcula el estado mínimo en que el elemento enfocado es visible (header → cualquiera excepto `footer` si tapa; filtros/mapa → `expanded`; footer → `footer`; hero → `collapsed`) y se transiciona si hace falta. Esto cubre el requisito "Focused elements are always visible".

### D4. Franja táctil del footer
El footer ya asoma 40 px (las silvas). Se convierte esa franja en el disparador: un `<button class="c-footer__reveal">` visualmente integrado (sin texto visible, con `aria-label`), `aria-expanded` sincronizado con el estado `footer`, que también recibe `touchstart/touchmove` hacia arriba.

### D5. Desplazamientos por variables CSS
JS mide `hero.offsetHeight` y `footer.offsetHeight` (y en `ResizeObserver`) y fija `--sil-hero-height` / `--sil-footer-height` en `.l-site-content`; `_main.scss` usa `calc()` con ellas en lugar de `-231px`.
- La altura del mapa (`_map.scss`) usa también `--sil-header-height` y `--sil-filter-height`, medidas igual, y `100dvh`, para que la franja del footer asome en móvil con la barra de direcciones visible.
- Layout móvil (añadido durante la implementación, fallo previo): en `≤767px` los items de `.c-filter-bar` pasan a `flex: 0 0 auto` (en columna, la base de 200 px se aplicaba como alto y la barra medía 682 px) y el `min-height` del mapa baja de 460 px a 240 px. Sin esto el mapa y la franja del footer quedaban fuera de pantalla y el escenario "Touch device" no se podía cumplir.

### D6. Panel de lista
`#map-project-list` arranca con `inert` y `aria-hidden` implícito por `inert`; el botón tiene `aria-controls="map-project-list"` y `aria-expanded`. Abrir quita `inert`. El texto del botón se cambia en un `<span>` hijo (no `innerHTML` del botón). Se quitan los `style=` en línea de `front-page.php`; visibilidad inicial vía `[data-home="collapsed"] .c-btn--map-toggle { visibility: hidden }`.

### D7. Sin JS
`overflow: hidden` en `body` y los `transform` de estado se aplican solo bajo `html[data-home]`, que el JS pone al iniciar. Sin JS no hay atributo y la página fluye con scroll nativo.

## Risks / Trade-offs

- [`focusin` en el header con footer abierto puede provocar saltos inesperados] → la tabla de D3 prioriza no mover si el elemento ya es visible.
- [`Space` choca con botones/enlaces enfocados] → solo actúa si el foco está en `body`/documento.
- [`inert` no soportado en navegadores antiguos] → soporte general desde 2023; fallback no necesario para el público objetivo, anotado.
- [Cambios de estado durante `transitionend` perdidos si la transición se interrumpe] → tiempo máximo de seguridad en D1.
