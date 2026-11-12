# Tasks

## 1. Opciones y datos

- [x] 1.1 Generar las opciones de Comarca y Localidade según design D1; verificar con un territorio de prueba de primer nivel sin hijas que aparece solo en "Comarca" (escenarios de "Filter options reflect the territory hierarchy")
- [x] 1.2 Cambiar `wp_get_post_terms` por `get_the_terms` y eliminar `$territorios` sin uso en `silveira_get_map_points()` (design D5); verificar que el JSON de puntos es idéntico al anterior y que Query Monitor (o `SAVEQUERIES`) muestra menos consultas en la portada
- [x] 1.3 Fijar un orden determinista en la consulta de `silveira_get_map_points()` (p. ej. `'orderby' => array( 'date' => 'DESC', 'ID' => 'DESC' )`); verificar que dos cargas seguidas de la portada dan el mismo JSON de puntos aunque haya proyectos con la misma fecha

## 2. Lógica de filtrado

- [x] 2.1 Extraer `matches(point, group)`, eliminar `matchesM/C/L` y hacer de `syncFilterOptions` la única responsable de visibilidad con `hidden`, incluyendo el desmarcado de localidades incompatibles (design D2); eliminar `initDependentFilters` de `forms.js`; verificar los escenarios de "Filters combine…" y "Only compatible options are offered" y que `grep -n "style.display" src/js` no devuelve nada
- [x] 2.2 Añadir el estado vacío en la lista (design D4) con su estilo en `_list.scss`; verificar el escenario "No matches" combinando filtros sin resultados

## 3. Accesibilidad de los desplegables

- [x] 3.1 Cambiar el marcado de los tres `.c-select` a botón + panel con `aria-expanded`/`aria-controls`/`role="group"` (design D3) y ajustar `_forms.scss` para que el aspecto no cambie; verificar visualmente contra la portada actual y la Kitchen Sink
- [x] 3.2 Adaptar `initSelects` en `forms.js`: `aria-expanded`, foco a la primera opción visible al abrir, `Escape` cierra y devuelve foco, uno abierto a la vez; verificar los escenarios de teclado del spec solo con teclado y con VoiceOver anunciando "contraído/expandido"
- [x] 3.3 Verificar que abrir un desplegable sigue expandiendo el mapa cuando está contraído

## 4. Build

- [x] 4.1 Actualizar los selectores del smoke test (`map-smoke-tests`) si cambian por el nuevo marcado de los desplegables; verificar que el commit incluye `assets/` regenerado por el hook y que la CI (incluido el smoke test de `map-smoke-tests`) pasa y repetir los escenarios del spec con `SILVEIRA_VITE_DEV=0`
- [x] 4.2 Hacer que el smoke test pulse el marcador de un proyecto concreto del seed (por su título o atributo, no el primero de la lista); verificar que pasa con el seed limpio y también con contenido extra en la base de datos local
