# Proposal

## Why

Los textos del theme mezclan cuatro idiomas o normas:

- **Galego reintegrado:** el footer ("Umha sebe coidada com agarimo pola…").
- **Portugués estándar:** las etiquetas de proyectos, eventos, recursos y taxonomías ("Não encontrado", "lixeira").
- **Castellano:** `index.php` ("No hay entradas") y fragmentos de `content-none.php` ("pueda ayudar", "con palavras-chave").
- **Inglés:** las etiquetas de navegación ("Main navigation", "Main Menu").

Además, los textos de `map.js` no se pueden traducir, y los títulos de archivo se limpian con un `str_replace` de prefijos en castellano o inglés que deja de funcionar si cambia el idioma de WordPress. La norma del sitio es el **galego reintegrado, en la variante "umha", "-çom", "nom"**.

## What Changes

- **Todos los textos visibles del sitio público y las etiquetas del admin que define el theme**, escritos en galego reintegrado (variante "umha"/"-çom"/"nom"). Incluye:
  - tipos de contenido y taxonomías;
  - plantillas: `content-none`, `index`, hero, navegación, footer;
  - textos de `map.js`;
  - textos introducidos por otros changes (estado vacío de filtros, avisos de geocoding).
- **Todos esos textos pasan por gettext con el text domain `silveira`**, y se carga el text domain del theme. Los textos de JavaScript llegan desde PHP ya traducidos.
- **Títulos de archivo sin prefijo** en cualquier idioma de WordPress. El filtro lo introduce `consistent-templates`; aquí se verifica con WordPress en galego.
- **Corrección del doble escapado** en el título de resultados de búsqueda.
- **Revisión lingüística posterior y no bloqueante:** el inventario de textos se envía a una persona que escriba la variante con soltura cuando haya disponibilidad. No impide aplicar ni archivar el change; sus correcciones se aplican después en un change de seguimiento.
- **Fuera de alcance:**
  - la plantilla interna Kitchen Sink (guía de estilo, en inglés);
  - los comentarios del código;
  - la traducción del núcleo de WordPress y de los plugins.

## Capabilities

### New Capabilities
- `site-language`: idioma y norma lingüística de los textos que el theme muestra en el sitio público y en el admin, y su gestión como textos traducibles.

### Modified Capabilities
<!-- Ninguna -->

## Impact

- **Registro de contenido:** `inc/cpt-projetos.php`, `inc/cpt-eventos.php`, `inc/cpt-recursos.php`, `inc/tax-shared.php`.
- **Plantillas:** `header.php`, `footer.php`, `index.php`, `front-page.php`, `template-parts/*.php` y las plantillas resultantes de `consistent-templates`.
- **Lógica del theme:** `inc/template-tags.php` (o `functions.php`): hero, títulos de archivo y búsqueda.
- **Datos para JS:** `inc/map-data.php`, o el lugar donde se inyecten los textos para JS.
- **JS:** `src/js/map.js` y rebuild de `assets/`.
- **Orden recomendado:** el último, después de todos los demás changes, para cubrir también los textos que introducen (`map-filters-fixes`, `geocoding-reliability`, `consistent-templates`, `homepage-scroll-a11y`, `third-party-assets`).
