---
name: silveira-dev-setup
description: Prepara y comprueba el entorno de desarrollo de silveira (hooks de Git, Node 22, dependencias del theme, PHPCS/PHPStan vía Docker, stack local de WordPress) y reproduce la CI en local. Úsala al empezar a trabajar en un clon, antes del primer commit de una sesión, cuando un hook o la CI fallen, o cuando haga falta levantar el sitio para verificar cambios.
---

# Entorno de desarrollo de silveira

El host **no tiene PHP**: todo lo PHP va por Docker. `assets/` está versionado y la CI comprueba que coincide con `src/`.

## 1. Comprobar el clon (siempre, antes del primer commit)

```bash
git config --get core.hooksPath          # debe ser .husky/_
ls node_modules/.bin/lint-staged         # deps de la raíz
ls wp-content/themes/silveira/node_modules/.bin/vite   # deps del theme
node -v                                  # v22.x (.nvmrc)
docker info >/dev/null && echo docker-ok
```

Si falta algo, arreglarlo:

```bash
nvm use                                   # Node 22
npm install                               # raíz: instala husky y activa los hooks
(cd wp-content/themes/silveira && npm ci)
docker run --rm -v "$PWD/wp-content/themes/silveira":/app -w /app composer:2 composer install -q
```

**Sin `core.hooksPath` no se ejecuta ningún hook**: ni lint, ni PHPCS, ni el rebuild de `assets/`. No afirmar que un commit "pasó por el hook" sin haberlo comprobado.

## 2. Qué hace el hook pre-commit

`lint-staged.config.mjs`, en serie (`--concurrent false`):
- JS/config → ESLint `--fix` + Prettier; CSS/SCSS → Stylelint `--fix` + Prettier.
- PHP → `scripts/lint-staged-phpcs.sh`: PHP local → Docker `composer:2` → aviso visible y exit 0.
- `src/**`, `vite.config.js` o `package-lock.json` staged → `scripts/build-assets.sh` (build + `git add assets/`), una vez por commit.

Consecuencia: no hace falta tarea de build en los changes; el commit incluye `assets/`. Si se commitea con `--no-verify`, la CI fallará por `assets/` desfasado.

## 3. Reproducir la CI en local

Desde `wp-content/themes/silveira`:

```bash
npm ci && npm run validate && npm run format:check && npm run build
git status --porcelain -- assets/        # debe salir vacío
docker run --rm -v "$PWD":/app -w /app composer:2 sh -c 'composer install -q && composer audit --locked --abandoned=report && composer run lint && composer run analyse'
```

- PHPCS (WPCS, text domain y prefijo `silveira`) y PHPStan nivel 5 cubren todo el theme; **0 errores, sin baseline**. Variables de plantilla con prefijo `silveira_`.
- `composer audit` es un paso bloqueante del job `php` (y hay una ejecución semanal en `audit.yml`). Si falla, actualizar solo el paquete afectado en un change pequeño, subiendo su restricción en `composer.json`, con `composer update <paquete>` acotado (sin `--with-dependencies` si no hace falta).
- `composer run lint:fix` (phpcbf) para lo automático; si es un reformateo masivo, commit propio y añadir su hash a `.git-blame-ignore-revs`.
- Para que el build en Linux sea idéntico: mismo lockfile y Node 22. Se puede verificar en un clon limpio con `docker run node:22`.

## 4. Levantar el sitio local para verificar

```bash
docker compose up -d                       # http://silveira.localhost (puerto 80 fijo por WP_HOME)
docker compose --profile tools run --rm -T wpcli wp <comando>   # WP-CLI: siempre con el prefijo "wp"
```

- **Si el puerto 80 está ocupado por otro proyecto, preguntar antes de parar sus contenedores** y volver a arrancarlos al terminar.
- Ejecutar `docker compose` **desde la raíz de este repo**: desde otro directorio (p. ej. un clon temporal) crea un proyecto nuevo con sus propios contenedores y volúmenes.
- Si los volúmenes son nuevos, WordPress está sin instalar: `wp core install`, `wp theme activate silveira`, `wp plugin install advanced-custom-fields --activate` (el theme usa `get_field`), `wp rewrite structure '/%postname%/'`, y sembrar proyectos con `projeto_latitude`/`projeto_longitude` vía `wp eval-file -` (con `update_field` no se dispara el geocoding).
- Modo dev de Vite: `SILVEIRA_VITE_DEV=1 docker compose up -d wordpress` y `npm run dev` en el theme; volver a `0` al terminar.

## 5. Verificar cambios sin regresiones

Para cambios "sin cambio funcional" (lint, reformateo, refactor): capturar HTML normalizado (sin `?ver=`, hashes de Vite ni nonces) y capturas de pantalla de portada, ficha de proyecto, archivos, singles, taxonomía, Kitchen Sink y 404 **antes y después**, y compararlos. Capturar dos veces el "antes" para confirmar que es determinista. La portada se compara por HTML (los tiles del mapa no son deterministas). Playwright (`playwright-core` con `channel: 'chrome'`) instalado en el scratchpad, nunca en el repo.

## 6. Smoke test E2E (Playwright)

Entorno aislado (proyecto Compose `silveira-e2e`, puerto **8081**, volúmenes propios, assets compilados): no necesita el puerto 80 ni toca la base de datos de desarrollo.

```bash
npm run e2e:up && npm run e2e:seed && npm run test:e2e   # desde la raíz
npm run e2e:down                                         # al terminar (borra sus volúmenes)
```

- El test usa `assets/` compilado: tras cambiar `src/`, `npm run build` en el theme antes de ejecutarlo.
- Si un change modifica el marcado o el comportamiento del mapa o de la portada, actualizar `tests/e2e/` (y el seed si hace falta) en el mismo change.
- Para comprobar que un test detecta una regresión: introducirla temporalmente, recompilar, ver el fallo y restaurar `src/` y `assets/` desde `HEAD` (`git restore --source=HEAD` + `git clean` de los `assets/` nuevos).
- `tests/e2e/helpers.js` tiene una excepción temporal (`KNOWN_MISSING`) para los 404 de las fuentes del diseño; la quita `self-hosted-fonts`.

## 7. Git y GitHub

- Merges a `main` siempre con merge commit (`--no-ff` o "Create a merge commit"); nunca squash ni rebase (rompería `.git-blame-ignore-revs`).
- `main` está protegida: exige los checks `frontend` y `php`; el admin puede saltársela para merges locales.
- La CI solo se ejecuta en PRs y en pushes a `main`; un push a otra rama no la dispara.
