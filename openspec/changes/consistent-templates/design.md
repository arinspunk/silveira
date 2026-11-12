# Design

## Context

Ver proposal.md. Jerarquía de plantillas de WordPress relevante: `archive-{post_type}.php` → `archive.php`; `taxonomy-{tax}.php` → `taxonomy.php` → `archive.php`; `single-{post_type}.php` → `single.php`. `silveira_hero()` ya calcula título/subtítulo/imagen según contexto. `c-stacked-card` existe en SCSS (header, media, content, headline, title, subhead, supporting-text, actions) pero ninguna plantilla lo usa.

## Goals / Non-Goals

**Goals:** menos plantillas, un `h1` por página, listados ligeros y coherentes, `wp_footer` fuera del contenedor animado.

**Non-Goals:** diseño específico de evento (fecha, lugar) — no hay campos ACF para eventos; filtros en archivos; cambiar el hero; textos (los fija `i18n-reintegrado`).

## Decisions

### D1. `archive.php` único
Hero + `<div class="o-container"><div class="c-card-grid">` + bucle con `get_template_part( 'template-parts/card' )` + `the_posts_pagination()`. Clase de `main`: `l-main l-main--archive l-main--archive-<post_type|taxonomy>` para no perder ganchos de estilo existentes.
- *Alternativa*: mantener las cuatro plantillas y solo cambiar el template-part — sigue la duplicación.

### D2. `template-parts/card.php` sobre `c-stacked-card`
Estructura: `article.c-stacked-card` > `__media` (miniatura `medium_large`) > `__content` > `__headline` (`h2.__title` con enlace, `__subhead` con contexto) > `__supporting-text` (extracto). Contexto: proyecto → modalidade + localidade; evento → territorio; recurso → categoría. Una función `silveira_card_context( $post )` en `inc/template-tags.php` lo resuelve. Se añade `.c-card-grid` (rejilla CSS auto-fill, min 300px) en `5-objects` o `6-components`.
- `content.php` queda solo para `index.php` (blog/búsqueda) y se adapta para no emitir `h1` (usa la tarjeta también).

### D3. Un `h1`
El hero es el único que emite `h1`. `content-projeto.php` elimina el título y mantiene etiquetas de modalidade y lema bajo el hero. `index.php` en singular muestra hero + contenido sin título adicional.

### D4. `single.php` para evento y recurso
Hero (dentro de `main`) + contenido + territorios/categoría como enlaces. Se borran `single-evento.php` y `single-recurso.php`.

### D5. `wp_footer()` fuera
`footer.php`: `</footer></div><!-- .l-site-content --><?php wp_footer(); ?></body>`.

### D6. Título de archivo limpio
Hoy el hero muestra `Archives: <span>Projetos</span>`: `get_the_archive_title()` envuelve el nombre en `<span>` y antepone un prefijo en inglés; `silveira_hero()` intenta quitarlo con un `str_replace` de prefijos fijos que no coinciden ("Archive: " frente a "Archives: ") y el hero escapa el HTML. Se sustituye por el filtro `get_the_archive_title_prefix` (devuelve `''`) y `wp_strip_all_tags()` sobre el título; se elimina el `str_replace`.

## Risks / Trade-offs

- [Cambian las URLs de plantilla y algún estilo depende de `l-main--archive-projeto`] → se mantienen esas clases (D1).
- [Mover `wp_footer` cambia el orden de los scripts respecto al DOM del footer] → los scripts ya esperan a `DOMContentLoaded`; verificar portada.
- [Quitar el título del artículo de proyecto cambia la maquetación] → el hero ya lo muestra; revisión visual con quien diseña (Figma).
