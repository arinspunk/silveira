# Tasks

## 1. Tarjeta y archivo

- [ ] 1.1 Crear `silveira_card_context()` y `template-parts/card.php` sobre `c-stacked-card` (design D2) y la rejilla `.c-card-grid`; añadir la tarjeta a la Kitchen Sink y verificar visualmente que coincide con el componente de Figma
- [ ] 1.2 Crear `archive.php` (design D1) y borrar `archive-evento.php`, `archive-projeto.php`, `archive-recurso.php` y `taxonomy-territorio.php`; verificar los escenarios de "Archives list items as cards" en `/projeto/`, `/agenda/`, `/recursos/`, un territorio y una categoría de recurso, incluida la paginación
- [ ] 1.3 Limpiar el título del hero en los archivos (design D6): sin prefijo "Archives:" ni `<span>` visible; verificar el escenario "Archive title" en `/projeto/`, `/agenda/`, `/recursos/` y un territorio

## 2. Fichas

- [ ] 2.1 Ajustar `single-projeto.php` (hero dentro de `main`, sin `comments_template`) y `content-projeto.php` (sin título, `h4`→`h2` con el mismo estilo en `_project-single.scss`) (design D3); verificar escenarios "One main heading per page", "Heading levels do not skip" y "Comments are not offered on projects"
- [ ] 2.2 Crear `single.php` para evento y recurso con hero (design D4) y borrar `single-evento.php` y `single-recurso.php`; verificar el escenario "Event page" y la ficha de un recurso
- [ ] 2.3 Adaptar `content.php`/`index.php` para no emitir `h1` propio; verificar con la búsqueda (`/?s=horta`) y una página estática que hay un único `h1`

## 3. Footer y verificación global

- [ ] 3.1 Mover `wp_footer()` fuera de `.l-site-content` (design D5); verificar que la portada (expandir mapa, abrir footer) y la barra de admin funcionan igual
- [ ] 3.2 Comprobar con un validador de encabezados (p. ej. extensión HeadingsMap o axe DevTools) portada, ficha de proyecto, evento, recurso, archivos y búsqueda: un `h1` y sin saltos; verificar que el commit incluye `assets/` regenerado por el hook y que la CI (incluido el smoke test de `map-smoke-tests`) pasa
