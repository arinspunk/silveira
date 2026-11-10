# Tasks

> Orden de aplicación (design D7): grupo 1 → change `harden-map-output` completo → grupos 2–5.

## 1. Desbloquear el build de frontend (antes de `harden-map-output`)

- [x] 1.1 Añadir `L: 'readonly'` a los globals de `src/**/*.js` en `eslint.config.js` (design D6); verificar que `npm run lint` en el theme no reporta `no-undef` para `L`
- [x] 1.2 Corregir el resto de errores de `npm run validate` (ESLint + Stylelint) en `src/`; verificar que `npm run validate` termina con código 0
- [x] 1.3 Aplicar `npm run format` en el theme en un commit propio y añadir su hash a `.git-blame-ignore-revs`; verificar que `npm run format:check` pasa
- [x] 1.4 Crear `.nvmrc` (Node 22) en la raíz y `engines.node` en el `package.json` del theme (design D5); verificar que `nvm use` lo detecta y `npm ci` en el theme funciona
- [x] 1.5 Eliminar `stylelint.config.js` de la raíz; verificar que lint-staged sigue usando el del theme (commit de prueba con un `.scss` modificado ejecuta stylelint sin error de configuración)
- [x] 1.6 Ejecutar `npm run build` y commitear `assets/` regenerado; verificar que `grep -c "silveira:slide-up" assets/js/main-*.js` ≥ 1 y que un segundo `npm run build` no deja cambios en `git status assets/`

## 2. Ampliar y limpiar el análisis PHP (después de `harden-map-output`)

- [x] 2.1 Cambiar `phpcs.xml.dist` para analizar todo el theme (`<file>.</file>` con exclusiones) y configurar `text_domain` y prefijo `silveira` (design D4); verificar con `docker run --rm -v "$PWD":/app -w /app composer:2 vendor/bin/phpcs --report=summary` que se listan todos los ficheros PHP del theme
- [x] 2.2 Ejecutar `phpcbf` vía Docker en un commit propio y añadir su hash a `.git-blame-ignore-revs`; verificar que el número de infracciones baja y que portada, ficha de proyecto, archivos y kitchen sink se ven igual que antes
- [x] 2.3 Corregir manualmente las infracciones PHPCS restantes (escapes, `date()`→`wp_date()`, docblocks, Yoda, etc.) sin cambio funcional; verificar que `composer run lint` (vía Docker) termina con 0 errores y revisión visual de las mismas páginas
- [x] 2.4 Añadir `php-stubs/acf-pro-stubs` a `require-dev`, configurar `phpstan.neon.dist` con `paths: [.]`, exclusiones, nivel 5 y los stubs; verificar que `composer run analyse` (vía Docker) analiza todos los ficheros
- [x] 2.5 Corregir los errores de PHPStan nivel 5; verificar que `composer run analyse` termina con 0 errores y sin baseline

## 3. Hook pre-commit

- [x] 3.1 Reescribir `scripts/lint-staged-phpcs.sh` con la cascada PHP local → Docker `composer:2` → aviso visible (design D3), convirtiendo rutas a relativas al theme; verificar con un PHP con una infracción deliberada que el commit se bloquea en un equipo sin `php` pero con Docker, y que sin Docker sale el aviso
- [x] 3.2 Crear `scripts/build-assets.sh` y la entrada de lint-staged que lo ejecuta una vez por commit cuando hay cambios en `src/**`, `vite.config.js` o `package-lock.json` del theme (design D2); verificar que un commit que solo toca un `.scss` incluye también los ficheros actualizados de `assets/` y el `manifest.json`
- [x] 3.3 Verificar que un commit que no toca `src/` no ejecuta el build (tiempo del hook sin cambios apreciables)

## 4. CI en GitHub Actions

- [x] 4.1 Crear `.github/workflows/ci.yml` con el job `frontend` (`npm ci`, `validate`, `format:check`, `build`, `git diff --exit-code` + `git status --porcelain` sobre `assets/`, mensaje explícito al fallar) (design D1); verificar en una PR que el job pasa en verde
- [x] 4.2 Añadir el job `php` (setup-php 8.2, `composer install`, `composer run lint`, `composer run analyse`); verificar en la misma PR que pasa en verde
- [x] 4.3 Comprobar el bloqueo: en una rama de prueba, modificar un `.scss` y commitear con `--no-verify` sin recompilar; verificar que el job `frontend` falla por diferencias en `assets/`; descartar la rama
- [x] 4.4 Comprobar reproducibilidad: comparar el `assets/` generado en local con el de CI (si falla 4.1 por diferencias sin cambios en `src/`, investigar antes de continuar)

## 5. Documentación

- [x] 5.1 Actualizar `README.md`: sección de CI (qué comprueba y cómo reproducir en local), hook (build automático de `assets/`, PHPCS vía Docker), comandos PHP con Docker y nota sobre `.git-blame-ignore-revs` (`git config blame.ignoreRevsFile .git-blame-ignore-revs`); verificar que los comandos documentados se ejecutan tal cual desde un clon limpio

## Workflow follow-up

- Activar en GitHub la protección de rama de `main` exigiendo los checks `frontend` y `php` (acción del propietario del repo).
- Pedir al equipo que ejecute `npm install` en la raíz para refrescar los hooks.
- Archivar el change tras la revisión.
