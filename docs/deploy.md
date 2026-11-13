# Deploying to production

Runbook for when there is a production server. Today the project only runs locally (Docker); this document collects what the OpenSpec changes left done in the local database and has to be repeated in any new environment.

The commands use `wp` as is (WP-CLI on the server). Locally, prefix them with `docker compose --profile tools run --rm wpcli`.

## 1. Server requirements

- PHP 8.2 (the version tested locally and in CI).
- WordPress 6.0 or later and MySQL/MariaDB.
- HTTPS.
- WP-CLI (recommended; steps 4 and 5 use it).
- Outbound HTTPS to `nominatim.openstreetmap.org` (project geocoding) and, from visitors' browsers, to `tile.openstreetmap.org` (map tiles).
- **Neither Node nor Composer is needed**: `assets/` is compiled in the repo and `vendor/` only holds development tools.

## 2. What to copy from the theme

Only `main` with the three CI checks green (`frontend`, `php`, `e2e`).

From `wp-content/themes/silveira/` deploy everything **except** the development tools:

```bash
rsync -av --delete \
  --exclude node_modules --exclude vendor --exclude src --exclude public \
  --exclude scripts --exclude '.*' \
  --exclude 'composer.*' --exclude 'package*.json' \
  --exclude '*.config.js' --exclude phpcs.xml.dist --exclude phpstan.neon.dist \
  wp-content/themes/silveira/ user@server:/path/to/wp-content/themes/silveira/
```

Essential: the `.php` files, `inc/`, `template-parts/`, `acf-json/`, `assets/` (with `assets/.vite/manifest.json`) and `style.css`.

Do not define the `SILVEIRA_VITE_DEV` environment variable on the server (or leave it at `0`): with it, the theme tries to load the assets from the Vite dev server.

## 3. First-time setup

1. Install WordPress and the site language:
   ```bash
   wp language core install gl_ES --activate
   ```
2. Install and activate ACF (the map and the project pages use its fields; without it the site works, but the map is empty and a notice appears in the admin):
   ```bash
   wp plugin install advanced-custom-fields --activate
   ```
   The fields are loaded from `acf-json/`; there is nothing to import.
3. Activate the theme and set the permalinks:
   ```bash
   wp theme activate silveira
   wp rewrite structure '/%postname%/'
   ```
4. In `wp-config.php`:
   ```php
   define( 'SILVEIRA_NOMINATIM_EMAIL', 'contact@domain' ); // contact for Nominatim's usage policy
   define( 'WP_DEBUG', false );
   ```
5. Assign a menu to the **Menu principal** location (Appearance → Menus). It is used in the header and in the footer.

## 4. Content

**If the local database is migrated:**

```bash
# locally
docker compose --profile tools run --rm wpcli wp db export /var/www/html/silveira.sql
# on the server, after importing the dump and copying wp-content/uploads/
wp search-replace 'http://silveira.localhost' 'https://domain' --all-tables --precise
```

**In any case, once:**

1. Give the `geral` category to resources that have none (new ones already get it when saved):
   ```bash
   wp eval 'foreach ( get_posts( array( "post_type" => "recurso", "post_status" => "any", "numberposts" => -1 ) ) as $p ) { silveira_recurso_ensure_category( $p ); }'
   ```
2. Regenerate the permalink rules:
   ```bash
   wp rewrite flush
   ```
3. Geocode the projects without coordinates (1 request per second):
   ```bash
   wp silveira geocode --dry-run   # list only, no requests
   wp silveira geocode
   ```
   Check on the map the ones that are not found and fix them by hand in the editor.

## 5. Post-deploy checks

- **Front page:** the map shows the projects; the filters open, filter and show the message when there are no results; with the keyboard the map expands and the footer can be reached.
- **An archive** (e.g. `/projeto/`) and **a page** for a project, an event and a resource: a single title, without prefixes.
- **A search** with results and another without.
- **Browser Network tab:** only requests to the site's own domain and to `tile.openstreetmap.org`; none to `unpkg.com`, `fonts.googleapis.com` or `fonts.gstatic.com`.
- **Admin:** saving a project with an address gets coordinates or shows the failure notice.

## 6. Later deployments

1. Merge into `main` with CI green.
2. Repeat the `rsync` from step 2.
3. `wp rewrite flush` if the change touches content types, taxonomies or permalinks.
4. Repeat the checks from step 5.

To be able to roll back, keep a copy of the previous theme on the server (or tag each deployed version in git) before the `rsync`.
