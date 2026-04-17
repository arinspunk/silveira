# silveira

WordPress project with a classic theme in `wp-content/themes/silveira/`, **Vite** for front-end assets, and Docker for local development. The canonical site URL is **`http://silveira.localhost`** (port **80**). Compiled assets under `assets/` are **committed** so you can deploy without Node on the server.

## Prerequisites

- Docker (Docker Desktop on macOS/Windows, or Docker Engine on Linux)
- Node.js (for the theme toolchain and repo-root Git hooks)
- Optional: PHP + Composer on the host for PHPCS/PHPStan; otherwise use Composer via Docker (see below)

## First-time setup

1. **Environment**

   ```bash
   cp .env.example .env
   ```

   Adjust values if needed. See [Environment variables](#environment-variables).

2. **Start WordPress**

   From the repository root:

   ```bash
   docker compose up -d
   ```

   Open **`http://silveira.localhost`** in the browser. On many systems, `*.localhost` resolves to `127.0.0.1` without editing `/etc/hosts`. If port **80** is already in use, change the `ports` mapping in `docker-compose.yml` (e.g. `8070:80`) and align `WP_HOME` / `WP_SITEURL` in `WORDPRESS_CONFIG_EXTRA` accordingly.

3. **Install Node dependencies**

   **Repository root** (Husky + lint-staged):

   ```bash
   npm install
   ```

   **Theme** (`wp-content/themes/silveira`):

   ```bash
   cd wp-content/themes/silveira
   npm install
   ```

   **Why two `node_modules`?** The **repository root** only installs **Husky** and **lint-staged** (Git runs hooks from the root). The **theme** installs **Vite**, ESLint, Prettier, Stylelint, etc. Pre-commit runs ESLint/Prettier/Stylelint with `npx --prefix wp-content/themes/silveira`, so those tools use the **theme’s** `node_modules` without duplicating them at the root. This split is intentional.

4. **Install PHP dev tools (theme)**

   ```bash
   cd wp-content/themes/silveira
   composer install
   ```

   If your system PHP is broken or you prefer not to use it, run Composer inside Docker from the theme directory:

   ```bash
   cd wp-content/themes/silveira
   docker run --rm -v "$PWD":/app -w /app composer:2 composer install
   ```

## Environment variables

Defined in `.env` and passed to the `wordpress` (and `wpcli`) services—see `.env.example`.

| Variable | Purpose |
|----------|---------|
| `SILVEIRA_VITE_DEV` | Set to `1` to load CSS/JS from the Vite dev server (HMR). Use `0` or unset for production builds from `assets/`. **Do not** use `WP_DEBUG` for this. |
| `VITE_DEV_SERVER_URL` | Base URL used in **`<script src>` for the browser** (not for PHP-to-Vite calls). Default: `http://127.0.0.1:5173` when Vite runs on the same machine as the browser. Avoid `host.docker.internal` here unless your browser resolves it; it is mainly for container→host networking. |

After changing `.env`, recreate or restart the stack so containers pick up the new values:

```bash
docker compose up -d
```

## Development (Vite + WordPress)

1. Set **`SILVEIRA_VITE_DEV=1`** in `.env` (and ensure `VITE_DEV_SERVER_URL` matches where Vite listens).

2. Start the Vite dev server from the theme:

   ```bash
   cd wp-content/themes/silveira
   npm run dev
   ```

3. Edit files under **`src/`** (JavaScript, SCSS). The browser still loads the site at **`http://silveira.localhost`**; scripts and styles are proxied from Vite according to `functions.php` and `vite.config.js`.

### Do not open the Vite URL as the “site”

`http://localhost:5173/wp-content/themes/silveira/` is only the **Vite dev server** (modules, HMR). There is **no WordPress HTML** there, so the browser may show an error or a blank/404 page. That is normal. Always use **`http://silveira.localhost`** for the actual site.

### SCSS / JS changes not showing on `silveira.localhost`

1. **`SILVEIRA_VITE_DEV` must be `1`** in `.env`. If it is `0` or missing, WordPress loads **built** files from `assets/` (from the last `npm run build`). Edits under `src/` will **not** appear until you run `npm run build` again or turn dev mode on.
2. After editing `.env`, run **`docker compose up -d`** again so the `wordpress` container gets the new variables.
3. Keep **`npm run dev`** running in the theme while you work.
4. **Check the page source** on `silveira.localhost`: in dev mode you should see `<script type="module">` tags whose `src` points at the Vite dev server (default: `http://127.0.0.1:5173/wp-content/themes/silveira/...`, matching `VITE_DEV_SERVER_URL`). If they still point to `.../wp-content/themes/silveira/assets/...`, dev mode is off or the container did not reload env.
5. **`VITE_DEV_SERVER_URL`:** use a host the **browser** can open (usually `http://127.0.0.1:5173` on the same Mac/PC as `npm run dev`). Using `http://host.docker.internal:5173` often breaks in the browser even though it works from **inside** containers.
6. **No styles with dev mode on:** WordPress prints `type="text/javascript"` on enqueued scripts; the theme forces `type="module"` for Vite scripts. If you fork the enqueue logic, keep that filter or scripts will not run and imported SCSS will not apply.

## Production build (committed assets)

Always use the theme’s **`npm run build`**, not a bare `vite build`, so **`prebuild` runs `validate`** (ESLint + Stylelint) before Vite:

```bash
cd wp-content/themes/silveira
npm run build
```

Output goes to **`assets/`**, including **`assets/.vite/manifest.json`**. **Commit** these files when they change so deployments do not require a Node build on the server.

## Git hooks (Husky)

Hooks live at the **repository root**. After `npm install` at the root, **`pre-commit`** runs **lint-staged**: ESLint / Prettier / Stylelint via the theme’s `node_modules`, and **PHPCS** on staged PHP when `wp-content/themes/silveira/vendor/bin/phpcs` exists and the system `php` runs. If `vendor/` is missing or PHP cannot start, PHPCS is skipped with a message (fix local PHP or run checks in CI / Docker).

## PHP quality (optional)

From `wp-content/themes/silveira`:

```bash
composer run lint      # PHPCS (WordPress Coding Standards)
composer run analyse   # PHPStan
```

Or via Docker:

```bash
docker run --rm -v "$PWD":/app -w /app composer:2 composer run lint
docker run --rm -v "$PWD":/app -w /app composer:2 composer run analyse
```

## Project layout (high level)

| Path | Description |
|------|-------------|
| `docker-compose.yml` | WordPress, MariaDB, optional `wpcli` (`tools` profile) |
| `wp-content/themes/silveira/` | Theme: Vite entry `src/main.js`, build output `assets/` |
| `package.json` (root) | Husky + lint-staged only → **`node_modules/` at repo root** |
| `wp-content/themes/silveira/package.json` | Vite + linters + build scripts → **`node_modules/` under the theme** |
| `scripts/lint-staged-phpcs.sh` | PHPCS helper for lint-staged |

## WP-CLI (optional)

With the `tools` profile:

```bash
docker compose --profile tools run --rm wpcli wp --info
```
