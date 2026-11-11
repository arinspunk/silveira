# Tasks

## 1. Dependencias

- [x] 1.1 Subir en `composer.json` las restricciones a `squizlabs/php_codesniffer: ^3.13.6` y `wp-coding-standards/wpcs: ^3.4.1` (design D1); ejecutar el `composer update` acotado vía Docker; verificar que el diff de `composer.lock` solo toca PHPCS, WPCS, PHPCSUtils (≥ 1.2.3) y PHPCSExtra (≥ 1.5.1) y que `composer audit` no reporta avisos
- [x] 1.2 Ejecutar `composer run lint` y `composer run analyse` vía Docker; si aparecen infracciones nuevas, corregirlas según design D4 (phpcbf en commit propio + `.git-blame-ignore-revs` si es masivo); verificar que ambos terminan con 0 errores y sin baseline, y si se tocó PHP, que el HTML y las capturas de las páginas del theme no cambian

## 2. CI

- [ ] 2.1 Añadir el paso `composer audit --locked --abandoned=report --no-interaction` al job `php` de `.github/workflows/ci.yml` tras `composer install` (design D2); verificar en la PR que el job `php` pasa en verde con el paso nuevo
- [ ] 2.2 Comprobar que el paso bloquea: en una rama de prueba con una versión vulnerable en `composer.lock` (p. ej. volver a WPCS 3.3.0), verificar que el job `php` falla en `composer audit`; cerrar la PR de prueba y borrar la rama
- [ ] 2.3 Crear `.github/workflows/audit.yml` con `schedule` semanal y `workflow_dispatch` (design D3); verificar lanzándolo a mano desde la pestaña Actions que termina en verde

## 3. Documentación

- [x] 3.1 Documentar en el README (sección de CI) el paso `composer audit`, el workflow semanal y qué hacer cuando falla (actualizar la dependencia afectada en un change pequeño); añadir también a `.claude/skills/silveira-dev-setup/SKILL.md` que `composer audit` forma parte del job `php`; verificar que los comandos documentados funcionan tal cual
- [ ] 3.2 Comprobar en la PR que los tres jobs (`frontend`, `php`, `e2e`) pasan en verde antes del archivado

## Workflow follow-up

- Pedir al equipo que ejecute `composer install` en el theme (vía Docker) tras el merge, para que el hook use las versiones corregidas.
- Fuera de este change: documentar en `silveira-dev-setup` que el archivado va como último commit de la rama del change y, si está activada la opción "Automatically delete head branches", que GitHub borra la rama al mergear.
