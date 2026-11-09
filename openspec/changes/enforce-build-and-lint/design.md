# Design

## Context

Ver proposal.md (Why). Estado actual de la cadena de calidad:

```
commit --> husky pre-commit --> lint-staged
             src/**/*.js     -> eslint --fix, prettier        (theme node_modules)
             **/*.{css,scss} -> stylelint --fix, prettier
             **/*.php        -> lint-staged-phpcs.sh -> sin vendor/ o sin php: SKIP (exit 0)
           (nada recompila assets/)

npm run build --> prebuild: eslint + stylelint --> vite build --> assets/   (manual)

push --> (sin CI)
```

- No hay `php` ni `composer` en el host del desarrollador; Docker sí está disponible (el entorno WP corre en Docker).
- `composer.json` ya incluye `szepeviktor/phpstan-wordpress` (stubs de WP) pero no stubs de ACF, y el theme usa `get_field`/`update_field`.
- Vite genera nombres con hash de contenido (`main-[hash].js`) y `manifest.json`; con el mismo lockfile y versión de Node el resultado debería ser byte a byte idéntico.

## Goals / Non-Goals

**Goals:**
- Que sea imposible fusionar en `main` un estado donde `assets/` no corresponda a `src/` o con infracciones de lint/análisis.
- Que en local el flujo normal (commit) mantenga `assets/` al día sin pasos manuales.
- Que PHPCS funcione en local sin instalar PHP.
- Dejar el código actual limpio frente a los estándares, sin cambios funcionales.

**Non-Goals:**
- Tests automatizados (unitarios/E2E) — no existen y no se introducen aquí.
- Despliegue automático.
- Endurecer reglas más allá de los estándares ya elegidos (WPCS `WordPress`, `eslint:recommended`, `stylelint-config-standard-scss`).
- Arreglos funcionales detectados en la revisión (XSS, geocoding, etc.); van en otros changes.

## Decisions

### D1. CI en GitHub Actions con dos jobs paralelos
`.github/workflows/ci.yml`, en `push` a `main` y en `pull_request`:

```
job "frontend" (ubuntu, node desde .nvmrc, cwd theme)
  npm ci -> npm run validate -> npm run format:check
  -> npm run build -> git diff --exit-code -- assets/  (+ git status --porcelain assets/ vacío)

job "php" (ubuntu, shivammathur/setup-php, PHP 8.2 = imagen WP)
  composer install -> composer run lint -> composer run analyse
```

- El check de assets falla con un mensaje explícito ("ejecuta npm run build y commitea assets/").
- `git status --porcelain` cubre ficheros nuevos/borrados (hashes que cambian), que `git diff` no ve.
- *Alternativa*: que CI compile y haga commit de `assets/` automáticamente. Descartada: commits de bot en PRs, permisos de escritura y bucles de workflow; el hook local ya cubre la comodidad.

### D2. El pre-commit recompila y añade `assets/`
Nueva entrada en `lint-staged` para `wp-content/themes/silveira/{src/**,vite.config.js,package-lock.json}` que ejecuta un script `scripts/build-assets.sh`: `npm run build` en el theme y `git add -A wp-content/themes/silveira/assets`. Se ejecuta una sola vez por commit (función en lint-staged que ignora la lista de ficheros).
- *Alternativa*: solo avisar si `src/` está staged y `assets/` no. Más rápido, pero `assets/` staged puede seguir desfasado y obliga a un paso manual que es justo lo que se olvidó. Como el build tarda segundos, se prefiere recompilar. CI sigue siendo la red de seguridad (`--no-verify`).
- Riesgo de lint-staged con ficheros añadidos durante la ejecución: lint-staged ≥ 15 incorpora al commit lo que las tareas añaden con `git add`; se valida en la tarea correspondiente.

### D3. PHPCS en el hook: PHP local → Docker → aviso
`scripts/lint-staged-phpcs.sh` pasa a:
1. Si `php` funciona y existe `vendor/bin/phpcs` → ejecutarlo.
2. Si no, si `docker` está disponible → `docker run --rm -v <theme>:/app -w /app composer:2 sh -c '[ -d vendor ] || composer install -q; vendor/bin/phpcs --standard=phpcs.xml.dist <ficheros relativos>'`.
3. Si no hay ninguno → mensaje en stderr bien visible y `exit 0` (CI bloqueará).
- Las rutas staged llegan absolutas/relativas a la raíz; se convierten a relativas al theme para el contenedor.
- *Alternativa*: usar el servicio `wordpress` de docker-compose (tiene PHP 8.2) — requiere el stack levantado y no trae Composer; `composer:2` es autocontenido.

### D4. Cobertura y nivel de análisis PHP
- `phpcs.xml.dist`: sustituir la lista de `<file>` por `<file>.</file>` con los `exclude-pattern` existentes; añadir `<config name="text_domain" value="silveira"/>` y prefijos (`WordPress.NamingConventions.PrefixAllGlobals` con `silveira`).
- `phpstan.neon.dist`: `paths: [.]` con exclusiones; subir a **nivel 5**. Añadir `php-stubs/acf-pro-stubs` como `require-dev` y en `scanFiles`/`bootstrapFiles`.
- `template-kitchen-sink.php` se incluye (es código del theme), aunque se puede excluir de reglas de estilo inline si genera ruido injustificado.
- *Alternativas*: nivel 6+ exige tipos en todas las firmas y arrays — coste alto para un theme de plantillas; nivel 0–4 deja pasar llamadas con tipos incorrectos. Baseline descartado por decisión del usuario (corregir todo).

### D5. Build reproducible
`.nvmrc` en la raíz con la versión mayor de Node usada en local (22) y `engines.node` en el `package.json` del theme. CI lee `.nvmrc`. `npm ci` garantiza lockfile. Se verifica que un build en limpio en local y en CI produce el mismo `assets/` antes de activar el check como bloqueante.

### D6. ESLint: global de Leaflet
En `eslint.config.js`, para `src/**/*.js`: `globals: { ...globals.browser, L: 'readonly' }`. Leaflet sigue cargándose desde CDN como script clásico; no se importa como módulo (fuera de alcance).

### D7. Orden respecto a `harden-map-output`
El change se aplica en dos tramos para no retrasar la corrección del XSS:

1. Grupo 1 (desbloquear el build, incluido D6).
2. Se aplica `harden-map-output` completo.
3. Grupos 2–5 (análisis PHP en todo el theme, hook, CI, documentación).

Al acabar el tramo 3, los changes posteriores ya no necesitan tareas propias de build ni de paths de PHPCS/PHPStan: el hook regenera `assets/` y la CI lo comprueba.

## Risks / Trade-offs

- [El build no es byte-idéntico entre macOS y Linux (p. ej. diferencias en Sass o rutas en sourcemaps)] → no se generan sourcemaps en build; verificar en la PR de introducción comparando artefacto de CI con local; si difiere, el job sube `assets/` como artefacto para diagnosticar.
- [Corregir todas las infracciones PHPCS toca muchas líneas y dificulta `git blame`] → hacer el reformateo automático (`phpcbf`, Prettier) en commits separados y añadir sus hashes a `.git-blame-ignore-revs`.
- [Corrección manual de PHPCS/PHPStan introduce cambios funcionales sin querer (p. ej. escapes que alteran salida)] → revisión visual de portada, ficha de proyecto, archivos y kitchen sink antes/después; los cambios de escape que alteren salida se documentan en el commit.
- [El hook se vuelve lento por el build y Docker] → build solo si hay cambios en `src/`; Docker solo si hay PHP staged y no hay PHP local; primer `composer install` en Docker cachea `vendor/` en el theme.
- [`composer:2` usa una versión de PHP más nueva que producción (8.2)] → PHPCS/WPCS no depende del runtime para las reglas (testVersion `8.0-` ya configurado); PHPStan corre en CI con 8.2.

## Migration Plan

1. Mergear con CI ya presente; la primera ejecución en la PR debe estar verde.
2. Tras el merge, activar en GitHub la protección de rama de `main` exigiendo los checks `frontend` y `php` (acción manual del propietario del repo).
3. Cada persona del equipo ejecuta `npm install` en la raíz (refresca hooks) y opcionalmente `docker pull composer:2`.
Rollback: revertir el commit; CI desaparece y los hooks vuelven a su comportamiento anterior.

## Open Questions

- ¿Quién activa la protección de rama en GitHub y si `main` admite pushes directos? No afecta a la implementación, solo a cuán obligatorio es el check.
