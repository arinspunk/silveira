# Tasks

## 1. CI

- [ ] 1.1 En el job `e2e` de `.github/workflows/ci.yml`, añadir un paso que obtenga `PW_VERSION` de `devDependencies['@playwright/test']` en `package.json` y falle con `::error::` si no es una versión exacta `X.Y.Z` (design D2); exportarla vía `$GITHUB_ENV`
- [ ] 1.2 Añadir el paso de descarga de `mcr.microsoft.com/playwright:v$PW_VERSION-noble` con 3 intentos de `timeout 150 docker pull`, 15 s de espera, `timeout-minutes: 8` y `::error::` explicando que es un fallo de infraestructura si no se consigue (design D3)
- [ ] 1.3 Sustituir `npx playwright install --with-deps chromium` y `npm run test:e2e` por un paso que ejecute `npx playwright test` dentro de esa imagen con `--network host --ipc host`, el workspace montado y `CI=true`, imprimiendo antes `node --version` dentro del contenedor (design D1); mantener sin cambios `e2e:up`, `e2e:seed`, la subida del informe y los logs de WordPress
- [ ] 1.4 Verificar en la PR: el job `e2e` pasa, su log no contiene `apt-get` ni `playwright install`, el informe confirma que se ejecutaron todos los tests (mismo número que en `main`) la versión de Playwright usada es la de `package.json`, y anotar en la PR la versión de Node de la imagen
- [ ] 1.5 Verificar que el job sigue bloqueando: en una rama de prueba creada desde la del change, con un commit que rompa un test, abrir una PR en borrador y comprobar que `e2e` falla y sube `playwright-report`; cerrar la PR y borrar la rama (el commit roto no debe llegar a la rama del change ni a `main`)

## 2. Documentación

- [ ] 2.1 Actualizar en `README.md` la descripción del job `e2e` (tabla de CI y sección Smoke test): los tests corren en la imagen `mcr.microsoft.com/playwright` con la versión de `package.json`; en local se sigue usando `npm run test:e2e`; para subir Playwright basta con cambiar la versión en `package.json` (y `npm install`)
- [ ] 2.2 Actualizar la sección E2E de `.claude/skills/silveira-dev-setup/SKILL.md` con lo mismo y con qué hacer si `e2e` falla al descargar la imagen (reintentar el job; no es un fallo de los tests)
