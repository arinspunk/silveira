# Despliegue a producción

Runbook para cuando exista un servidor de producción. Hoy el proyecto solo corre en local (Docker); este documento recoge lo que los changes de OpenSpec dejaron hecho en la base de datos local y hay que repetir en cualquier entorno nuevo.

Los comandos usan `wp` tal cual (WP-CLI en el servidor). En local, anteponer `docker compose --profile tools run --rm wpcli`.

## 1. Requisitos del servidor

- PHP 8.2 (la versión con la que se prueba en local y en la CI).
- WordPress 6.0 o superior y MySQL/MariaDB.
- HTTPS.
- WP-CLI (recomendado; los pasos 4 y 5 lo usan).
- Salida HTTPS hacia `nominatim.openstreetmap.org` (geocoding de proyectos) y, en el navegador de los visitantes, hacia `tile.openstreetmap.org` (teselas del mapa).
- **No hace falta Node ni Composer**: `assets/` va compilado en el repo y `vendor/` solo tiene herramientas de desarrollo.

## 2. Qué se copia del tema

Solo `main` con los tres checks de la CI en verde (`frontend`, `php`, `e2e`).

Del directorio `wp-content/themes/silveira/` se despliega todo **salvo** las herramientas de desarrollo:

```bash
rsync -av --delete \
  --exclude node_modules --exclude vendor --exclude src --exclude public \
  --exclude scripts --exclude '.*' \
  --exclude 'composer.*' --exclude 'package*.json' \
  --exclude '*.config.js' --exclude phpcs.xml.dist --exclude phpstan.neon.dist \
  wp-content/themes/silveira/ usuario@servidor:/ruta/a/wp-content/themes/silveira/
```

Imprescindibles: los `.php`, `inc/`, `template-parts/`, `acf-json/`, `assets/` (con `assets/.vite/manifest.json`) y `style.css`.

No definir la variable de entorno `SILVEIRA_VITE_DEV` en el servidor (o dejarla a `0`): con ella el tema intenta cargar los assets del servidor de Vite.

## 3. Primera instalación

1. Instalar WordPress y el idioma del sitio:
   ```bash
   wp language core install gl_ES --activate
   ```
2. Instalar y activar ACF (el mapa y las fichas usan sus campos; sin él el sitio funciona, pero el mapa queda vacío y aparece un aviso en el admin):
   ```bash
   wp plugin install advanced-custom-fields --activate
   ```
   Los campos se cargan desde `acf-json/`; no hay que importarlos.
3. Activar el tema y fijar los enlaces permanentes:
   ```bash
   wp theme activate silveira
   wp rewrite structure '/%postname%/'
   ```
4. En `wp-config.php`:
   ```php
   define( 'SILVEIRA_NOMINATIM_EMAIL', 'contacto@dominio' ); // contacto para la política de Nominatim
   define( 'WP_DEBUG', false );
   ```
5. Asignar un menú a la ubicación **Menu principal** (Apariencia → Menús). Se usa en la cabecera y en el pie.

## 4. Contenido

**Si se migra la base de datos local:**

```bash
# en local
docker compose --profile tools run --rm wpcli wp db export /var/www/html/silveira.sql
# en el servidor, tras importar el dump y copiar wp-content/uploads/
wp search-replace 'http://silveira.localhost' 'https://dominio' --all-tables --precise
```

**En cualquier caso, una vez:**

1. Dar la categoría `geral` a los recursos que no tengan ninguna (los nuevos ya la reciben al guardarse):
   ```bash
   wp eval 'foreach ( get_posts( array( "post_type" => "recurso", "post_status" => "any", "numberposts" => -1 ) ) as $p ) { silveira_recurso_ensure_category( $p ); }'
   ```
2. Regenerar las reglas de enlaces permanentes:
   ```bash
   wp rewrite flush
   ```
3. Geocodificar los proyectos sin coordenadas (1 petición por segundo):
   ```bash
   wp silveira geocode --dry-run   # solo lista, sin peticiones
   wp silveira geocode
   ```
   Revisar en el mapa los que no se encuentren y corregirlos a mano en el editor.

## 5. Comprobación tras desplegar

- **Portada:** el mapa muestra los proyectos; los filtros abren, filtran y muestran el mensaje si no hay resultados; con teclado se expande el mapa y se llega al pie.
- **Un archivo** (p. ej. `/projeto/`) y **una ficha** de proyecto, evento y recurso: un único título, sin prefijos.
- **Una búsqueda** con resultados y otra sin ellos.
- **Pestaña Red del navegador:** solo peticiones al propio dominio y a `tile.openstreetmap.org`; ninguna a `unpkg.com`, `fonts.googleapis.com` ni `fonts.gstatic.com`.
- **Admin:** al guardar un proyecto con dirección se obtienen coordenadas o aparece el aviso de fallo.

## 6. Despliegues siguientes

1. Mergear en `main` con la CI en verde.
2. Repetir el `rsync` del paso 2.
3. `wp rewrite flush` si el cambio toca tipos de contenido, taxonomías o enlaces permanentes.
4. Repetir la comprobación del paso 5.

Para poder volver atrás, conservar una copia del tema anterior en el servidor (o etiquetar en git cada versión desplegada) antes del `rsync`.
