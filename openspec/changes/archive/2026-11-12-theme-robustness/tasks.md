# Tasks

## 1. Modularizar functions.php

- [x] 1.1 Mover setup/menús a `inc/setup.php`, Vite a `inc/vite.php`, ACF JSON a `inc/acf.php`, `silveira_hero` a `inc/template-tags.php` y geocoding a `inc/geocoding.php`, dejando `functions.php` solo con `require_once get_theme_file_path(...)` (design D4); verificar que portada, ficha de proyecto, archivos y escritorio de admin cargan sin errores y que PHPStan no reporta funciones indefinidas
- [x] 1.2 Comprobar que el theme ya no imprime `preconnect` ni define `silveira_add_preconnect` (los elimina `self-hosted-fonts`, design D6); verificar en el código fuente de la portada que no aparece ningún `preconnect` a dominios de Google

## 2. ACF opcional

- [x] 2.1 Crear `silveira_field()` y `silveira_has_acf()` y sustituir todas las llamadas directas a `get_field` en plantillas y `inc/` (design D1); verificar con `grep -rn "get_field(" wp-content/themes/silveira --include=*.php` que solo aparecen dentro de `inc/acf.php`
- [x] 2.2 Registrar el geocoding y los filtros `acf/settings/*` solo si ACF existe, y añadir el aviso de admin; verificar los escenarios de `specs/theme-dependencies/spec.md` desactivando ACF con `docker compose --profile tools run --rm wpcli wp plugin deactivate advanced-custom-fields` y reactivándolo al terminar

## 3. Permalinks de recursos

- [x] 3.1 Añadir `default_term` (`Geral`/`geral`) a `categoria_recurso` y, si hace falta, el hook de guardado (design D2); verificar el escenario "Recurso saved without category" creando un recurso de prueba desde el editor de bloques
- [x] 3.2 Asignar `geral` a los recursos existentes sin categoría con WP-CLI; verificar con `wp post list --post_type=recurso` + `wp post term list` que ninguno queda sin término
- [x] 3.3 Hacer flush de reglas y verificar todos los escenarios de `specs/recurso-permalinks/spec.md` con `curl -I`; si la paginación de categoría da 404, añadir la regla explícita (design D3) y repetir

## 4. Menús

- [x] 4.1 Añadir `silveira_context` a los `wp_nav_menu` de header y footer y adaptar los filtros de clases (design D5); verificar en el HTML que el footer tiene `c-footer__menu-*` y no `c-header__*`, y que el aspecto de header y footer no cambia
