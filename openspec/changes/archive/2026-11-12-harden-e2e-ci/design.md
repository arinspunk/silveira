# Design

## Context

Job `e2e` actual (`.github/workflows/ci.yml`), en `ubuntu-latest`:

1. `actions/setup-node` (Node 22, `.nvmrc`) y `npm ci` en la raíz (instala `@playwright/test` 1.56.1).
2. `npm run e2e:up`: Docker Compose, proyecto `silveira-e2e`, WordPress publicado en `localhost:8081` (`WP_HOME`/`WP_SITEURL` fijados a `http://localhost:8081` en `docker-compose.e2e.yml`).
3. `npm run e2e:seed`: WP-CLI en contenedor.
4. `npx playwright install --with-deps chromium`: descarga Chromium (CDN de Playwright) **y** paquetes de sistema con `apt-get` ← punto de fallo observado.
5. `npm run test:e2e` (`playwright.config.js`: `baseURL: http://localhost:8081`, `CI` activa `retries: 1` y el reporter HTML).

Restricciones:
- WordPress redirige a `WP_HOME`, así que el navegador tiene que llegar al sitio como `http://localhost:8081`; no vale cambiar el host por el nombre del servicio.
- La imagen `mcr.microsoft.com/playwright:v<versión>-noble` trae los navegadores en `/ms-playwright` para esa versión exacta de Playwright; si la librería no coincide, Playwright busca otra revisión del navegador y falla.

## Goals / Non-Goals

**Goals:**
- Que el job `e2e` no dependa de apt ni de la CDN de navegadores de Playwright.
- Que un fallo de red al obtener el navegador falle pronto y con un mensaje claro.
- Mantener exactamente los mismos tests, datos y artefactos de diagnóstico.

**Non-Goals:**
- Cambiar los tests, el seed o el entorno Compose.
- Ejecutar los tests locales en Docker (macOS no soporta bien `--network host`; en local se sigue usando el Playwright del host).
- Eliminar el resto de dependencias de red del job (Docker Hub, npm, wordpress.org para ACF). Se anotan como riesgo.

## Decisions

### D1. `docker run` de la imagen de Playwright en el paso de tests, no `container:` en el job

Paso de tests:

```bash
docker run --rm --network host --ipc host \
  -v "$PWD":/work -w /work -e CI=true \
  mcr.microsoft.com/playwright:v$PW_VERSION-noble \
  npx playwright test
```

- `--network host`: el contenedor comparte la red del runner, así que `http://localhost:8081` llega al WordPress publicado por Compose sin tocar `WP_HOME` ni `playwright.config.js`.
- `--ipc host`: recomendado por Playwright para Chromium en Docker (evita cuelgues por `/dev/shm` pequeño).
- Se reutilizan los `node_modules` del `npm ci` del runner (misma arquitectura linux/x64; `@playwright/test` no tiene binarios nativos), así que no hay segundo `npm ci`.
- El contenedor ejecuta los tests con el Node de la imagen, no con el de `.nvmrc`; el paso imprime `node --version` dentro del contenedor antes de los tests para que quede en el log.
- `CI=true` mantiene `retries: 1`, `forbidOnly` y el reporter HTML; `playwright-report/` y `test-results/` quedan en el workspace y los recoge el paso de subida de artefactos existente.

Alternativa descartada: `container: mcr.microsoft.com/playwright…` a nivel de job. Todo el job correría dentro del contenedor, incluido `docker compose`, que necesitaría el socket de Docker montado, y `localhost:8081` dejaría de apuntar al puerto publicado (habría que usar la red de Compose y cambiar `WP_HOME`). Más piezas móviles para el mismo resultado.

Alternativa descartada: seguir en el runner y cachear `~/.cache/ms-playwright` + reintentos de `install --with-deps`. La caché solo cubre el navegador; las dependencias de sistema siguen saliendo de apt en cada ejecución, que es justo lo que falló.

### D2. La versión de la imagen se deriva de `package.json`

```bash
PW_VERSION=$(node -p "require('./package.json').devDependencies['@playwright/test']")
```

Se exige que sea una versión exacta (`^\d+\.\d+\.\d+$`); si alguien pone un rango (`^1.56.1`), el paso falla con un mensaje explicando que hay que fijar la versión, porque la etiqueta de la imagen debe coincidir con la librería instalada. Hoy ya está fijada (`1.56.1`).

### D3. Descarga de la imagen en un paso propio, con reintentos acotados

`docker pull` en su propio paso: 3 intentos, cada uno limitado con `timeout 150 docker pull …`, 15 s entre ellos y `timeout-minutes: 8` para el paso completo. Así una descarga lenta corta el intento y deja margen para reintentar, en vez de agotar el tiempo del paso en el primero. Si falla, `::error::` indicando que no se pudo descargar la imagen de Playwright de `mcr.microsoft.com` y que es un problema de infraestructura, no de los tests. Separarlo del paso de tests deja claro en la UI de Actions qué falló.

## Risks / Trade-offs

- **`mcr.microsoft.com` también puede caerse** → se cambia una dependencia externa por otra, pero el registro de Microsoft y la imagen versionada son un único artefacto inmutable (sin resolución de paquetes), y el fallo es rápido y con mensaje claro gracias a D3.
- **La imagen pesa ~1,5–2 GB** → la descarga cuesta ~20–40 s en los runners de GitHub, comparable a la instalación actual con apt.
- **Ficheros del informe creados como root** dentro del workspace → no afecta a `upload-artifact` ni a la limpieza del runner (efímero).
- **Otras dependencias de red** (Docker Hub, npm, wordpress.org en el seed) siguen ahí; si alguna da problemas, se trata en otro change.
- **Node de la imagen distinto del de `.nvmrc`** → los tests son JS sencillo sin dependencias de versión de Node; la versión queda en el log (D1) por si algún día importa.
- **Divergencia local/CI**: en local el navegador es el del host y en la CI el de la imagen; ambos son la misma revisión de Chromium para la misma versión de Playwright, así que no se esperan diferencias.
