# Proposal

## Why

Las plantillas del theme se han ido creando por copia y ya no son coherentes:

- **Archivo de proyectos (`/projeto/`):** pinta cada proyecto con la plantilla de ficha completa, con un `<h1>` y todo el contenido por cada elemento.
- **Ficha de proyecto:** tiene dos `<h1>` (el del hero y el del artículo), y el hero queda fuera de `<main>`, al revés que en el resto de plantillas.
- **Fichas de evento y recurso:** no tienen hero.
- **Cuatro plantillas de archivo casi idénticas** (`archive-evento`, `archive-projeto`, `archive-recurso`, `taxonomy-territorio`).
- **`wp_footer()` dentro de `<footer>`:** queda dentro del contenedor que se anima en la portada, lo que arrastra los scripts y la barra de admin.
- **Encabezados de la barra lateral:** saltan de `h1` a `h4`.
- **`comments_template()`:** se carga en un tipo de contenido que no admite comentarios.

El componente `c-stacked-card`, recién implementado, no se usa en ninguna plantilla.

## What Changes

- **Una sola plantilla `archive.php`** para todos los archivos (tipos de contenido y taxonomías). Lista los elementos como tarjetas `c-stacked-card` en rejilla, con paginación.
- **Nuevo `template-parts/card.php`** con la tarjeta del listado: imagen, título enlazado, contexto (modalidade/territorio o categoría) y extracto.
- **Una sola plantilla `single.php`** para eventos y recursos, con hero. Se mantiene `single-projeto.php` para la ficha de proyecto.
- **Un único `<h1>` por página.** Lo pone el hero, y el título de los artículos no se repite. En la barra lateral de la ficha, `h4` pasa a `h2`, con el mismo aspecto.
- **Hero siempre dentro de `<main>`.**
- **`wp_footer()` se mueve justo antes de `</body>`**, fuera del contenedor animado.
- **Se elimina `comments_template()`** de la ficha de proyecto.
- **Se borran las plantillas sustituidas:** `archive-evento.php`, `archive-projeto.php`, `archive-recurso.php`, `taxonomy-territorio.php`, `single-evento.php` y `single-recurso.php`.

## Capabilities

### New Capabilities
- `content-templates`: estructura de las páginas de listado y de ficha de los contenidos del sitio (proyectos, eventos, recursos, territorios).

### Modified Capabilities
<!-- Ninguna -->

## Impact

- **Plantillas PHP:**
  - nuevas: `archive.php`, `single.php`, `template-parts/card.php`;
  - modificadas: `single-projeto.php`, `template-parts/content-projeto.php`, `template-parts/content.php`, `index.php`, `footer.php`;
  - eliminadas: las 6 indicadas arriba.
- **SCSS:** rejilla de listado y posibles ajustes de `c-stacked-card` y `_project-single.scss` por el cambio de nivel de los encabezados. Rebuild de `assets/`.
- **Coordinación con otros changes:**
  - toca las mismas plantillas que `i18n-reintegrado`, por lo que conviene aplicar este primero;
  - el traslado de `wp_footer()` afecta a la portada: verificar junto con `homepage-scroll-a11y`.
