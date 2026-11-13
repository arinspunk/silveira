---
name: silveira-dev-setup
description: Prepares and checks the silveira development environment (Git hooks, Node 22, theme dependencies, PHPCS/PHPStan via Docker, local WordPress stack) and reproduces CI locally. Use it when starting work on a clone, before the first commit of a session, when a hook or CI fails, or when the site needs to be brought up to verify changes.
---

# silveira development environment

The host **has no PHP**: everything PHP goes through Docker. `assets/` is versioned and CI checks that it matches `src/`.

## 1. Check the clone (always, before the first commit)

```bash
git config --get core.hooksPath          # must be .husky/_
ls node_modules/.bin/lint-staged         # root deps
ls wp-content/themes/silveira/node_modules/.bin/vite   # theme deps
node -v                                  # v22.x (.nvmrc)
docker info >/dev/null && echo docker-ok
```

If something is missing, fix it:

```bash
nvm use                                   # Node 22
npm install                               # root: installs husky and enables the hooks
(cd wp-content/themes/silveira && npm ci)
docker run --rm -v "$PWD/wp-content/themes/silveira":/app -w /app composer:2 composer install -q
```

**Without `core.hooksPath` no hook runs**: no lint, no PHPCS, no `assets/` rebuild. Don't claim that a commit "went through the hook" without having checked.

## 2. What the pre-commit hook does

`lint-staged.config.mjs`, sequentially (`--concurrent false`):
- JS/config → ESLint `--fix` + Prettier; CSS/SCSS → Stylelint `--fix` + Prettier.
- PHP → `scripts/lint-staged-phpcs.sh`: local PHP → Docker `composer:2` → visible warning and exit 0.
- `src/**`, `vite.config.js` or `package-lock.json` staged → `scripts/build-assets.sh` (build + `git add assets/`), once per commit.

Consequence: changes don't need a build task; the commit includes `assets/`. If you commit with `--no-verify`, CI will fail because `assets/` is out of date.

## 3. Reproduce CI locally

From `wp-content/themes/silveira`:

```bash
npm ci && npm run validate && npm run format:check && npm run build
git status --porcelain -- assets/        # must print nothing
docker run --rm -v "$PWD":/app -w /app composer:2 sh -c 'composer install -q && composer audit --locked --abandoned=report && composer run lint && composer run analyse'
```

- PHPCS (WPCS, text domain and prefix `silveira`) and PHPStan level 5 cover the whole theme; **0 errors, no baseline**. Template variables prefixed with `silveira_`.
- `composer audit` is a blocking step of the `php` job (and there is a weekly run in `audit.yml`). If it fails, update only the affected package in a small change, raising its constraint in `composer.json`, with a scoped `composer update <package>` (without `--with-dependencies` unless needed).
- `composer run lint:fix` (phpcbf) for the automatic fixes; if it is a mass reformat, its own commit and add its hash to `.git-blame-ignore-revs`.
- For the Linux build to be identical: same lockfile and Node 22. It can be checked in a clean clone with `docker run node:22`.

## 4. Bring up the local site to verify

```bash
docker compose up -d                       # http://silveira.localhost (port 80 fixed by WP_HOME)
docker compose --profile tools run --rm -T wpcli wp <command>   # WP-CLI: always with the "wp" prefix
```

- **If port 80 is used by another project, ask before stopping its containers** and start them again when done.
- Run `docker compose` **from the root of this repo**: from another directory (e.g. a temporary clone) it creates a new project with its own containers and volumes.
- If the volumes are new, WordPress is not installed: `wp core install`, `wp theme activate silveira`, `wp plugin install advanced-custom-fields --activate` (the theme uses `get_field`), `wp rewrite structure '/%postname%/'`, and seed projects with `projeto_latitude`/`projeto_longitude` via `wp eval-file -` (`update_field` doesn't trigger geocoding).
- Vite dev mode: `SILVEIRA_VITE_DEV=1 docker compose up -d wordpress` and `npm run dev` in the theme; set it back to `0` when done.

## 5. Verify changes without regressions

For "no functional change" changes (lint, reformatting, refactoring): capture normalized HTML (without `?ver=`, Vite hashes or nonces) and screenshots of the front page, project page, archives, singles, taxonomy, Kitchen Sink and 404 **before and after**, and compare them. Capture the "before" twice to confirm it is deterministic. The front page is compared by HTML (the map tiles are not deterministic). Playwright (`playwright-core` with `channel: 'chrome'`) installed in the scratchpad, never in the repo.

## 6. E2E smoke test (Playwright)

Isolated environment (Compose project `silveira-e2e`, port **8081**, its own volumes, compiled assets): it doesn't need port 80 and doesn't touch the development database.

```bash
npm run e2e:up && npm run e2e:seed && npm run test:e2e   # from the root
npm run e2e:down                                         # when done (deletes its volumes)
```

- The test uses the compiled `assets/`: after changing `src/`, run `npm run build` in the theme before running it.
- If a change modifies the markup or behaviour of the map or the front page, update `tests/e2e/` (and the seed if needed) in the same change.
- To check that a test detects a regression: introduce it temporarily, rebuild, see the failure and restore `src/` and `assets/` from `HEAD` (`git restore --source=HEAD` + `git clean` of the new `assets/`).
- In CI the tests run inside `mcr.microsoft.com/playwright:v<version>-noble` (`docker run --network host`), without apt or `playwright install`. The version comes from `@playwright/test` in the root `package.json`, which must be exact (`X.Y.Z`); to upgrade Playwright, change it and run `npm install`. Nothing changes locally: `npm run test:e2e` uses the host's browser.
- If `e2e` fails in the «Descargar imagen de Playwright» step, it is an infrastructure failure, not a test failure: re-run the job (`gh run rerun <id> --failed`).
- `tests/e2e/helpers.js` has a temporary exception (`KNOWN_MISSING`) for the 404s of the design fonts; `self-hosted-fonts` removes it.

## 7. Git and GitHub

- Merges into `main` always with a merge commit (`--no-ff` or "Create a merge commit"); never squash or rebase (it would break `.git-blame-ignore-revs`).
- `main` is protected: it requires the `frontend`, `php` and `e2e` checks; the admin can bypass it for local merges.
- GitHub deletes the PR branch on merge ("Automatically delete head branches"); afterwards, `git switch main && git pull && git branch -d <branch>` locally.
- OpenSpec changes: one branch and one PR per change. The archive (`openspec/changes/archive/YYYY-MM-DD-<name>/`, with spec sync if there are specs) goes as the **last commit of that same branch**, with CI green before the merge; never in a separate PR.
- CI only runs on PRs and on pushes to `main`; a push to another branch doesn't trigger it.
