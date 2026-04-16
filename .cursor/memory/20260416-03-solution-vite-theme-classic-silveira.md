# Solución — **Vite** en theme WordPress classic (proyecto silveira)

## Resumen del problema

Desarrollar el theme **classic** del proyecto **silveira** con **SCSS/CSS y JS** usando **Vite**, sobre el stack ya definido (WordPress en **Docker**, theme en **bind mount**). Se busca: HMR en desarrollo, **assets estáticos** en producción local (`wp_enqueue_*`), y **calidad de código** con ESLint/Prettier/Stylelint y, donde aplique, PHPCS/PHPStan, ejecutados de forma **habitual** vía **Husky + lint-staged** y **`prebuild`** antes de `npm run build`.

## Decisiones cerradas (silveira)

| Tema | Decisión |
|------|-----------|
| Ubicación de Vite | **Dentro del theme** `wp-content/themes/silveira/` (un `package.json`, `base` = `/wp-content/themes/silveira/`). |
| Node en Docker | **No** obligatorio; Node en el host para `dev`/`build`/lint. |
| Calidad en el flujo | **Husky + lint-staged** en `pre-commit`; script **`validate`** en hook npm **`prebuild`** antes de `vite build`. |
| URL del sitio WP | **`http://silveira.localhost`** (coherente con el entorno Docker ya montado; ver otros docs de memoria si cambia puerto/host). |
| Manifest | **Sí** `build.manifest` (p. ej. salida bajo `assets/` con `manifest.json` en la carpeta que genere Vite en la versión elegida) para encolar chunks hasheados desde PHP. |

La opción “Vite en raíz del monorepo” queda solo si más adelante hay varios paquetes o themes compartiendo toolchain (sección *Alternativas*).

## Enfoque propuesto (KISS)

En el theme: **`package.json`**, **`vite.config`**, entrada **`src/main.js`** que importa **`src/scss/main.scss`**, salida compilada hacia **`assets/`** (y manifest para mapear entrada → ficheros finales).

**Dos modos claros:**

1. **Desarrollo:** `npm run dev` en el host. Vite sirve en un origen distinto (p. ej. `http://localhost:5173`). WordPress responde en **`http://silveira.localhost`**. Hay que encolar `@vite/client` + entrada en modo desarrollo y alinear **`server` / CORS / HMR** con el host real del navegador (detalle en la siguiente sección).

2. **Build / máquina sin intención de HMR:** `npm run build` (no solo `vite build` a mano si quieres que corran **`prebuild`** y el lint acordado). Salida en `assets/`; `functions.php` lee el **manifest** y encola CSS/JS con versión basada en `filemtime()` del manifest o del asset.

Flujo resumido: **Node en el host**, **WP en Docker**, theme como carpeta compartida.

## URL del sitio, CORS y HMR

- La **URL canónica** con la que el usuario abre el sitio (p. ej. `http://silveira.localhost`) debe ser coherente con **`server.origin`** / **`server.hmr`** en Vite cuando el dev server no sea el mismo host/puerto.
- Si el front se abre como `silveira.localhost` pero Vite escucha en `localhost:5173`, revisar en la doc de Vite las opciones de **HMR** (`host`, `clientPort`, `protocol`) para evitar WebSocket o módulos bloqueados.
- Para decidir “modo Vite dev” en PHP, conviene una **constante o variable de entorno propia** (p. ej. `SILVEIRA_VITE_DEV` o `VITE_DEV_SERVER_URL`) **además o en lugar de** `WP_DEBUG`: `WP_DEBUG` activa avisos PHP en pantalla y no siempre significa “quiero HMR”.

## Pasos de implementación (cuando pidas código)

1. En `wp-content/themes/silveira/`: `package.json`, `vite.config.js` (o `.ts`), `src/main.js`, `src/scss/main.scss`.
2. **Vite:** `base` `/wp-content/themes/silveira/`; `build.outDir` recomendado `assets`; **`build.manifest: true`**; bloque `server` según la sección anterior.
3. **`functions.php`:** si modo dev (constante/env), encolar `@vite/client` + script entrada con URL del servidor Vite; si no, **leer manifest** y encolar CSS/JS generados.
4. **`.gitignore`:** `node_modules/`, `vendor/`, cachés de herramientas; política de **`assets/`** (ignorar compilados *o* commitearlos — ver *Trade-offs*).
5. **Scripts npm:** `dev`, `build`, `lint`, `lint:css`, `format` / `format:check`, **`validate`** (agregador para `prebuild`). Definir **`"prebuild": "npm run validate"`** (o equivalente). **Convención:** el equipo usa siempre **`npm run build`**, no `vite build` suelto, para no saltarse validación.
6. **Husky + lint-staged:** instalar Husky (`prepare` en `package.json` para que nuevos clones instalen hooks). **Importante:** los hooks de Git suelen vivir en la **raíz del repositorio**; si Git está en `silveira/` y el `package.json` del theme está en un subdirectorio, o bien se inicializa Husky desde la **raíz** del repo con scripts que `cd` al theme, o se configura `core.hooksPath` apuntando a `.husky` en la raíz — evitar asumir que `husky init` dentro del theme funcionará sin ajustar rutas.
7. **Composer en el theme (opcional):** PHPCS + WPCS, PHPStan; scripts `composer lint` / `composer analyse`. Incluirlos en `validate` o en lint-staged solo si todos los devs tienen PHP/Composer usable localmente o vía contenedor documentado.
8. Documentación mínima (comentario en `package.json`, fragmento en `.env.example` del theme, etc.): `npm install`, `npm run dev` para HMR, `npm run build` antes de entregar o para probar sin dev server.

## Herramientas para buen código y menos errores

Objetivo: feedback automático y estilo homogéneo; no sustituyen revisión humana ni pruebas.

**Stack sugerido:** ESLint (flat) + Prettier + `eslint-config-prettier`; Stylelint (+ SCSS si aplica); PHPCS con WPCS; PHPStan con nivel bajo al inicio. TypeScript o JSDoc estricto como evolución opcional.

### Cómo encajan con npm / Git

- **`validate`:** agrupa lo que debe fallar antes del build (como mínimo lint JS + Stylelint; PHP solo si está disponible de forma fiable en todos los entornos de desarrollo).
- **`prebuild`:** llama a `validate` → **`npm run build`** implica calidad previa. Quien ejecute **`vite build` a mano** se salta ese hook; por eso la convención del equipo importa.
- **Husky + lint-staged en `pre-commit`:** Prettier/ESLint en `*.{js,mjs,cjs}`, Stylelint en `*.{css,scss}`, `phpcs` en `*.php` si `vendor/bin` existe o está documentado el camino.

### Cuándo se ejecutan (resumen)

| Momento | Qué suele correr | Nota |
|---------|------------------|------|
| Al guardar (editor) | ESLint / Prettier / Stylelint | Comodidad individual. |
| `pre-commit` (Husky) | lint-staged | Homogeneiza lo que entra en Git. |
| `prebuild` (npm) | `validate` | Bloquea `assets/` rotos antes de Vite. |
| Manual | `npm run lint`, `composer phpcs`, … | Antes de PR o cuando CI no existe aún. |
| CI (cuando haya remoto) | Mismos comandos | Capa objetiva fuera del portátil. |

### Editor (Cursor / VS Code)

Extensiones alineadas con las reglas del repo; “format on save” opcional. La **referencia** para el equipo sigue siendo lo que ejecutan **Husky**, **`prebuild`** y la **CI**.

## Trade-offs

### Vite, assets y repo

| Decisión | Ventaja | Coste |
|----------|---------|--------|
| Vite en el host + WP en Docker | HMR real, patrón habitual | Dos orígenes: CORS/HMR a configurar con cuidado |
| Manifest en build | Encolado correcto de chunks hasheados | Más lógica PHP para leer manifest |
| Salida fija (`main.css`) sin manifest | PHP más simple | Peor control de caché en despliegues reales |
| Commitear `assets/` compilados | Clonar y ver el theme sin `npm run build` | PRs con diffs ruidosos en binarios/texto generado |
| Ignorar `assets/` en git | Repo limpio | Cada entorno/CI debe poder ejecutar `npm run build` |

### Calidad, Husky y `prebuild`

| Decisión | Ventaja | Coste |
|----------|---------|--------|
| Husky + lint-staged | Menos cambios inconsistentes en Git | Requiere Node en quien commitea; atención a **raíz Git** vs carpeta del theme |
| `prebuild` → `validate` | Ningún `npm run build` “oficial” empaqueta con lint roto | Cada build algo más lento; `vite build` directo lo evita |
| PHPCS/PHPStan en `validate` | Una sola puerta antes del build | Frustra si no hay PHP/Composer en el host (mitigar con Docker o no incluir en `validate`) |
| ESLint + Prettier + Stylelint | Menos errores front y formato uniforme | Curva inicial de configuración |

## Alternativas (más complejas)

- **Vite en raíz del monorepo** con `root` en el theme: útil con varios paquetes; más indirección para un solo theme.
- **`@wordpress/scripts`:** muy orientado a bloques/editor; menos KISS para solo front classic.
- **Node dentro de Docker:** mismo Node para todos; más servicios y mantenimiento en `docker-compose.yml`.

## Aclaraciones pendientes (producto / equipo)

- **Política de `assets/`:** **cerrada (2026-04-16)** — versionar compilados en `wp-content/themes/silveira/assets/` en Git para permitir despliegue sin Node en servidor; convención: `npm run build` antes de commit cuando cambie `src/`.
- **HTTPS local** (Vite + WP): si algún día es obligatorio, afecta `server.https` y cookies; hoy el doc asume HTTP en `silveira.localhost`.
- **PHP en `validate`:** ¿solo Docker, Composer local, o ambos documentados?

---

*Siguiente paso cuando lo indiques: implementar en el theme `vite.config`, `package.json`, encolado en `functions.php`, toolchain (ESLint, Prettier, Stylelint, PHPCS, PHPStan), **Husky + lint-staged** y **`prebuild` → `validate`**, respetando la raíz del repo Git para los hooks.*
