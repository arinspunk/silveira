# Proposal

## Why

`composer audit` en el theme reporta tres avisos de seguridad de gravedad alta en las herramientas PHP de desarrollo: `squizlabs/php_codesniffer` 3.13.5 (CVE-2026-67434, inyección de comandos; afecta a <3.13.6), `phpcsstandards/phpcsutils` 1.2.2 (CVE-2026-65954, ejecución de código; <1.2.3) y `wp-coding-standards/wpcs` 3.3.0 (CVE-2026-45293, ejecución de código; <3.4.1). No llegan a producción (son `require-dev`), pero se ejecutan en el hook pre-commit y en la CI sobre el código de cada PR. Se detectaron durante `enforce-build-and-lint` y no quedaron registrados en ningún sitio; además, nada avisa si aparece un aviso nuevo.

## What Changes

- **Actualizar las tres herramientas** a la versión corregida dentro de su versión mayor actual: PHPCS ≥ 3.13.6 (sin saltar a PHPCS 4), WPCS ≥ 3.4.1 y, a través de WPCS, PHPCSUtils ≥ 1.2.3 y PHPCSExtra ≥ 1.5.1. Se suben las restricciones de `composer.json` de PHPCS y WPCS para que no se pueda volver a una versión vulnerable.
- **Mantener el estándar a cero**: `composer run lint` y `composer run analyse` deben seguir terminando con 0 errores y sin baseline. Si la nueva WPCS añade o endurece reglas, se corrigen las infracciones sin cambio funcional.
- **`composer audit` en la CI**:
  - nuevo paso bloqueante en el job `php`, para que un aviso en dependencias PHP no llegue a `main`;
  - nuevo workflow programado semanal que solo ejecuta `composer audit`, para que un aviso publicado se vea aunque nadie abra una PR;
  - los paquetes abandonados se informan sin bloquear (`--abandoned=report`).
- Fuera de alcance: PHPStan y `szepeviktor/phpstan-wordpress` (tienen actualizaciones, pero sin avisos; van en otro change si se quiere), PHPCS 4, las dependencias npm y la documentación del flujo de archivado en la skill `silveira-dev-setup` (commit aparte, sin relación con este change).

## Capabilities

### New Capabilities
<!-- Ninguna: cambio de herramientas sin comportamiento observable del sitio. -->

### Modified Capabilities
<!-- Ninguna. El change declara skip_specs: true en .openspec.yaml. -->

## Impact

- **Dependencias**: `wp-content/themes/silveira/composer.json` y `composer.lock` (solo `squizlabs/php_codesniffer`, `wp-coding-standards/wpcs`, `phpcsstandards/phpcsutils` y `phpcsstandards/phpcsextra`; las dos últimas como dependencias de WPCS).
- **Código PHP del theme**: solo si la nueva WPCS reporta infracciones nuevas; correcciones sin cambio funcional.
- **CI**: `.github/workflows/ci.yml` (paso `composer audit` en el job `php`) y nuevo `.github/workflows/audit.yml` (ejecución semanal).
- **Documentación**: README (qué hace `composer audit` en la CI y qué hacer cuando falla).
- **Equipo**: tras el merge, cada persona ejecuta `composer install` en el theme para actualizar su `vendor/`.
- Sin cambios en `assets/` ni en el comportamiento del sitio; el smoke test E2E debe seguir en verde.
