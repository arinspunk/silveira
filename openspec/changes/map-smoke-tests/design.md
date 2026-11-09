# Design

## Context

Ver proposal.md. `docker-compose.yml` levanta `db` (MariaDB 10.11), `wordpress` (php8.2-apache, puerto 80, `WP_HOME` fijo a `http://silveira.localhost`) y `wpcli` (perfil `tools`). El theme se monta desde el repo; WordPress y los plugins viven en el volumen `wp_data`, no en el repo. Basta con la versión gratuita de ACF: los campos del grupo Projeto son `text` y `number` (`projeto_lema`, `projeto_endereco`, `projeto_latitude`, `projeto_longitude`). Taxonomías: `modalidade_projeto` y `territorio` (jerárquica: comarca → localidade).

## Goals / Non-Goals

**Goals:** detectar en cada PR que la portada y el mapa siguen funcionando con los assets versionados; ejecutable igual en local y en CI; menos de ~3 minutos en CI.

**Non-Goals:** cobertura funcional completa; tests visuales por captura; tests de accesibilidad automatizados (se puede añadir axe más adelante); probar el geocoding contra Nominatim.

## Decisions

### D1. Entorno aislado con Compose
`docker compose -p silveira-e2e -f docker-compose.yml -f docker-compose.e2e.yml`. El override:
- publica en `8081:80` con `ports: !override` (Compose ≥ 2.24) para no chocar con el entorno de desarrollo;
- redefine `WORDPRESS_CONFIG_EXTRA` con `WP_HOME`/`WP_SITEURL` = `http://localhost:8081`;
- fuerza `SILVEIRA_VITE_DEV=0`, para probar `assets/` tal como se despliega.

El nombre de proyecto `silveira-e2e` da volúmenes propios; el script de limpieza hace `down -v`.

### D2. Datos con WP-CLI
`scripts/e2e-seed.sh` (idempotente, parte de una instalación vacía):
1. `wp core install` con usuario admin de prueba; `wp theme activate silveira`; `wp plugin install advanced-custom-fields --activate`; `wp rewrite structure '/%postname%/'`.
2. Términos: dos modalidades, una comarca con una localidade hija.
3. Proyectos: tres con coordenadas en Galiza y modalidades distintas, uno sin coordenadas (no debe aparecer en el mapa) y uno con título `<img src=x onerror="window.__xss=1">Horta` y lema `</script><script>window.__xss=1</script>`.
- Los campos se rellenan con `wp eval` + `update_field()` para que ACF guarde también las referencias de campo.
- Ningún proyecto lleva `projeto_endereco`, para que el guardado no llame a Nominatim.

### D3. Tests
`tests/e2e/home.spec.js` y `tests/e2e/projeto.spec.js` con `@playwright/test` y el Chromium preinstalado/`npx playwright install chromium` en CI. Selectores por clases BEM existentes (`.leaflet-marker-icon`, `.c-project-list-item`, `.c-map-card`) y por texto; donde el marcado vaya a cambiar (desplegables en `map-filters-fixes`), se usa el rol o el `name` del checkbox, para reducir mantenimiento.
- Errores: se recogen `page.on('pageerror')` y `console` de tipo `error` y el test falla si hay alguno; también si el HTML contiene `Fatal error` o `Warning:`.
- XSS: `page.on('dialog')` falla el test; se comprueba `window.__xss === undefined`, que no existe `img[src="x"]` en el mapa ni en la lista y que el texto literal es visible.
- Los marcadores se cuentan tras esperar a `.leaflet-marker-icon`; el número esperado sale del seed (3).

### D4. CI
Job `e2e` en `.github/workflows/ci.yml`, en paralelo a `frontend` y `php`: `docker compose … up -d --wait`, seed con el servicio `wpcli`, `npm ci` en la raíz, `npx playwright install --with-deps chromium`, `npm run test:e2e`, y sube el informe HTML como artefacto si falla. Se añade a los checks obligatorios de `main`.

## Risks / Trade-offs

- [Tests frágiles por cambios de marcado en changes posteriores] → selectores por rol/texto donde se pueda; cada change que cambie el marcado del mapa tiene una tarea para actualizar el test.
- [Tiempo de CI por levantar MariaDB y WordPress] → imágenes cacheadas por el runner; `--wait` con el healthcheck existente.
- [`plugin install` depende de wordpress.org] → aceptable; si falla de forma recurrente, fijar versión y cachear el zip.
- [La portada depende de ACF hasta `theme-robustness`] → el seed siempre activa ACF; el caso "sin ACF" lo cubre `theme-robustness` con su propio escenario manual.
