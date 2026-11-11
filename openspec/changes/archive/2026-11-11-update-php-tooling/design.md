# Design

## Context

Ver proposal.md (Why). Estado actual en `wp-content/themes/silveira`:

- `composer.json` (`require-dev`): `squizlabs/php_codesniffer: ^3.10`, `wp-coding-standards/wpcs: ^3.1`; `phpcsstandards/phpcsutils` (1.2.2) y `phpcsstandards/phpcsextra` llegan como dependencias de WPCS.
- WPCS 3.4.1 exige `php_codesniffer ^3.13.5`, `phpcsutils ^1.2.3` y `phpcsextra ^1.5.1`; no admite PHPCS 4. PHPCSExtra 1.5.1 exige a su vez `phpcsutils ^1.2.3`.
- PHP solo vía Docker (`composer:2`) en local; en CI, `shivammathur/setup-php` con PHP 8.2.
- Estándar actual: PHPCS (WPCS, text domain y prefijo `silveira`) y PHPStan nivel 5 con 0 errores y sin baseline; la CI tiene los jobs `frontend`, `php` y `e2e`, todos obligatorios en `main`.

## Goals / Non-Goals

**Goals:**
- `composer audit` sin avisos en el theme.
- Imposible volver a instalar una versión vulnerable con `composer install`/`update`.
- Lint y análisis siguen a 0 errores; el sitio no cambia.
- Un aviso nuevo en dependencias PHP se ve en la CI.

**Non-Goals:**
- PHPCS 4 (WPCS 3.x no lo soporta).
- Actualizar PHPStan, `szepeviktor/phpstan-wordpress` o los stubs (sin avisos; posibles errores nuevos fuera del objetivo).
- Auditoría de dependencias npm.

## Decisions

### D1. Actualización mínima dentro de la versión mayor
`composer.json`: `squizlabs/php_codesniffer: ^3.13.6` y `wp-coding-standards/wpcs: ^3.4.1`. PHPCSUtils no se añade a `require-dev`: WPCS 3.4.1 ya exige `^1.2.3`, así que el mínimo seguro queda garantizado mientras WPCS esté en `^3.4.1`. PHPCSExtra sube a 1.5.1 por la misma vía.
- PHPCS se fija explícitamente en `^3.13.6` porque WPCS solo exige `^3.13.5`, que sigue siendo vulnerable.
Comando (vía Docker): `composer update squizlabs/php_codesniffer wp-coding-standards/wpcs phpcsstandards/phpcsutils phpcsstandards/phpcsextra`, **sin** `--with-dependencies`: con esa opción también sube `dealerdirect/phpcodesniffer-composer-installer` (1.2.0 → 1.2.1), que nadie exige (PHPCSUtils acepta `^1.0`).
- *Alternativa*: `composer update` completo. Descartada: arrastraría PHPStan 2.3 y otros paquetes fuera de alcance, con posibles errores nuevos de análisis.
- *Alternativa*: `config.audit.block-insecure` de Composer en lugar de restricciones. Complementa, pero depende de la versión de Composer del entorno; las restricciones explícitas son deterministas.

### D2. `composer audit` en el job `php`, bloqueante
Paso `composer audit --locked --abandoned=report --no-interaction` tras `composer install`. Bloqueante: un aviso de gravedad alta en herramientas que ejecutan código de las PRs debe atenderse.
- `--abandoned=report`: desde Composer 2.7, `audit` también falla por defecto si un paquete está marcado como abandonado. Eso no es una vulnerabilidad, así que se informa en el log sin bloquear.
- *Trade-off*: un aviso nuevo publicado en cualquier momento pondrá en rojo PRs que no tocan PHP. Se acepta: el arreglo suele ser un change pequeño como este, y dejarlo en aviso es lo que hizo que se perdiera esta vez.
- *Alternativa*: `continue-on-error: true` (solo aviso). Descartada por lo anterior.

### D3. Auditoría semanal programada
Nuevo `.github/workflows/audit.yml` con `schedule` (cron semanal, lunes a primera hora UTC) y `workflow_dispatch`: PHP 8.2 con `setup-php`, `composer install` y el mismo `composer audit` de D2, en `wp-content/themes/silveira`. Así un aviso publicado se detecta aunque no haya PRs, y GitHub envía un email al fallar.
- Se separa de `ci.yml` para no ejecutar `frontend` ni `e2e` cada semana.
- No se añade a la protección de rama: no se ejecuta en PRs.

### D4. Infracciones nuevas de WPCS
Si WPCS 3.4.x reporta infracciones nuevas, se corrigen igual que en `enforce-build-and-lint`: `phpcbf` en commit propio (y su hash en `.git-blame-ignore-revs` si es masivo) y correcciones manuales sin cambio funcional, verificadas con el smoke test E2E y la comparación de HTML/capturas de las páginas del theme.

## Risks / Trade-offs

- [WPCS 3.4.x endurece reglas y aparecen muchas infracciones] → D4; si el volumen fuese grande, se valora antes de seguir.
- [CI en rojo por avisos ajenos a la PR] → D2, aceptado.
- [`composer audit` necesita red] → el runner la tiene; en local se ejecuta igual vía Docker.
- [GitHub desactiva los workflows programados tras 60 días sin actividad en un repo público] → se avisa por email; reactivarlo desde la pestaña Actions.
- [`vendor/` local desactualizado tras el merge] → el hook seguiría usando las versiones vulnerables en local; follow-up para que el equipo ejecute `composer install`.
