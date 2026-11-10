# Proposal

## Why

El mapa interactivo de la portada pinta los datos de cada proyecto (título, lema, extracto, imagen, URL) con `innerHTML` sin escapar, y los entrega al navegador mediante un `<script>` en línea generado con `json_encode` sin protección frente a `</script>`. Cualquier persona con permiso para editar un proyecto puede inyectar HTML o JavaScript en la portada pública. Además, el JS compilado que se versiona en `assets/` está desfasado respecto a `src/` (no incluye, por ejemplo, la expansión del mapa al pulsar un marcador), así que producción no refleja el código actual.

## What Changes

- Los datos de los proyectos para el mapa se generan en una función dedicada del theme y se entregan al navegador asociados al script principal, codificados de forma segura (sin `<script>` en línea en la plantilla).
- Los campos de texto se envían como texto plano normalizado (sin etiquetas y con entidades decodificadas); las URLs de enlace e imagen se sanean y solo se aceptan `http`/`https`.
- El popup del marcador y la lista lateral se construyen como nodos DOM con `textContent` y atributos asignados, en lugar de concatenar HTML con datos del proyecto.
- Se regenera el build de `assets/` para que producción incluya estos cambios y los ya existentes en `src/` (expansión del mapa al pulsar un marcador).
- Fuera de alcance: mecanismos para impedir que el build vuelva a desfasarse (CI/hook), cambios en el comportamiento de scroll/footer, y el geocoding.

## Capabilities

### New Capabilities
- `project-map`: mapa interactivo de proyectos en la portada — qué información de cada proyecto se muestra en marcadores, popups y lista lateral, y cómo se presenta de forma segura.

### Modified Capabilities
<!-- Ninguna: no existen specs previas en el proyecto. -->

## Impact

- `wp-content/themes/silveira/front-page.php`: se elimina la construcción de datos y el `<script>` en línea.
- Nuevo `wp-content/themes/silveira/inc/map-data.php` (obtención y normalización de puntos) y su `require` en `functions.php`.
- `wp-content/themes/silveira/src/js/map.js`: renderizado de popup y lista.
- `wp-content/themes/silveira/assets/` (build regenerado: JS, CSS y `manifest.json` con nuevos hashes).
- Sin cambios en `phpcs.xml.dist` / `phpstan.neon.dist`: el tramo 2 de `enforce-build-and-lint` los extiende a todo el theme.
- Orden: después del grupo 1 de `enforce-build-and-lint` y antes de sus grupos 2–5.
- Sin cambios de dependencias ni de modelo de datos (ACF/taxonomías intactos).
