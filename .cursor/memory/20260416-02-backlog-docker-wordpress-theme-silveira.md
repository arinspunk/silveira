# Backlog — **silveira** (Docker + theme WordPress)

## Encabezado

| Campo | Valor |
|--------|--------|
| **Proyecto** | silveira |
| **Objetivo** | Repo mínimo (Docker + theme), WordPress en volumen, PHP 8, puerto 8070, permalinks y URL canónica con host local (`silveira` / `silveira.localhost`). |
| **Fecha de generación** | 2026-04-16 |
| **Solución de referencia** | [.cursor/memory/20260416-01-docker-wordpress-theme-silveira.md](20260416-01-docker-wordpress-theme-silveira.md) |

---

## Leyenda de estado

⏳ Pendiente | 🔄 En curso | ✅ Hecho | ⚠️ Bloqueado

---

## Fase 1 — Definición y estructura del repo

**[1.1]** ✅ Fijar URL canónica de desarrollo  
> **What to do:** Elegir y documentar una sola URL base (recomendado: `http://silveira.localhost:8070` o `http://silveira:8070` con `/etc/hosts`). Anotar decisión para alinear WP y documentación.  
> **Date completed:** 2026-04-16  
> **Work done:** URL fijada a `http://silveira.localhost:8070`. Documentada en cabecera de `docker-compose.yml`, `WORDPRESS_CONFIG_EXTRA` y comentarios de `.env.example` (sin `/etc/hosts` en la mayoría de SO gracias a `*.localhost`).

**[1.2]** ✅ Estructura de carpetas (solo Docker + theme)  
> **What to do:** Crear árbol acordado (p. ej. `docker-compose.yml` en raíz, `wp-content/themes/silveira/` con esqueleto mínimo del theme). Criterio: nada del core WP en git.  
> **Date completed:** 2026-04-16  
> **Work done:** Raíz con `docker-compose.yml`, `.env.example`, `.gitignore`; carpeta `wp-content/themes/silveira/`. Core WP solo en volumen `wp_data`.

**[1.3]** ✅ Theme mínimo reconocible por WordPress  
> **What to do:** Añadir `style.css` con headers obligatorios (`Theme Name`, etc.) y un `index.php` mínimo para que el theme sea activable sin error fatal.  
> **Date completed:** 2026-04-16  
> **Work done:** `style.css` con headers; `index.php` con bucle; `functions.php` (`title-tag`); `header.php` / `footer.php` para evitar errores con `get_header`/`get_footer`.

---

## Fase 2 — Docker y persistencia

**[2.1]** ✅ `docker-compose.yml`: base de datos  
> **What to do:** Servicio MariaDB o MySQL 8, variables de entorno, volumen nombrado para datos de BD, red interna entre servicios.  
> **Date completed:** 2026-04-16  
> **Work done:** Servicio `db`: imagen `mariadb:10.11`, variables `MYSQL_*` desde `.env` con valores por defecto, volumen `db_data` → `/var/lib/mysql`, `healthcheck` oficial.

**[2.2]** ✅ `docker-compose.yml`: WordPress (PHP 8 + Apache)  
> **What to do:** Imagen oficial tipo `wordpress:php8.2-apache` (o 8.1/8.2 según preferencia), `WORDPRESS_DB_*`, mapeo **8070:80**, dependencia explícita o implícita de salud de DB si se desea.  
> **Date completed:** 2026-04-16  
> **Work done:** Servicio `wordpress`: `wordpress:php8.2-apache`, credenciales enlazadas a las de MariaDB, `8070:80`, `depends_on` con `condition: service_healthy` en `db`.

**[2.3]** ✅ Volúmenes: core WP fuera del repo, theme desde bind mount  
> **What to do:** Volumen nombrado en `/var/www/html` + bind mount `./wp-content/themes/silveira` → `/var/www/html/wp-content/themes/silveira`. Verificar que no se pisan rutas críticas en primer `up`.  
> **Date completed:** 2026-04-16  
> **Work done:** `wp_data:/var/www/html` + bind del theme en `wordpress`. Primer `docker compose up -d` correcto; mismo bind añadido luego en `wpcli` (ver desviaciones) para coherencia con el theme en disco.

**[2.4]** ✅ URL canónica en `wp-config` (entorno)  
> **What to do:** Configurar `WORDPRESS_CONFIG_EXTRA` (o equivalente) con `WP_HOME` y `WP_SITEURL` igual a la URL de la fase 1.1. Criterio: login y enlaces del sitio no redirigen a otro host.  
> **Date completed:** 2026-04-16  
> **Work done:** `WORDPRESS_CONFIG_EXTRA` en servicio `wordpress` con `WP_HOME` y `WP_SITEURL` = `http://silveira.localhost:8070`. Comprobado con `curl` (redirección canónica y 200).

---

## Fase 3 — Verificación local

**[3.1]** ✅ Arranque y instalación WP  
> **What to do:** `docker compose up -d`, completar asistente de instalación usando **exactamente** la URL canónica. Criterio: admin accesible y base de datos con tablas creadas.  
> **Date completed:** 2026-04-16  
> **Work done:** `docker compose up -d` ejecutado. Instalación con `docker compose --profile tools run --rm wpcli wp core install --url=http://silveira.localhost:8070 ...` (usuario admin / contraseña admin en entorno local; cambiar en producción).

**[3.2]** ✅ Permalinks (pretty URLs)  
> **What to do:** En Ajustes → Enlaces permanentes, activar estructura distinta de “plain”; comprobar que una página/post resuelve sin 404. Criterio: `.htaccess`/rewrite funcionando en el contenedor.  
> **Date completed:** 2026-04-16  
> **Work done:** `wp option update permalink_structure '/%postname%/'` y `wp rewrite flush`; post de prueba `prueba-permalink`; `curl -L` a `/prueba-permalink/` devuelve 200.

**[3.3]** ✅ Ciclo de edición del theme  
> **What to do:** Cambio visible en un template del theme en el host, recarga en el navegador sin rebuild de imagen. Criterio: bind mount del theme operativo.  
> **Date completed:** 2026-04-16  
> **Work done:** Comentario HTML en `index.php` del repo; `curl` al front incluye la cadena `silveira: theme desde bind mount` (volumen del theme = carpeta del proyecto).

---

## Fase 4 — Documentación y equipo

**[4.1]** ✅ Documentar hosts y URL (si aplica)  
> **What to do:** Si no se usa solo `*.localhost`, documentar línea `/etc/hosts` (`127.0.0.1 silveira`) y puerto 8070. Referencia: URL canónica de 1.1.  
> **Date completed:** 2026-04-16  
> **Work done:** Comentarios en `docker-compose.yml` (URL + puerto) y `.env.example` (cuándo hace falta `/etc/hosts` si se usa hostname corto `silveira`).

**[4.2]** ✅ (Opcional) `.env.example` / secretos  
> **What to do:** Plantilla de variables para DB y WP sin commitear contraseñas reales. Criterio: nuevo dev puede copiar y arrancar.  
> **Date completed:** 2026-04-16  
> **Work done:** `.env.example` con `MYSQL_*`, `WORDPRESS_TABLE_PREFIX` y notas de URL; `.gitignore` ignora `.env`.

---

## Progreso

| Métrica | Valor |
|---------|--------|
| **Total tareas** | 10 |
| **Completadas** | 10 |
| **Progreso** | 100% |

---

## Dependencias y camino crítico

- **1.1** bloquea **2.4** y **3.1** (sin URL fija, `WP_HOME`/`WP_SITEURL` serán incorrectos).
- **2.1** y **2.2** bloquean **3.1** (sin compose funcional no hay instalación).
- **2.3** bloquea **3.3** (bind mount del theme debe existir antes de probar edición).
- **3.1** bloquea **3.2** (hay que tener WP instalado para permalinks).

**Camino crítico sugerido:** 1.1 → 1.2 → 1.3 → 2.1 → 2.2 → 2.3 → 2.4 → 3.1 → 3.2 → 3.3 → 4.1 → (4.2 opcional).

---

## Contexto y adjuntos

| Tipo | Referencia |
|------|------------|
| Documento fuente | `20260416-01-docker-wordpress-theme-silveira.md` |
| URL canónica | `http://silveira.localhost:8070` |
| Puerto host | `8070` |
| WP-CLI (perfil) | `docker compose --profile tools run --rm wpcli wp ...` |

## Desviaciones respecto al plan original

- Servicio **`wpcli`** (perfil `tools`, KISS) añadido para instalar WP y permalinks sin navegador; requiere el **mismo bind mount del theme** que `wordpress`, si no WP-CLI no ve `style.css` (volumen sin overlay del host).
- Credenciales de prueba tras `wp core install`: usuario **`admin`**, contraseña **`admin`** — solo para desarrollo local; rotar o reinstalar antes de exponer.
