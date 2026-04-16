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

**[0.1]** ⏳ Política de `assets/` en Git  
> **What to do:** Decidir (y documentar) si los compilados bajo `assets/` se **commitean** o se **ignoran** (`.gitignore`) y cada clon/CI debe ejecutar `npm run build`. Criterio: decisión escrita en comentario de `package.json`, fragmento en `.gitignore`, o nota acordada por el equipo.  
> **Date completed:** -  
> **Work done:** -

**[0.2]** ⏳ Modo “Vite dev” en PHP (constante / env)  
> **What to do:** Elegir mecanismo explícito (**no** basar solo en `WP_DEBUG`): p. ej. `SILVEIRA_VITE_DEV`, `VITE_DEV_SERVER_URL` en `.env` del theme o constante en `wp-config` vía Docker. Criterio: documentado qué valor activa HMR en front/admin.  
> **Date completed:** -  
> **Work done:** -

---

## Fase 1 — Scaffold Vite en el theme

**[1.1]** ⏳ `package.json` y dependencias Vite  
> **What to do:** Crear `package.json` en `wp-content/themes/silveira/` con scripts `dev` y `build` que invoquen Vite; instalar `vite` y plugin SCSS si aplica. Criterio: `npm run dev` y `npm run build` ejecutan sin error en el theme.  
> **Date completed:** -  
> **Work done:** -

**[1.2]** ⏳ `vite.config` (base, outDir, manifest, server)  
> **What to do:** Configurar `base: '/wp-content/themes/silveira/'`, `build.outDir: 'assets'`, `build.manifest: true`, bloque `server` alineado con `http://silveira.localhost` y HMR/CORS según solución §URL. Criterio: build genera `manifest` bajo `assets/` (ruta exacta según versión de Vite verificada en disco).  
> **Date completed:** -  
> **Work done:** -

**[1.3]** ⏳ Entradas `src/`  
> **What to do:** Añadir `src/main.js` que importe `src/scss/main.scss`; contenido mínimo comprobable en front (p. ej. clase o variable CSS). Criterio: tras `npm run build`, existen CSS/JS referenciados en el manifest.  
> **Date completed:** -  
> **Work done:** -

---

## Fase 2 — Integración WordPress (`functions.php`)

**[2.1]** ⏳ Helpers lectura manifest / URLs del theme  
> **What to do:** Funciones PHP (o clase mínima) para localizar `manifest.json`, resolver claves de entrada (`main` o nombre acordado) y devolver rutas públicas bajo el theme. Criterio: sin warnings PHP en `WP_DEBUG` al cargar front con build existente.  
> **Date completed:** -  
> **Work done:** -

**[2.2]** ⏳ Encolado modo **producción** (manifest + versión)  
> **What to do:** Si modo dev desactivado: `wp_enqueue_style` / `wp_enqueue_script` desde manifest; versión con `filemtime()` del manifest o del asset. Criterio: vista fuente del front muestra URLs bajo `/wp-content/themes/silveira/assets/...` y carga sin 404.  
> **Date completed:** -  
> **Work done:** -

**[2.3]** ⏳ Encolado modo **desarrollo** (Vite client + entrada)  
> **What to do:** Si modo dev activo: encolar `@vite/client` y script tipo módulo apuntando al origen del servidor Vite (`VITE_DEV_SERVER_URL` o default `http://localhost:5173`). Criterio: con `npm run dev` + WP en Docker, HMR o recarga funcional al editar `src/`.  
> **Date completed:** -  
> **Work done:** -

**[2.4]** ⏳ Prueba manual extremo a extremo  
> **What to do:** Abrir `http://silveira.localhost` con build y con dev server; comprobar admin no rompe (al menos sin errores fatales en consola por scripts del theme en páginas públicas). Criterio: checklist breve anotado en backlog o comentario.  
> **Date completed:** -  
> **Work done:** -

---

## Fase 3 — Lint, formato y `prebuild`

**[3.1]** ⏳ ESLint (flat) + Prettier + `eslint-config-prettier`  
> **What to do:** Configurar ESLint y Prettier para `src/`; script `npm run lint` y `format` / `format:check`. Criterio: `npm run lint` pasa en código base del theme.  
> **Date completed:** -  
> **Work done:** -

**[3.2]** ⏳ Stylelint (+ SCSS si aplica)  
> **What to do:** Configurar Stylelint para `src/**/*.scss` (y CSS si existe); script `npm run lint:css`. Criterio: `npm run lint:css` pasa.  
> **Date completed:** -  
> **Work done:** -

**[3.3]** ⏳ Script `validate` y hook `prebuild`  
> **What to do:** Agregar `validate` que ejecute al menos `lint` + `lint:css`; `"prebuild": "npm run validate"` y `build` que llame a `vite build`. Criterio: `npm run build` falla si se introduce error de lint intencional; convención documentada: no usar `vite build` solo para builds “oficiales”.  
> **Date completed:** -  
> **Work done:** -

---

## Fase 4 — Husky y raíz Git

**[4.1]** ⏳ Estrategia hooks (raíz repo vs theme)  
> **What to do:** Confirmar dónde está la **raíz Git** del proyecto `silveira`; documentar si `.husky` vive en raíz con scripts que `cd` al theme, o `core.hooksPath` / `package.json` en raíz. Criterio: un párrafo en documentación mínima o comentario reproducible por otro dev.  
> **Date completed:** -  
> **Work done:** Raíz Git = directorio del proyecto `silveira` (coincide con raíz del repo); `origin` = `git@github.com:arinspunk/silveira.git`; commit inicial `8f4b49b`. Pendiente: documentar Husky/lint-staged cuando exista `package.json` en el theme (o `prepare` en raíz).

**[4.2]** ⏳ Husky + `prepare` + lint-staged en `pre-commit`  
> **What to do:** Instalar `husky`, `lint-staged`; `prepare` en el `package.json` que corresponda (raíz o theme); hook `pre-commit` que ejecute lint-staged (Prettier/ESLint en JS, Stylelint en SCSS). Criterio: commit de prueba con archivo mal formateado es rechazado o autocorregido según reglas elegidas.  
> **Date completed:** -  
> **Work done:** -

---

## Fase 5 — PHP estático (opcional, según [0.x] / equipo)

**[5.1]** ⏳ `composer.json` en el theme con PHPCS + WPCS + PHPStan  
> **What to do:** Añadir `composer.json`, dependencias de desarrollo, reglas WPCS, nivel PHPStan bajo; scripts `composer lint` / `composer analyse`. Criterio: comandos documentados y ejecutables donde haya PHP/Composer.  
> **Date completed:** -  
> **Work done:** -

**[5.2]** ⏳ Integración PHPCS en lint-staged o exclusión de `validate`  
> **What to do:** Según solución §PHP en `validate`: o bien `phpcs` en lint-staged para `*.php` si `vendor/bin` existe, o bien **no** incluir PHP en `validate` y documentar ejecución vía Docker/Composer manual. Criterio: decisión explícita y sin sorpresas para quien no tenga PHP local.  
> **Date completed:** -  
> **Work done:** -

---

## Fase 6 — Cierre y contexto

**[6.1]** ⏳ `.gitignore` y exclusiones  
> **What to do:** Asegurar `node_modules/`, `vendor/`, cachés de linters; aplicar política de **[0.1]** sobre `assets/`. Criterio: `git status` limpio tras build según política elegida.  
> **Date completed:** -  
> **Work done:** En raíz del repo, `.gitignore` ya incluye `.env`, `node_modules/`, `vendor/` (commit `8f4b49b`). Pendiente: reglas para `assets/` compilados y cachés de linters según **[0.1]**.

**[6.2]** ⏳ Documentación mínima de uso  
> **What to do:** Instrucciones breves: `npm install`, `npm run dev` (HMR), `npm run build`, hooks, URL `http://silveira.localhost` (y variables env del theme si las hay). Criterio: nuevo dev puede seguir la lista sin preguntar por Slack.  
> **Date completed:** -  
> **Work done:** -

---

## Progreso

| Métrica | Valor |
|---------|--------|
| **Total tareas** | 15 |
| **Completadas** | 0 |
| **Progreso** | 0% |

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
| Solución Vite | `20260416-03-solution-vite-theme-classic-silveira.md` |

## Desviaciones respecto al plan

- Ninguna aún; registrar aquí si se elige monorepo, Node en Docker, o no commitear Husky por política del host.
