# Design

## Context

Ver proposal.md. `silveira_enqueue_vite_assets` registra Leaflet desde unpkg y lo encola en `is_front_page()`; `map.js` (incluido en el bundle de todas las páginas) comprueba `typeof L`. Iconos y tipografías ya se sirven desde el theme tras `self-hosted-fonts`.

## Goals / Non-Goals

**Goals:** cero peticiones a CDNs de terceros; Leaflet solo en la portada; teselas configurables.

**Non-Goals:** iconos y tipografías (`self-hosted-fonts`); alojar teselas propias; cambiar de librería de mapas; optimizar imágenes.

## Decisions

### D1. Leaflet por import dinámico
En `map.js`: si existe `#map`, `const [{ default: L }] = await Promise.all([import('leaflet'), import('leaflet/dist/leaflet.css')])`. Vite crea un chunk JS + CSS aparte, cargados solo en la portada. Los iconos por defecto de Leaflet no se usan (marcador `divIcon` propio), así que no hay que resolver sus imágenes.
- *Alternativa*: encolar `node_modules/leaflet/dist` copiado a `public/` con `wp_enqueue_script` — mantiene la global pero duplica la gestión de versiones fuera de Vite.
- El CSS del chunk dinámico lo inyecta el runtime de Vite en `<head>` al importarse; verificar que no hay parpadeo del mapa sin estilos (si lo hay, añadir el CSS al manifest y encolarlo desde PHP en la portada).

### D2. Teselas por configuración
`silveira_map_tiles()` devuelve `['url' => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png', 'attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors', 'maxZoom' => 19]` pasado por `apply_filters( 'silveira_map_tiles', ... )`, y se inyecta junto a los puntos (`window.silveiraMapConfig`). Se usa el host sin subdominios `{s}` (recomendación actual de OSM).
- La atribución es HTML controlado por el sitio (no por editores); Leaflet la inserta como HTML.

## Risks / Trade-offs

- [Cambiar de unpkg al bundle altera la versión de Leaflet] → fijar `leaflet@1.9.4` (la actual).
