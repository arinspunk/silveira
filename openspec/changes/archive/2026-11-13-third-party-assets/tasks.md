# Tasks

## 1. Leaflet

- [x] 1.1 Añadir `leaflet@1.9.4` como dependencia del theme y cargarlo por import dinámico en `map.js` (design D1); eliminar el registro de unpkg en PHP y la global `L` de `eslint.config.js`; verificar "Front page" y "Page without map" en la pestaña Red (sin `leaflet` fuera de la portada, sin `unpkg.com`) y que `npm run lint` pasa
- [x] 1.2 Verificar que el mapa no se ve sin estilos al cargar con la caché vacía y red lenta (throttling "Fast 3G"); si parpadea, encolar el CSS del chunk desde PHP como indica design D1

## 2. Teselas

- [x] 2.1 Crear `silveira_map_tiles()` con filtro e inyectarlo como `window.silveiraMapConfig`, y usarlo en `map.js` (design D2); verificar "Default provider" (atribución con enlace a `/copyright`) y "Alternative provider" con un filtro temporal en un mu-plugin de prueba que se retira después

## 3. Cierre

- [x] 3.1 Revisar la pestaña Red en portada, ficha de proyecto, archivo y búsqueda: solo peticiones al dominio del sitio y a las teselas; verificar que el commit incluye `assets/` regenerado por el hook y que la CI (incluido el smoke test de `map-smoke-tests`) pasa
