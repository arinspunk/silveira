# Design

## Context

Ver proposal.md. `functions.php` tiene ~430 líneas con responsabilidades mezcladas; `inc/` ya contiene CPTs, taxonomía y assets. Se usa `get_template_directory()` en unos sitios y `get_stylesheet_directory()` en otros.

## Goals / Non-Goals

**Goals:** cero errores fatales sin ACF; URLs de recursos siempre válidas; `functions.php` reducido a `require`s; HTML de `<head>` sin duplicados.

**Non-Goals:** cambiar el comportamiento del geocoding (change `geocoding-reliability`); migrar campos fuera de ACF; soporte de child theme más allá de usar `get_theme_file_path()`.

## Decisions

### D1. Helper `silveira_field()` en `inc/acf.php`
`silveira_field( $name, $post_id = false )` devuelve `get_field()` si `function_exists( 'get_field' )`, si no `null`. `silveira_has_acf()` para lógica condicional. El geocoding y los filtros `acf/settings/*` solo se registran si ACF existe. Aviso con `admin_notices` y `current_user_can( 'activate_plugins' )`.
- *Alternativa*: declarar ACF como requerido con `Requires Plugins` (WP 6.5+) — solo aplica a plugins, no a themes.

### D2. Asignar `geral` al guardar
Hook `save_post_recurso` (y `rest_after_insert_recurso` para el editor de bloques): si `wp_get_object_terms` está vacío, `term_exists( 'geral' )` o `wp_insert_term( 'Geral', ... )` y `wp_set_object_terms`. El filtro `post_type_link` mantiene el fallback a `geral` solo para borradores/previews.
- *Alternativa*: `default_term` en `register_taxonomy` (WP 5.5+) — hace lo mismo de forma nativa. **Se prefiere `default_term`** (`array( 'name' => 'Geral', 'slug' => 'geral' )`) y el hook queda solo si las pruebas muestran que no cubre algún flujo (p. ej. REST sin términos).
- *Resultado de las pruebas (al aplicar)*: `default_term` cubre la creación sin categorías (también desde el editor de bloques), pero no cuando la API REST recibe `categoria_recurso: []` explícito (p. ej. al desmarcar la última categoría): el recurso quedaba sin término. Se mantiene `silveira_recurso_ensure_category()` en `rest_after_insert_recurso` y `save_post_recurso`, que asigna el término por defecto solo si no hay ninguno; también se usa para asignar `geral` a los recursos existentes (tarea 3.2).

### D3. Reglas de reescritura
Se verifican las URLs del spec tras flush. Si `/recursos/<cat>/page/N/` choca con la regla del CPT (que interpretaría `page` como slug de recurso), se añade una regla explícita de paginación para la taxonomía con prioridad `top`.

### D4. Modularización
```
functions.php  -> require_once get_theme_file_path( 'inc/<fichero>.php' )
inc/setup.php          after_setup_theme, menús, filtros nav_menu_*
inc/vite.php           silveira_vite_* y enqueue
inc/assets.php         (solo si sigue existiendo tras self-hosted-fonts)
inc/acf.php            helper, rutas JSON, aviso
inc/template-tags.php  silveira_hero()
inc/geocoding.php      silveira_geocode_project (sin cambios de lógica)
```
Movimiento puro, sin cambiar nombres de funciones ni hooks.

### D5. Clases de menú por contexto
`wp_nav_menu` recibe un argumento propio `'silveira_context' => 'header'|'footer'`; los filtros `nav_menu_css_class` y `nav_menu_link_attributes` añaden `c-header__menu-*` o `c-footer__menu-*` según ese argumento. No requiere crear una nueva ubicación de menú en el admin.
- *Alternativa*: registrar ubicación `footer` — obliga a asignar menú en el admin; se puede hacer más adelante.

### D6. Resource hints
Sin decisión propia: `self-hosted-fonts` (aplicado antes) elimina Google Fonts, el `echo` de `preconnect` y `silveira_add_preconnect`. Aquí solo se comprueba que no quedan.

## Risks / Trade-offs

- [Mover funciones entre ficheros rompe un `require` y tumba el sitio] → verificar portada, ficha, archivos y admin tras cada movimiento; PHPCS/PHPStan del change `enforce-build-and-lint` detectan funciones indefinidas.
- [`default_term` asigna `geral` también a recursos existentes sin término solo al re-guardarlos] → tarea de WP-CLI para asignarlo a los existentes.
- [Las clases `c-footer__menu-*` no tienen estilos] → revisar `_footer.scss`/`_main-nav.scss` y mantener aspecto idéntico.
