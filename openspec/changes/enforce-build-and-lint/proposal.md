# Proposal

## Why

El theme se despliega con los assets compilados versionados en `assets/`, pero nada garantiza que coincidan con `src/`. Hoy `assets/` es del 22 de abril y `src/` del 4 de mayo, y producción no incluye funcionalidad ya escrita. El control de calidad también tiene huecos que dejan pasar código sin revisar:

- `npm run build` probablemente falla en `prebuild`, porque ESLint no conoce la global `L` de Leaflet que usa `map.js`.
- PHPCS y PHPStan solo analizan 4 de unos 20 ficheros PHP, y PHPStan está en nivel 0.
- El hook de PHPCS se salta en silencio si no hay PHP en el host, que es el caso habitual en este equipo.
- No existe CI.

## What Changes

- **CI en GitHub Actions** (push a `main` y pull requests), que bloquea si falla cualquiera de estos pasos:
  - lint de JS/SCSS y comprobación de formato (Prettier);
  - PHPCS y PHPStan sobre todo el theme;
  - rebuild de los assets y comparación con `assets/` versionado. Si hay diferencias, falla.
- **Hook pre-commit**:
  - si se han preparado para commit ficheros de `src/` o la configuración de Vite, recompila y añade `assets/` al commit;
  - PHPCS se ejecuta con el PHP local o, si no hay, con Docker (`composer:2`). Solo se salta con un aviso visible cuando no hay ninguno de los dos.
- **Configuración de linters**:
  - ESLint declara la global `L` de Leaflet;
  - PHPCS y PHPStan pasan a cubrir todo el theme salvo `vendor/`, `node_modules/` y `assets/`;
  - PHPStan sube de nivel e incorpora stubs de ACF;
  - se fija la versión de Node para que el build sea reproducible.
- **Corrección de todas las infracciones existentes** que afloren (PHPCS, PHPStan, ESLint, Stylelint, Prettier), sin cambiar el comportamiento del sitio.
- **Limpieza**: se elimina el `stylelint.config.js` duplicado de la raíz, que nadie usa.
- **Documentación**: se actualiza el README (CI, hook y PHP vía Docker).

## Capabilities

### New Capabilities
<!-- Ninguna: cambio de herramientas y calidad, sin cambio de comportamiento observable del sitio. -->

### Modified Capabilities
<!-- Ninguna. El change declara skip_specs: true en .openspec.yaml. -->

## Impact

- **Nuevos**: `.github/workflows/ci.yml` y `.nvmrc` (o `engines` en `package.json`).
- **Configuración**:
  - de herramientas: `package.json` (raíz, bloque `lint-staged`), `scripts/lint-staged-phpcs.sh`, nuevo script para el build en el hook;
  - del theme: `eslint.config.js`, `phpcs.xml.dist`, `phpstan.neon.dist` (más posible `phpstan-baseline.neon` vacío) y `composer.json`/`composer.lock` (stubs de ACF).
- **Código PHP del theme**: todas las plantillas, `inc/`, `template-parts/` y `functions.php`, solo con correcciones de estándar sin cambio funcional.
- **Fuentes JS/SCSS**: reformateo con Prettier y arreglos de lint.
- **`assets/`**: build regenerado.
- **`README.md`**: sección de calidad y hooks.
- **Aplicación en dos tramos** (ver design D7): el grupo 1 (desbloquear el build) se aplica primero; después `harden-map-output`, que corrige el XSS sin esperar a la limpieza de lint; y a continuación los grupos 2–5 de este change.
