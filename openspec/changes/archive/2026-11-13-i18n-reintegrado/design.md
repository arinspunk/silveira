# Design

## Context

Ver proposal.md. Los textos ya pasan por `__()`/`esc_html_e()` en PHP con el dominio `silveira`, pero no se llama a `load_theme_textdomain`. `map.js` tiene "Ver projeto", "Ver a lista" y "Acochar a lista" en el código fuente. `silveira_hero()` quita prefijos con `str_replace( ['Archivo: ', 'Archive: ', 'Território: '] )` y construye el título de búsqueda con `sprintf( esc_html__(...), get_search_query() )` que luego la plantilla escapa de nuevo.

## Goals / Non-Goals

**Goals:** un único idioma/norma en los textos del theme; todo texto traducible; títulos limpios.

**Non-Goals:** ficheros `.po/.mo` (el idioma fuente ya es el definitivo); idioma del núcleo de WP/ACF; contenido editorial; Kitchen Sink.

## Decisions

### D1. Idioma fuente = reintegrado
Los `msgid` se escriben directamente en la variante del sitio. No se generan traducciones; `load_theme_textdomain( 'silveira', get_theme_file_path( 'languages' ) )` se añade para permitir sobrescrituras futuras (requisito "Theme text is translatable").
- *Alternativa*: `msgid` en inglés + `gl_ES.po` reintegrado. Más "canónico", pero añade un paso de compilación y riesgo de desincronía para un sitio monolingüe.

### D2. Textos de JS desde PHP
Junto a `window.silveiraMapPoints` (mismo `wp_add_inline_script` de `harden-map-output`) se inyecta `window.silveiraI18n = { viewProject, showList, hideList, noResults, ... }` con `wp_json_encode` de valores `__()`. `map.js` lee de ahí con fallback al propio texto.
- *Alternativa*: `wp_set_script_translations` + `@wordpress/i18n` — requiere ficheros JSON de traducción por hash de script, mal encaje con Vite y hashes cambiantes.

### D3. Prefijo de archivo
Ya resuelto por `consistent-templates` (su D6): filtro `get_the_archive_title_prefix` → `''` y sin `str_replace`. Aquí solo se verifica que, con WordPress en galego, el título sigue saliendo sin prefijo.

### D4. Búsqueda
`sprintf( __( 'Resultados para: %s', 'silveira' ), get_search_query( false ) )` y la plantilla del hero escapa una vez con `esc_html`.

### D5. Inventario y revisión
Se genera un inventario con `wp i18n make-pot` (WP-CLI) en `scratch` (no versionado) para listar todos los `msgid` del theme. Se reescriben según la variante (p. ej. "Não" → "Nom", "-ção" → "-çom", "uma" → "umha", "na lixeira" → forma a decidir) Los textos dudosos se marcan en la PR y en el inventario. La revisión lingüística es posterior y no bloquea el cierre del change (ver Risks).

### D6. Nombre accesible de los filtros
Hoy el `<button class="c-select__trigger">` no tiene nombre explícito, así que su nombre accesible es todo su contenido: la etiqueta ("Modalidade educativa") y el texto de ejemplo ("Que procuras?"), que además no cambia al marcar opciones.

- En `front-page.php`, cada botón lleva `aria-labelledby="filter-<grupo>-label filter-<grupo>-status"`. El texto de ejemplo sigue visible, pero queda fuera del nombre.
- `filter-<grupo>-status` es un `<span>` solo para lectores de pantalla dentro del botón. Vacío si no hay nada marcado (nombre: "Modalidade educativa"); con opciones marcadas, `_n( '%d selecionada', '%d selecionadas', … )` precedido de coma ("Modalidade educativa, 2 selecionadas").
- El tema no tiene utilidad de texto oculto: se añade `.screen-reader-text` (la clase convencional de WordPress) en la capa de utilidades del SCSS.
- `map.js` actualiza los tres `status` cuando cambia una casilla y cuando `syncFilterOptions` desmarca localidades incompatibles. Los textos singular/plural llegan por `window.silveiraI18n` (D2) como plantillas con `%d`.
- La forma "selecionada(s)" se marca como dudosa en el inventario (D5) para la revisión lingüística.
- *Ajuste en la implementación:* Chrome une los ids de `aria-labelledby` con un espacio, así que etiqueta + ", 2 selecionadas" daba "Modalidade educativa , 2 selecionadas". Por eso `aria-labelledby` apunta solo al `<span>` de estado, que contiene el nombre completo: la etiqueta, o la etiqueta seguida de ", N selecionada(s)". PHP lo pinta ya con la etiqueta (nombre correcto sin JS) y `map.js` lo actualiza. El resultado anunciado es el mismo que se describe arriba.

## Risks / Trade-offs

- [Formas léxicas dudosas en la variante (p. ej. "Adicionar"/"Engadir", "lixo/papeleira")] → se marcan como dudosos y se envían a revisión lingüística cuando haya disponibilidad; el change se archiva sin esperarla y las correcciones van en un change de seguimiento.
- [Otros changes añaden textos después] → cada change nuevo debe escribir sus textos en la variante (decisión guardada); este change revisa los existentes en el momento de aplicarse.
- [El `lang` del documento lo fija el ajuste de idioma de WordPress, no el theme] → verificar que el sitio está en `gl_ES` (atributo `lang="gl-ES"`); si no, documentarlo como ajuste de instalación.
