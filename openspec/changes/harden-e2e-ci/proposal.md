# Proposal

## Why

El job `e2e` de la CI instala Chromium y sus dependencias de sistema con `npx playwright install --with-deps chromium`, que ejecuta `apt-get` contra los mirrors de Ubuntu del runner. El 11-11-2026 el runner dejó de resolver `azure.archive.ubuntu.com` y el job falló tres veces seguidas durante más de una hora (≈11 min cada intento colgado en apt), sin llegar a ejecutar ningún test. Como `e2e` es un check obligatorio en `main`, una caída de infraestructura ajena al código bloqueó el merge de una PR que solo tocaba documentación (#10) hasta el día siguiente.

## What Changes

- **Los tests de Playwright se ejecutan dentro de la imagen oficial `mcr.microsoft.com/playwright`**, que ya trae Chromium y todas sus dependencias de sistema. Se elimina el paso `npx playwright install --with-deps chromium`: el job `e2e` deja de usar apt.
- **La versión de la imagen sale de `package.json`** (`@playwright/test`, hoy 1.56.1), de modo que el navegador de la imagen y la librería de los tests no pueden desincronizarse; subir Playwright sigue siendo cambiar un solo sitio.
- **Fallo rápido y legible si el registro no responde**: la descarga de la imagen se reintenta un número acotado de veces y, si falla, el job termina con un error que dice qué falló, en vez de quedarse colgado hasta el timeout.
- El resto del job no cambia: `npm ci`, `e2e:up` (WordPress aislado en el puerto 8081), `e2e:seed`, los mismos tests, el informe de Playwright y los logs de WordPress como artefactos si falla.
- La ejecución local (`npm run test:e2e` con el Playwright del host) no cambia. Se actualizan el README y la skill `silveira-dev-setup` para describir cómo corre `e2e` en la CI.

## Capabilities

### New Capabilities

Ninguna.

### Modified Capabilities

Ninguna. Es un cambio de infraestructura de CI sin cambio funcional en el sitio (`skip_specs: true`).

## Impact

- `.github/workflows/ci.yml` (job `e2e`).
- `README.md` (sección de CI / Smoke test) y `.claude/skills/silveira-dev-setup/SKILL.md` (sección E2E).
- Nueva dependencia externa en la CI: el registro `mcr.microsoft.com` sustituye a los mirrors de apt de Ubuntu para el navegador. Siguen existiendo las dependencias que ya había: Docker Hub (imágenes de WordPress y MariaDB), npm y wordpress.org (instalación de ACF en el seed).
- Sin cambios en el theme, `assets/`, los tests ni los datos de prueba.
