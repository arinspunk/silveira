# Proposal

## Why

El mapa de la portada depende de recursos de terceros servidos desde sus dominios:

- **Leaflet** (JS y CSS) desde `unpkg.com`, sin integridad (SRI) y registrado en todas las páginas.
- **Teselas del mapa** de `tile.openstreetmap.org`, con la URL fija en el código y una atribución sin enlace.

Los iconos (Material Symbols) y la tipografía propia (Isaac) ya se sirven desde el theme gracias a `self-hosted-fonts`.

## What Changes

- **Leaflet desde npm**, cargado por import dinámico solo cuando la página tiene mapa. Vite lo genera como un fragmento aparte dentro de `assets/`. Se elimina el registro de `unpkg`, y la global `L` deja de ser necesaria.
- **Proveedor de teselas configurable** desde PHP: URL, atribución y zoom máximo mediante un filtro. El valor por defecto sigue siendo OpenStreetMap, con la atribución enlazada a su página de copyright.

## Capabilities

### New Capabilities
<!-- Ninguna -->

### Modified Capabilities
- `external-resources` (creada por `self-hosted-fonts`): se añade que las librerías JS/CSS se sirven desde el sitio, que la librería de mapas solo se carga donde hay mapa y que las teselas están atribuidas y son configurables.

## Impact

- **Dependencias:** `package.json`/`package-lock.json` del theme (nueva `leaflet`).
- **Código:** `src/js/map.js` (import dinámico, teselas desde configuración), `inc/vite.php` (sin Leaflet).
- **Datos para JS:** `inc/map-data.php` (configuración de teselas).
- **Configuración y build:** `eslint.config.js` (se quita la global `L` añadida en `enforce-build-and-lint`); `assets/` lo regenera el hook.
- **Orden recomendado:** después de `homepage-scroll-a11y`, porque toca la inicialización de `map.js`, y antes de `i18n-reintegrado`.
