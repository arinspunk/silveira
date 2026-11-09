# Tasks

## 1. Datos del mapa en PHP

- [ ] 1.1 Crear `wp-content/themes/silveira/inc/map-data.php` con `silveira_get_map_points()`, moviendo la consulta y el cálculo de `overline`/`modalidades`/`territorios` desde `front-page.php` sin cambiar su resultado (eliminar la variable `$territorios` no usada); verificar comparando el JSON resultante con el actual para los proyectos existentes (mismas claves y mismos puntos)
- [ ] 1.2 Normalizar en `silveira_get_map_points()` los campos `title`, `lema`, `excerpt` y `overline` a texto plano (`wp_strip_all_tags` + `html_entity_decode` UTF-8) y `url`/`image` con `esc_url_raw` (design D2); verificar que un título `A Horta d'Ana & Cía` llega al JSON sin entidades
- [ ] 1.3 Añadir callback en `wp_enqueue_scripts` (prioridad 30, solo `is_front_page()`) que inyecte `window.silveiraMapPoints` con `wp_add_inline_script('silveira-main', ..., 'before')` y `wp_json_encode` con `JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT` (design D1); hacer `require_once` en `functions.php`; verificar en el código fuente de la portada que el script de datos aparece justo antes del módulo `silveira-main` en modo prod y en modo dev (`SILVEIRA_VITE_DEV=1`)
- [ ] 1.4 Eliminar de `front-page.php` el bucle de construcción de puntos y el `<script>` en línea; verificar que la portada carga el mapa con todos los marcadores y que no queda ningún `var silveiraMapPoints` en el HTML del body
- [ ] 1.5 Pasar PHPCS y PHPStan sobre `inc/map-data.php` indicando el fichero en la línea de comandos (vía Docker `composer:2`), sin tocar `phpcs.xml.dist` ni `phpstan.neon.dist`: los amplía a todo el theme la tarea 2.1 de `enforce-build-and-lint`; verificar que ambos terminan sin errores

## 2. Renderizado seguro en JS

- [ ] 2.1 Añadir en `src/js/map.js` el helper `el()` y `safeUrl()` (design D3, D4); verificar con `npm run lint` en el theme
- [ ] 2.2 Sustituir el template literal del popup por `buildPopup(point)` que devuelve un `HTMLElement` con la misma estructura y clases `c-map-card*`, `alt` = título, y sin bloque de imagen ni enlace si `safeUrl` devuelve `null`; verificar visualmente que el popup es idéntico al actual en un proyecto normal
- [ ] 2.3 Sustituir el `li.innerHTML` de la lista lateral por `buildListItem(point)` con la misma estructura y clases `c-project-list-item*`; verificar visualmente que la lista es idéntica al actual y que filtrar sigue actualizando lista y marcadores
- [ ] 2.4 Comprobar que no quedan asignaciones de `innerHTML` ni template literals HTML que interpolen campos de `point` (`grep -n "innerHTML\|\${point" src/js/map.js` solo muestra contenido estático)

## 3. Verificación de seguridad (entorno Docker local)

- [ ] 3.1 Crear proyectos de prueba con coordenadas: título `<img src=x onerror=alert(1)>Horta`, lema `</script><script>alert(1)</script>`, título `A Horta d'Ana & Cía`, título con comillas dobles y otro sin imagen destacada; verificar todos los escenarios de `specs/project-map/spec.md` (texto literal, sin alertas ni elementos inyectados, sin entidades visibles, `alt` íntegro, popup sin imagen)
- [ ] 3.2 Forzar en consola `window.silveiraMapPoints[0].url = 'javascript:alert(1)'` antes de inicializar (o con un punto de prueba en modo dev) y verificar que no se renderiza enlace con ese esquema
- [ ] 3.3 Borrar los proyectos de prueba al terminar y verificar que el mapa vuelve a mostrar solo los proyectos reales

## 4. Build de producción

- [ ] 4.1 Ejecutar `npm run build` en `wp-content/themes/silveira` (incluye lint vía `prebuild`) y verificar que termina sin errores y que `assets/.vite/manifest.json` apunta a los nuevos ficheros con hash
- [ ] 4.2 Con `SILVEIRA_VITE_DEV=0`, verificar en la portada que pulsar un marcador con el mapa contraído lo expande, muestra "Ver a lista" y abre el popup (escenario "Marker click in production build"), y que `grep -c "silveira:slide-up" assets/js/main-*.js` devuelve ≥ 1
- [ ] 4.3 Revisar visualmente portada, ficha de proyecto y plantilla Kitchen Sink con el nuevo CSS compilado (incluye `stacked-card`, hasta ahora no compilado) y anotar cualquier regresión antes de commitear `assets/`
