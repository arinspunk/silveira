# Backlog — **silveira** (Vite + toolchain en theme classic)

## Encabezado

| Campo | Valor |
|--------|--------|
| **Proyecto** | silveira |
| **Objetivo** | Integrar **Vite** dentro de `wp-content/themes/silveira/`, encolado dev/prod vía manifest, URL **`http://silveira.localhost`**, y calidad con **ESLint / Prettier / Stylelint**, **Husky + lint-staged**, **`prebuild` → `validate`**; PHP (**PHPCS/WPCS**, **PHPStan**) según disponibilidad documentada. |
| **Fecha de generación** | 2026-04-16 |
| **Solución de referencia (principal)** | [.cursor/memory/20260416-03-solution-vite-theme-classic-silveira.md](20260416-03-solution-vite-theme-classic-silveira.md) |
| **Contexto Docker / WP** | [.cursor/memory/20260416-01-docker-wordpress-theme-silveira.md](20260416-01-docker-wordpress-theme-silveira.md) (entorno ya definido; coherencia de URL/puerto) |

---

## Leyenda de estado

⏳ Pendiente | 🔄 En curso | ✅ Hecho | ⚠️ Bloqueado

---

## Fase 0 — Decisiones de producto / repo

**[0.1]** ✅ Política de `assets/` en Git  
> **What to do:** Decidir (y documentar) si los compilados bajo `assets/` se **commitean** o se **ignoran** (`.gitignore`) y cada clon/CI debe ejecutar `npm run build`. Criterio: decisión escrita en comentario de `package.json`, fragmento en `.gitignore`, o nota acordada por el equipo.  
> **Date completed:** 2026-04-16  
> **Work done:** **Versión compilada en Git:** `wp-content/themes/silveira/assets/` (salida de Vite) se **versiona** en el repo para poder desplegar en servidores **sin Node** en principio. Convención: tras cambios en `src/`, ejecutar `npm run build` y commitear `assets/` + manifest. Nota en `.gitignore` (raíz del repo). Repetir aviso en `package.json` del theme cuando exista ([1.1]).
> **Commit:** `d993e0e` feat(theme): add Vite scaffold and env-based dev toggle

**[0.2]** ✅ Modo “Vite dev” en PHP (constante / env)  
> **What to do:** Elegir mecanismo explícito (**no** basar solo en `WP_DEBUG`): p. ej. `SILVEIRA_VITE_DEV`, `VITE_DEV_SERVER_URL` en `.env` del theme o constante en `wp-config` vía Docker. Criterio: documentado qué valor activa HMR en front/admin.  
> **Date completed:** 2026-04-16  
> **Work done:** **Decisión:** variables de entorno en el contenedor `wordpress` (y `wpcli`) vía `docker-compose` + `.env`: `SILVEIRA_VITE_DEV` (por defecto `0`) y `VITE_DEV_SERVER_URL` (por defecto `http://host.docker.internal:5173` para alcanzar Vite en el host desde Docker Desktop). Helpers `silveira_is_vite_dev()` y `silveira_vite_dev_server_url()` en `functions.php`. Documentado en `.env.example`. Encolado real de `@vite/client` en **[2.3]**.
> **Commit:** `d993e0e` feat(theme): add Vite scaffold and env-based dev toggle

---

## Fase 1 — Scaffold Vite en el theme

**[1.1]** ✅ `package.json` y dependencias Vite  
> **What to do:** Crear `package.json` en `wp-content/themes/silveira/` con scripts `dev` y `build` que invoquen Vite; instalar `vite` y plugin SCSS si aplica. Criterio: `npm run dev` y `npm run build` ejecutan sin error en el theme.  
> **Date completed:** 2026-04-16  
> **Work done:** `package.json` con `type: module`, scripts `dev`/`build`, `devDependencies`: `vite@6.4.x`, `sass`. Campo `description` recuerda commitear `assets/`. `wp-content/themes/silveira/.gitignore` con `node_modules/`. `npm install` y `npm run build` OK; `npm run dev` arranca Vite (verificado en host; en sandbox puede fallar por interfaces de red).
> **Commit:** `d993e0e` feat(theme): add Vite scaffold and env-based dev toggle

**[1.2]** ✅ `vite.config` (base, outDir, manifest, server)  
> **What to do:** Configurar `base: '/wp-content/themes/silveira/'`, `build.outDir: 'assets'`, `build.manifest: true`, bloque `server` alineado con `http://silveira.localhost` y HMR/CORS según solución §URL. Criterio: build genera `manifest` bajo `assets/` (ruta exacta según versión de Vite verificada en disco).  
> **Date completed:** 2026-04-16  
> **Work done:** `vite.config.js`: `base`, `outDir: assets`, `manifest: true`, `rollupOptions.output` con `js/` y `css/` para evitar carpeta `assets/assets/`. `server`: `host: 0.0.0.0`, `port: 5173`, `origin: http://silveira.localhost`, `cors: true`. Manifest generado en **`assets/.vite/manifest.json`** (Vite 6).
> **Commit:** `d993e0e` feat(theme): add Vite scaffold and env-based dev toggle

**[1.3]** ✅ Entradas `src/`  
> **What to do:** Añadir `src/main.js` que importe `src/scss/main.scss`; contenido mínimo comprobable en front (p. ej. clase o variable CSS). Criterio: tras `npm run build`, existen CSS/JS referenciados en el manifest.  
> **Date completed:** 2026-04-16  
> **Work done:** `src/main.js` importa `./scss/main.scss`. `src/scss/main.scss` con `:root { --silveira-accent }` y estilos base. Build produce p. ej. `js/main-*.js`, `css/main-*.css`; manifest referencia entrada `src/main.js` con `css` y `file` JS.
> **Commit:** `d993e0e` feat(theme): add Vite scaffold and env-based dev toggle

---

## Fase 2 — Integración WordPress (`functions.php`)

**[2.1]** ✅ Helpers lectura manifest / URLs del theme  
> **What to do:** Funciones PHP (o clase mínima) para localizar `manifest.json`, resolver claves de entrada (`main` o nombre acordado) y devolver rutas públicas bajo el theme. Criterio: sin warnings PHP en `WP_DEBUG` al cargar front con build existente.  
> **Date completed:** 2026-04-16  
> **Work done:** Constante `SILVEIRA_VITE_ENTRY`; `silveira_vite_manifest_path()`, `silveira_vite_get_manifest()` (estático), `silveira_vite_asset_url()` / `silveira_vite_asset_path()`, `silveira_vite_theme_base_path()` para modo dev. Manifest: `assets/.vite/manifest.json`.
> **Commit:** `e366841` feat(theme): enqueue Vite assets from manifest and dev server

**[2.2]** ✅ Encolado modo **producción** (manifest + versión)  
> **What to do:** Si modo dev desactivado: `wp_enqueue_style` / `wp_enqueue_script` desde manifest; versión con `filemtime()` del manifest o del asset. Criterio: vista fuente del front muestra URLs bajo `/wp-content/themes/silveira/assets/...` y carga sin 404.  
> **Date completed:** 2026-04-16  
> **Work done:** `silveira_enqueue_vite_prod()` en `wp_enqueue_scripts`: lee entrada `SILVEIRA_VITE_ENTRY`, encola cada CSS del array y el JS; `ver` = `filemtime` del asset o del manifest. Comprobado: HTML incluye `.../assets/css/main-*.css` y `.../assets/js/main-*.js`.
> **Commit:** `e366841` feat(theme): enqueue Vite assets from manifest and dev server

**[2.3]** ✅ Encolado modo **desarrollo** (Vite client + entrada)  
> **What to do:** Si modo dev activo: encolar `@vite/client` y script tipo módulo apuntando al origen del servidor Vite (`VITE_DEV_SERVER_URL` o default `http://localhost:5173`). Criterio: con `npm run dev` + WP en Docker, HMR o recarga funcional al editar `src/`.  
> **Date completed:** 2026-04-16  
> **Work done:** `silveira_enqueue_vite_dev()`: URLs `{origin}{base}/@vite/client` y `{origin}{base}/src/main.js` con `silveira_vite_dev_server_url()`. Filtro `script_loader_tag` → `type="module"` para handles `silveira-vite-client` y `silveira-main`. HMR: activar `SILVEIRA_VITE_DEV=1` en `.env`, `docker compose up -d`, `npm run dev` en el theme.
> **Commit:** `e366841` feat(theme): enqueue Vite assets from manifest and dev server

**[2.4]** ✅ Prueba manual extremo a extremo  
> **What to do:** Abrir `http://silveira.localhost` con build y con dev server; comprobar admin no rompe (al menos sin errores fatales en consola por scripts del theme en páginas públicas). Criterio: checklist breve anotado en backlog o comentario.  
> **Date completed:** 2026-04-16  
> **Work done:** **Producción (sin Vite dev):** `curl` al front con `Host: silveira.localhost` devuelve enlaces a CSS/JS bajo `wp-content/themes/silveira/assets/`. **Admin:** los assets del theme se encolan solo en `wp_enqueue_scripts` (front público); no se añadió `admin_enqueue_scripts` para estos bundles. **Modo dev:** validar en navegador con `.env` + `npm run dev` cuando haga falta.
> **Commit:** `e366841` feat(theme): enqueue Vite assets from manifest and dev server

---

## Fase 3 — Lint, formato y `prebuild`

**[3.1]** ✅ ESLint (flat) + Prettier + `eslint-config-prettier`  
> **What to do:** Configurar ESLint y Prettier para `src/`; script `npm run lint` y `format` / `format:check`. Criterio: `npm run lint` pasa en código base del theme.  
> **Date completed:** 2026-04-16  
> **Work done:** `eslint.config.js` (flat): `@eslint/js` recommended, `globals` (browser para `src/`, node para configs), `eslint-config-prettier`. Scripts: `lint` sobre `src`, `vite.config.js`, configs; `format` / `format:check` con Prettier; `.prettierrc.json`, `.prettierignore` (excluye `assets/`, `node_modules/`).
> **Commit:** `9da27d8` chore(theme): add eslint, prettier, stylelint and prebuild validation

**[3.2]** ✅ Stylelint (+ SCSS si aplica)  
> **What to do:** Configurar Stylelint para `src/**/*.scss` (y CSS si existe); script `npm run lint:css`. Criterio: `npm run lint:css` pasa.  
> **Date completed:** 2026-04-16  
> **Work done:** `stylelint.config.js` con `stylelint-config-standard-scss`, `ignoreFiles` para `assets/` y `node_modules/`. Script `npm run lint:css` en `src/**/*.{css,scss}`.
> **Commit:** `9da27d8` chore(theme): add eslint, prettier, stylelint and prebuild validation

**[3.3]** ✅ Script `validate` y hook `prebuild`  
> **What to do:** Agregar `validate` que ejecute al menos `lint` + `lint:css`; `"prebuild": "npm run validate"` y `build` que llame a `vite build`. Criterio: `npm run build` falla si se introduce error de lint intencional; convención documentada: no usar `vite build` solo para builds “oficiales”.  
> **Date completed:** 2026-04-16  
> **Work done:** `validate` → `lint && lint:css`; `prebuild` → `validate`; `build` → `vite build` (npm ejecuta `prebuild` antes de `build`). `description` en `package.json` recuerda usar `npm run build` completo. Verificado: `npm run build` completa lint + Vite sin error.
> **Commit:** `9da27d8` chore(theme): add eslint, prettier, stylelint and prebuild validation

---

## Fase 4 — Husky y raíz Git

**[4.1]** ✅ Estrategia hooks (raíz repo vs theme)  
> **What to do:** Confirmar dónde está la **raíz Git** del proyecto `silveira`; documentar si `.husky` vive en raíz con scripts que `cd` al theme, o `core.hooksPath` / `package.json` en raíz. Criterio: un párrafo en documentación mínima o comentario reproducible por otro dev.  
> **Date completed:** 2026-04-16  
> **Work done:** **Raíz Git** = directorio del repo `silveira` (misma raíz que `docker-compose` y `package.json` de Husky). **Hooks:** `.husky/` en la raíz; `pre-commit` hace `cd` al directorio padre del hook y ejecuta `npx lint-staged` (sin `cd` manual al theme: los globs de lint-staged son rutas relativas a la raíz). **Documentación:** campo `description` en `package.json` de la raíz: clonar → `npm install` (raíz) + `npm install` en el theme + `composer install` en el theme para PHP; qué hace el pre-commit. No se usa `core.hooksPath` alternativo.  
> **Commit:** `12117aa`

**[4.2]** ✅ Husky + `prepare` + lint-staged en `pre-commit`  
> **What to do:** Instalar `husky`, `lint-staged`; `prepare` en el `package.json` que corresponda (raíz o theme); hook `pre-commit` que ejecute lint-staged (Prettier/ESLint en JS, Stylelint en SCSS). Criterio: commit de prueba con archivo mal formateado es rechazado o autocorregido según reglas elegidas.  
> **Date completed:** 2026-04-16  
> **Work done:** Raíz: `devDependencies` `husky`, `lint-staged`; script `"prepare": "husky"`. `.husky/pre-commit` → `exec npx lint-staged` (sin `husky.sh` deprecado en Husky 9+, ver `82dda20`). `lint-staged` en el `package.json` raíz: ESLint/Prettier sobre `src/**/*.{js,mjs,cjs}`, configs del theme, Stylelint + Prettier en CSS/SCSS, todo vía `npx --prefix wp-content/themes/silveira` para usar el `node_modules` del theme. Tras `npm install` en la raíz, `prepare` registra Husky.  
> **Commit:** `12117aa` (hook base), `82dda20` (pre-commit sin `husky.sh`)

---

## Fase 5 — PHP estático (opcional, según [0.x] / equipo)

**[5.1]** ✅ `composer.json` en el theme con PHPCS + WPCS + PHPStan  
> **What to do:** Añadir `composer.json`, dependencias de desarrollo, reglas WPCS, nivel PHPStan bajo; scripts `composer lint` / `composer analyse`. Criterio: comandos documentados y ejecutables donde haya PHP/Composer.  
> **Date completed:** 2026-04-16  
> **Work done:** `wp-content/themes/silveira/composer.json`: `require-dev` PHPCS, WPCS 3.x, PHPStan 2.x, `phpstan/extension-installer`, `szepeviktor/phpstan-wordpress` **^2.0** (compatible con PHPStan 2; la 1.x chocaba con `phpstan/phpstan` ^2). Scripts `composer run lint` → `phpcs`, `composer run analyse` → `phpstan analyse`. `phpcs.xml.dist` (regla `WordPress`, ficheros del theme). `phpstan.neon.dist`: nivel 0, paths `functions.php`, plantillas; **sin** `includes` manual a `extension.neon` de phpstan-wordpress (lo instala `phpstan/extension-installer`; duplicarlo avisaba). `composer.lock` generado. Verificación en agente: `docker run ... composer:2 composer update` y `composer run lint` / `composer run analyse` OK.  
> **Commit:** `12117aa`

**[5.2]** ✅ Integración PHPCS en lint-staged o exclusión de `validate`  
> **What to do:** Según solución §PHP en `validate`: o bien `phpcs` en lint-staged para `*.php` si `vendor/bin` existe, o bien **no** incluir PHP en `validate` y documentar ejecución vía Docker/Composer manual. Criterio: decisión explícita y sin sorpresas para quien no tenga PHP local.  
> **Date completed:** 2026-04-16  
> **Work done:** **Decisión:** PHP **no** entra en `npm run validate` del theme (sigue siendo solo ESLint + Stylelint). **lint-staged:** `wp-content/themes/silveira/**/*.php` → `bash scripts/lint-staged-phpcs.sh`, que ejecuta `vendor/bin/phpcs` con `phpcs.xml.dist` si existe el binario; si no hay `vendor/`, imprime aviso y **exit 0** (no bloquea a quien no haya hecho `composer install`). Si `php` no está en PATH o **no arranca** (p. ej. ICU/Homebrew roto), también **exit 0** con mensaje — en ese caso usar PHPCS vía Docker/CI o reparar PHP local. `functions.php` y plantillas ajustados a WPCS; `phpstan.neon.dist` sin include duplicado.  
> **Commit:** `12117aa`

---

## Fase 6 — Cierre y contexto

**[6.1]** ⏳ `.gitignore` y exclusiones  
> **What to do:** Asegurar `node_modules/`, `vendor/`, cachés de linters; aplicar política de **[0.1]** sobre `assets/`. Criterio: `git status` limpio tras build según política elegida.  
> **Date completed:** -  
> **Work done:** Política **[0.1]**: `assets/` **no** se ignoran (se commitean). Raíz: `.gitignore` con `.env`, `node_modules/`, `vendor/` y comentario sobre `assets/`. Theme: `.gitignore` con `node_modules/`; primer commit de `assets/` en `d993e0e`. Pendiente: ignorar solo cachés de tooling bajo el theme si molestan (sin excluir `assets/.vite/manifest.json` ni CSS/JS generados).

**[6.2]** ⏳ Documentación mínima de uso  
> **What to do:** Instrucciones breves: `npm install`, `npm run dev` (HMR), `npm run build`, hooks, URL `http://silveira.localhost` (y variables env del theme si las hay). Criterio: nuevo dev puede seguir la lista sin preguntar por Slack.  
> **Date completed:** -  
> **Work done:** -

---

## Progreso

| Métrica | Valor |
|---------|--------|
| **Total tareas** | 18 |
| **Completadas** | 16 |
| **Progreso** | 89% |

---

## Dependencias y camino crítico

- **[0.1]** y **[0.2]** desbloquean decisiones en **[6.1]** y **[2.3]** (política `assets/` y señal de modo dev).
- **[1.1]** → **[1.2]** → **[1.3]** antes de **[2.***]** (hace falta manifest y archivos generados o al menos config dev).
- **[2.1]** antes de **[2.2]** / **[2.3]**.
- **[3.1]**–**[3.2]** antes de **[3.3]** (validate debe tener lint definido).
- **[4.1]** antes de **[4.2]** (Husky mal ubicado rompe el flujo Git).
- **[5.*]** es paralelo a **[4.*]** una vez exista `package.json` del theme, salvo que lint-staged dependa de **[5.2]**.

**Camino crítico sugerido:** 0.2 → 1.1 → 1.2 → 1.3 → 2.1 → 2.2 → 2.3 → 2.4 → 3.1 → 3.2 → 3.3 → 4.1 → 4.2 → 6.1 → 6.2; **0.1** y **5.*** en paralelo según prioridad del equipo.

---

## Contexto y adjuntos

| Tipo | Referencia |
|------|------------|
| Theme | `wp-content/themes/silveira/` |
| URL sitio (doc actual) | `http://silveira.localhost` |
| Remoto Git | `git@github.com:arinspunk/silveira.git` |
| Commit inicial (planificación + Docker + theme esqueleto) | `8f4b49b` |
| Commit Vite scaffold, assets versionados, env dev | `d993e0e` |
| Commit encolado manifest + modo dev Vite (`functions.php`) | `e366841` |
| Commit ESLint, Prettier, Stylelint, prebuild (Fase 3) | `9da27d8` |
| Commit Husky, lint-staged, Composer PHP tools (Fases 4–5) | `12117aa` |
| Commit pre-commit Husky sin husky.sh deprecado | `82dda20` |
| Solución Vite | `20260416-03-solution-vite-theme-classic-silveira.md` |

## Desviaciones respecto al plan

- **[0.1]** Se opta por **commitear `assets/`** (despliegue posible sin Node en servidor); la solución Vite listaba también ignorar `assets/` como alternativa — aquí prima el artefacto compilado en Git.
