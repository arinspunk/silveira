# Proposal

## Why

Varios puntos frágiles del theme pueden tumbar el sitio o generar enlaces rotos sin que nadie lo note:

- **ACF:** el theme llama a `get_field()`/`update_field()` sin comprobar que ACF esté activo. Si se desactiva el plugin, o falla una actualización, la portada y las fichas dan error fatal.
- **Recursos sin categoría:** su URL usa un término `geral` que puede no existir, lo que probablemente da 404.
- **`functions.php`:** mezcla la carga de Vite, menús, ACF, el hero y el geocoding, mientras el resto vive en `inc/`.
- **Menú del footer:** recibe las clases BEM del header.

## What Changes

- **ACF como dependencia opcional y controlada:**
  - se accede a los campos a través de un helper del theme que devuelve `null` si ACF no está activo;
  - se muestra un aviso en el admin cuando falta ACF;
  - el sitio público sigue funcionando, con el mapa sin puntos y las fichas sin lema ni dirección.
- **Recursos siempre con categoría:** al guardar un recurso sin categoría se le asigna la categoría `geral` (se crea si no existe), de forma que su URL siempre resuelve.
- **`functions.php` modularizado** en ficheros de `inc/`: `vite.php`, `setup.php` (soportes y menús), `acf.php`, `template-tags.php` (hero) y `geocoding.php`. Todas las rutas se resuelven con `get_theme_file_path()`.
- **Clases de los menús según el contexto** (header o footer) en lugar de la ubicación del menú. Así el footer recibe `c-footer__*` y el header `c-header__*`.

## Capabilities

### New Capabilities
- `theme-dependencies`: cómo se comporta el sitio cuando falta un plugin del que depende (ACF).
- `recurso-permalinks`: estructura y resolución de las URLs de los recursos y de sus categorías.

### Modified Capabilities
<!-- Ninguna -->

## Impact

- `functions.php`, nuevos `inc/vite.php`, `inc/setup.php`, `inc/acf.php`, `inc/template-tags.php`, `inc/geocoding.php`; `inc/cpt-recursos.php`.
- Plantillas que usan `get_field` (`front-page.php` o `inc/map-data.php` si `harden-map-output` ya está aplicado, `template-parts/content-projeto.php`).
- `header.php` y `footer.php` (argumento de contexto del menú).
- Orden recomendado: después de `enforce-build-and-lint`, `harden-map-output`, `map-smoke-tests` y `self-hosted-fonts`, y antes de los demás changes funcionales, porque fija la estructura de `inc/` sobre la que trabajan.
- Los `preconnect` duplicados a Google Fonts no se tratan aquí: desaparecen con `self-hosted-fonts`, que deja de usar Google Fonts.
