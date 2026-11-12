# silveira

WordPress project with a classic theme in `wp-content/themes/silveira/`, **Vite** for front-end assets, and Docker for local development. The canonical site URL is **`http://silveira.localhost`** (port **80**). Compiled assets under `assets/` are **committed** so you can deploy without Node on the server.

## Prerequisites

- Docker (Docker Desktop on macOS/Windows, or Docker Engine on Linux)
- Node.js **22** (pinned in `.nvmrc`; run `nvm use` at the repo root) for the theme toolchain and repo-root Git hooks
- PHP + Composer on the host are **not** required: PHPCS/PHPStan run through Docker (`composer:2`), see [PHP quality](#php-quality)

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

   **Why two `node_modules`?** The **repository root** only installs **Husky** and **lint-staged** (Git runs hooks from the root). The **theme** installs **Vite**, ESLint, Prettier, Stylelint, etc. The pre-commit tasks `cd` into the theme before running them, so they use the **theme’s** `node_modules` without duplicating them at the root. This split is intentional.

   > **The root `npm install` is what installs the Git hooks.** Without it, commits run no checks at all and `assets/` is not rebuilt.

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

Output goes to **`assets/`**, including **`assets/.vite/manifest.json`**. These files are **committed** so deployments do not require a Node build on the server. You normally don't need to run the build by hand: the pre-commit hook rebuilds and stages `assets/` whenever a commit touches `src/`, `vite.config.js` or `package-lock.json`, and CI fails if `assets/` does not match `src/`.

## Fonts and icons

Everything is served from the theme; the site makes no requests to Google Fonts. Font files live in `wp-content/themes/silveira/src/fonts/` (only `woff2`) and Vite emits them with a hash to `assets/fonts/`.

**Isaac** (text typeface): Regular, Italic, Bold and Bold Italic, declared in `src/scss/1-settings/_typography.scss`. These are the only styles the CSS uses (weight 500 doesn't exist in the family; the browser uses Regular). To add another style, convert its `otf` to `woff2` without subsetting (e.g. with `fonttools`: `TTFont(path)`, `flavor = 'woff2'`, `save()`), put it in `src/fonts/` and add its `@font-face` with the right `font-weight`/`font-style`. Never commit the `otf` files.

**Icons** (Material Symbols Outlined): a subset with only the icons listed in `src/material-symbols-icons.txt`, in `src/fonts/material-symbols-subset.woff2`, declared in `src/scss/4-elements/_icons.scss`. To use a new icon:

```bash
cd wp-content/themes/silveira
echo new_icon_name >> src/material-symbols-icons.txt
npm run icons:subset    # downloads the new subset once from Google Fonts
```

Then commit the list and the `woff2` (the hook rebuilds `assets/`). An icon missing from the subset shows up as its name in plain text; the Kitchen Sink page lists every icon so it's easy to spot.

## Git hooks (Husky)

Hooks live at the **repository root** and are installed by `npm install` at the root. **`pre-commit`** runs **lint-staged** (config in `lint-staged.config.mjs`) sequentially on the staged files:

1. **JS / config files**: ESLint `--fix` + Prettier (theme’s `node_modules`).
2. **CSS / SCSS**: Stylelint `--fix` + Prettier.
3. **PHP** (`scripts/lint-staged-phpcs.sh`): PHPCS with local PHP if available; otherwise **through Docker** (`composer:2`, installs `vendor/` on first run). If neither PHP nor Docker is available it prints a visible warning and lets the commit through — CI will still block it.
4. **Assets** (`scripts/build-assets.sh`): if the commit touches `src/**`, `vite.config.js` or `package-lock.json`, runs `npm run build` once and adds `assets/` to the same commit. Commits that don't touch those files skip the build.

Tip: `docker pull composer:2` once so the first PHP commit isn't slow.

## PHP quality

PHPCS (WordPress Coding Standards, text domain and prefix `silveira`) and PHPStan (level 5, WordPress + ACF stubs) cover the **whole theme** except `vendor/`, `node_modules/` and `assets/`. Both must pass with zero errors (no baseline).

From `wp-content/themes/silveira`, via Docker (no PHP needed on the host):

```bash
docker run --rm -v "$PWD":/app -w /app composer:2 composer install
docker run --rm -v "$PWD":/app -w /app composer:2 composer run lint      # PHPCS
docker run --rm -v "$PWD":/app -w /app composer:2 composer run lint:fix  # PHPCBF (auto-fix)
docker run --rm -v "$PWD":/app -w /app composer:2 composer run analyse   # PHPStan
```

With PHP + Composer installed locally, the same `composer run …` commands work directly.

## Continuous integration

GitHub Actions (`.github/workflows/ci.yml`) runs on every pull request and on pushes to `main`. All three jobs must be green (they are required checks on `main`):

| Job | Checks |
|-----|--------|
| `frontend` | `npm ci`, `npm run validate` (ESLint + Stylelint), `npm run format:check` (Prettier), `npm run build`, then fails if `assets/` differs from the committed one (changed, new or deleted files). On failure it uploads the generated `assets/` as an artifact for diagnosis. |
| `php` | PHP 8.2, `composer install`, `composer audit` (security advisories in PHP dependencies; abandoned packages are only reported), `composer run lint` (PHPCS), `composer run analyse` (PHPStan). |
| `e2e` | Isolated WordPress (Docker, port 8081, compiled `assets/`), seeded test data, Playwright smoke test run inside the official `mcr.microsoft.com/playwright` image (no `apt`, no `playwright install`). On failure it uploads the Playwright report. See [Smoke test (E2E)](#smoke-test-e2e). |

To reproduce locally, from `wp-content/themes/silveira`:

```bash
npm ci && npm run validate && npm run format:check && npm run build
git status --porcelain -- assets/   # must print nothing
docker run --rm -v "$PWD":/app -w /app composer:2 sh -c 'composer install -q && composer audit --locked --abandoned=report && composer run lint && composer run analyse'
```

If the `frontend` job reports that `assets/` is out of date, run `npm run build` in the theme and commit `assets/` (or make sure the hooks are installed with `npm install` at the root).

### Weekly security audit

`.github/workflows/audit.yml` runs the same `composer audit` every Monday at 06:00 UTC (and on demand from the **Actions** tab → *Audit* → *Run workflow*), so a newly published advisory shows up even when nobody opens a PR. GitHub emails the repo owner when it fails. GitHub disables scheduled workflows after 60 days without repository activity; re-enable it from the Actions tab if that happens.

**When `composer audit` fails** (in the `php` job or the weekly run): the advisory is about a PHP dev tool, not the site. Update only the affected package to a fixed version in a small change (raise its constraint in `composer.json` so a vulnerable version can't come back, run a scoped `composer update <package>` via Docker, and keep `lint`/`analyse` at zero errors). After it is merged, run `composer install` in the theme so your local `vendor/` (used by the pre-commit hook) is updated too.

## Smoke test (E2E)

A Playwright smoke test (`tests/e2e/`) checks the front page and a project page against a **separate** WordPress (Compose project `silveira-e2e`, own volumes, **port 8081**, `SILVEIRA_VITE_DEV=0` so it uses the committed `assets/`). It doesn't touch your development database and doesn't need port 80.

It covers: no PHP/console errors, one marker and list item per project with coordinates, marker popup, the modalidade filter, project content with HTML shown as text without running anything (regression test for the map XSS), and the project page.

From the repository root (after `npm install`):

```bash
npm run e2e:up      # start the isolated stack on http://localhost:8081
npm run e2e:seed    # install WP + ACF and create the test data (idempotent)
npx playwright install chromium   # first time only
npm run test:e2e    # run the tests
npm run e2e:down    # stop and delete the stack and its volumes
```

**In CI** the tests don't use the runner's browser: they run with `docker run --network host` inside `mcr.microsoft.com/playwright:v<version>-noble`, which already ships Chromium and its system libraries. The tag is taken from `@playwright/test` in the root `package.json`, which must stay pinned to an exact `X.Y.Z` version (the job fails otherwise). To upgrade Playwright, change that version and run `npm install`; CI picks the matching image. If the job fails while downloading the image, it is an infrastructure problem, not a test failure: re-run the job. Locally nothing changes: `npm run test:e2e` uses the browser installed on your machine.

The test data lives in `scripts/e2e-seed.sh`. If a change alters the map/front-page markup the tests rely on, update `tests/e2e/` in the same change.

## Git blame and mass reformatting

Commits that only reformat code (Prettier, phpcbf) are listed in **`.git-blame-ignore-revs`**. Tell Git to skip them in `git blame` (once per clone):

```bash
git config blame.ignoreRevsFile .git-blame-ignore-revs
```

GitHub's blame view applies this file automatically.

## Project layout (high level)

| Path | Description |
|------|-------------|
| `docker-compose.yml` | WordPress, MariaDB, optional `wpcli` (`tools` profile) |
| `wp-content/themes/silveira/` | Theme: Vite entry `src/main.js`, build output `assets/` |
| `package.json` (root) | Husky, lint-staged and Playwright → **`node_modules/` at repo root** |
| `wp-content/themes/silveira/src/fonts/`, `src/material-symbols-icons.txt`, `scripts/icons-subset.mjs` | Self-hosted fonts and the icon subset (see [Fonts and icons](#fonts-and-icons)) |
| `wp-content/themes/silveira/package.json` | Vite + linters + build scripts → **`node_modules/` under the theme** |
| `lint-staged.config.mjs` | Pre-commit tasks (lint, PHPCS, assets build) |
| `scripts/lint-staged-phpcs.sh` | PHPCS for staged PHP: local PHP → Docker → warning |
| `scripts/build-assets.sh` | Rebuilds and stages `assets/` from the pre-commit hook |
| `.github/workflows/ci.yml` | CI: `frontend`, `php` and `e2e` jobs |
| `tests/e2e/`, `playwright.config.js` | Playwright smoke test |
| `docker-compose.e2e.yml`, `scripts/e2e-*.sh` | Isolated E2E stack, seed and cleanup |
| `.nvmrc` | Node version (22) for local and CI |
| `.git-blame-ignore-revs` | Reformatting commits hidden from `git blame` |

## WP-CLI (optional)

With the `tools` profile:

```bash
docker compose --profile tools run --rm wpcli wp --info
```
