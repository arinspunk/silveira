# Tasks

> Aplicar después de `enforce-build-and-lint` completo (necesita su CI) y de `harden-map-output`.

## 1. Entorno y datos

- [x] 1.1 Crear `docker-compose.e2e.yml` (design D1); verificar que `docker compose -p silveira-e2e -f docker-compose.yml -f docker-compose.e2e.yml up -d --wait` levanta WordPress en `http://localhost:8081` mientras el entorno de desarrollo sigue en el puerto 80 con sus datos intactos
- [x] 1.2 Crear `scripts/e2e-seed.sh` (design D2); verificar que dos ejecuciones seguidas dejan los mismos proyectos y términos, y que ningún guardado hace peticiones a Nominatim
- [x] 1.3 Añadir un script de limpieza (`down -v` del proyecto `silveira-e2e`); verificar que no deja volúmenes `silveira-e2e_*` en `docker volume ls`

## 2. Tests

- [x] 2.1 Añadir `@playwright/test` y el script `test:e2e` al `package.json` de la raíz, `playwright.config.js` con `baseURL` `http://localhost:8081` y los informes en `.gitignore`; verificar que `npm run test:e2e` arranca sin tests
- [x] 2.2 Escribir `tests/e2e/home.spec.js`: portada sin errores, 4 marcadores (3 normales y el de XSS), lista con 4 elementos, popup al pulsar un marcador, filtro de modalidade que reduce lista y marcadores (design D3); verificar que pasa en local
- [x] 2.3 Añadir el caso XSS y `tests/e2e/projeto.spec.js` (design D3); verificar que pasa y que falla si se revierte temporalmente `buildPopup`/`buildListItem` a `innerHTML` en `src/js/map.js` (comprobar y descartar el cambio)

## 3. CI y documentación

- [x] 3.1 Añadir el job `e2e` a `.github/workflows/ci.yml` (design D4); verificar en una PR que pasa en verde y que el informe se sube como artefacto en un fallo provocado
- [x] 3.2 Documentar en el README cómo levantar el entorno, sembrar, ejecutar y limpiar; verificar que los comandos funcionan tal cual desde un clon limpio

## Workflow follow-up

- Añadir el check `e2e` a la protección de rama de `main` (acción del propietario del repo).
