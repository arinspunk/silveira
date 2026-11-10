# Proposal

## Why

Todas las verificaciones de los changes son manuales. Cuatro changes seguidos (`harden-map-output`, `map-filters-fixes`, `homepage-scroll-a11y`, `third-party-assets`) tocan `map.js`, y `self-hosted-fonts`, `theme-robustness` y `consistent-templates` tocan PHP que usa la portada. Sin una comprobación automática, una regresión en el mapa (no pinta marcadores, el filtro deja de filtrar, vuelve el XSS) solo se detecta si alguien repite a mano los escenarios.

## What Changes

- **Smoke test end-to-end con Playwright** contra WordPress levantado con Docker y los assets compilados (`SILVEIRA_VITE_DEV=0`). Comprueba:
  - la portada carga sin errores de PHP ni de consola y el mapa pinta un marcador por proyecto con coordenadas;
  - la lista lateral muestra esos proyectos y al pulsar un marcador se abre su popup;
  - un filtro de modalidade reduce lista y marcadores;
  - un proyecto con un título que contiene HTML malicioso se muestra como texto, sin ejecutar nada (regresión de `harden-map-output`);
  - la ficha de un proyecto responde 200 sin errores de consola.
- **Datos de prueba reproducibles**: un script con WP-CLI instala WordPress, activa el theme y ACF, y crea proyectos, modalidades y territorios de prueba.
- **Entorno aislado**: proyecto de Docker Compose y volúmenes propios, en otro puerto, para no tocar la base de datos de desarrollo.
- **Job `e2e` en la CI** de `enforce-build-and-lint`, bloqueante como los demás.

## Capabilities

### New Capabilities
<!-- Ninguna: herramienta de calidad sin cambio de comportamiento del sitio. -->

### Modified Capabilities
<!-- Ninguna. skip_specs: true. -->

## Impact

- **Nuevos:** `tests/e2e/*.spec.js`, `playwright.config.js` (raíz), `docker-compose.e2e.yml`, `scripts/e2e-seed.sh`.
- **Configuración:** `package.json` de la raíz (`@playwright/test` y script `test:e2e`), `.github/workflows/ci.yml` (job `e2e`), `.gitignore` (informes de Playwright).
- **README:** cómo ejecutar el smoke test en local.
- **Orden recomendado:** justo después de `enforce-build-and-lint` completo (necesita su CI) y, por tanto, después de `harden-map-output`. Los changes posteriores mantienen el test al día si cambian el marcado que usa.
