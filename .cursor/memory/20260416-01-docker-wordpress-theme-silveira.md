# Docker para desarrollo de theme WordPress — proyecto **silveira**

## Resumen del problema

Necesitas un entorno reproducible con Docker para desarrollar un theme de WordPress, con PHP 8, puerto **8070**, URLs “bonitas” (permalinks) usando un host tipo **`silveira`** (interpretación de `silveira:localhost` / acceso local con nombre de host), y un repositorio que **solo** contenga configuración Docker + código del theme; el núcleo de WordPress y el resto de `wp-content` viven en **volúmenes**, no en el repo.

## Enfoque propuesto (KISS)

1. **`docker-compose.yml`** con dos servicios:
   - **Base de datos**: MariaDB (o MySQL 8) en contenedor, datos en volumen nombrado.
   - **WordPress**: imagen oficial `wordpress` con variante **PHP 8.x + Apache** (por ejemplo `wordpress:php8.2-apache`), que ya trae el core y Apache con `mod_rewrite` para permalinks.

2. **Volúmenes**:
   - Volumen nombrado montado en `/var/www/html` del contenedor WordPress para persistir core, plugins, uploads y configuración generada.
   - **Bind mount** solo de la carpeta del theme del repo, por ejemplo `./wp-content/themes/silveira` → `/var/www/html/wp-content/themes/silveira` (o `./theme` si prefieres nombre corto en el repo; lo importante es la ruta final dentro de WP).

3. **Puerto y host**:
   - Mapeo `8070:80` en el servicio WordPress.
   - Para que el navegador use un host fijo (p. ej. `http://silveira:8070` o `http://silveira.localhost:8070`), documentar:
     - Entrada en `/etc/hosts`: `127.0.0.1 silveira` (si eliges hostname corto), **o** usar `*.localhost` que muchos sistemas resuelven a loopback (`silveira.localhost`).
   - En WordPress, alinear **`WP_HOME`** y **`WP_SITEURL`** con esa URL base (variables de entorno vía `WORDPRESS_CONFIG_EXTRA` en la imagen oficial, o un `wp-config-docker.php` / fragmento montado solo si hace falta). Sin eso, WP redirige al hostname con el que se instaló y rompe permalinks o el login al cambiar de URL.

4. **Pretty URLs**: con Apache + volumen persistente, tras el primer arranque activar permalinks en el admin (o dejar `.htaccess` gestionado por WP). La imagen oficial suele traer lo necesario; el punto crítico es la **URL canónica** coherente con el host que usarás en el día a día.

## Pasos de implementación (cuando pidas código)

1. Crear estructura mínima del repo, por ejemplo:
   - `docker-compose.yml`
   - (Opcional) `README` con hosts + URL — solo si lo pides; el comando indicaba no escribir código aún.
   - `wp-content/themes/silveira/` con `style.css` mínimo del theme (o carpeta equivalente acordada).

2. Definir variables de entorno de WordPress (`WORDPRESS_DB_*`) y volumen DB.

3. Añadir `WORDPRESS_CONFIG_EXTRA` (o mecanismo equivalente) con `WP_HOME` / `WP_SITEURL` apuntando a la URL elegida (`http://silveira:8070` o `http://silveira.localhost:8070`).

4. `docker compose up -d`, completar instalación en el navegador con esa URL, activar permalinks.

5. Documentar en un sitio visible del equipo la línea de `/etc/hosts` y la URL exacta de desarrollo.

## Alternativas más complejas (no recomendadas de inicio)

- **Nginx + php-fpm** en lugar de la imagen oficial: más control, más archivos y mantenimiento.
- **wp-env** (Node): cómodo para plugins/themes en ecosistema JS, pero añade dependencia Node y no era el pedido explícito.
- **Bind mount de todo `wp-content`**: contradice “solo theme en el repo” salvo que el resto sea ignorado por git (menos limpio que volumen + solo theme).

## Trade-offs

| Decisión | Ventaja | Coste |
|----------|---------|--------|
| Imagen oficial WP + Apache | Menos Dockerfile, permalinks sencillos | Menos customizable que Nginx+FPM |
| Volumen en `/var/www/html` | Core y datos fuera del repo | Hay que hacer backup del volumen si se quiere clonar “todo” |
| Solo theme en bind mount | Repo pequeño y claro | Rutas y nombre del theme deben coincidir con lo que espera WP |
| Host personalizado | URLs estables como en producción | Cada dev debe tener `/etc/hosts` o usar `*.localhost` |

## Aclaración útil

La cadena **`silveira:localhost`** puede interpretarse como nombre de host + dominio (`silveira.localhost`) o como notación **host:puerto** en documentación informal. En la implementación conviene **fijar una URL canónica** (por ejemplo `http://silveira.localhost:8070`) y usarla siempre en `WP_HOME` / `WP_SITEURL` y en el navegador.

---

*Siguiente paso cuando lo indiques: generar `docker-compose.yml` y estructura de carpetas concreta (sin adelantar código en esta fase).*
