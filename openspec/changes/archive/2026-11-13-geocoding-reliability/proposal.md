# Proposal

## Why

El geocoding automático de proyectos (Nominatim, al guardar) coloca puntos mal o no los actualiza:

- **Limpieza que destroza direcciones:** quita subcadenas sin respetar palabras ("Localidade" pasa a "idade").
- **Sin actualización:** solo geocodifica si no hay coordenadas, así que al cambiar la dirección el punto se queda en el sitio viejo.
- **Resultados sin comprobar:** no mira el código HTTP ni si el resultado cae en la zona del proyecto.
- **Fallos silenciosos:** cuando falla, nadie se entera.
- **Política de Nominatim:** se incumple en identificación (no hay contacto en el User-Agent ni en la petición) y no hay forma de geocodificar en lote respetando su límite.

## What Changes

- **Limpieza de ruido de la dirección** solo con palabras completas ("Local", "Baixo/Bajo", "Planta", "Piso", "Esquerda/Izquierda/Derecha"…).
- **Búsqueda acotada:** a España (`countrycodes=es`) y a una caja geográfica que cubre Galiza incluida la Galiza exterior (Eo-Navia, Bierzo, As Portelas, Seabra), con `viewbox` + `bounded`. Se descartan los resultados fuera de esa caja.
- **Re-geocodificación cuando cambia la dirección**, salvo que alguien haya fijado las coordenadas a mano después del último geocoding. En ese caso se respetan.
- **Comprobaciones de la respuesta:** código HTTP 200, timeout explícito y coordenadas numéricas.
- **Aviso en el editor del proyecto** cuando el geocoding falla o no encuentra la dirección.
- **Identificación ante Nominatim:** User-Agent con la URL del sitio y, si se configura, un email de contacto mediante una constante.
- **Comando WP-CLI** para geocodificar en lote los proyectos sin coordenadas, con 1 petición por segundo como máximo.

## Capabilities

### New Capabilities
- `project-geocoding`: obtención automática de coordenadas de los proyectos a partir de su dirección y su relación con las coordenadas introducidas a mano.

### Modified Capabilities
<!-- Ninguna -->

## Impact

- `inc/geocoding.php` (tras `theme-robustness`; si no, `functions.php`).
- Nuevo comando WP-CLI registrado solo si `WP_CLI` está definido.
- Meta privada nueva por proyecto: `_silveira_geocode` (dirección normalizada y coordenadas obtenidas). Sin cambios en los campos ACF.
- Petición saliente a `nominatim.openstreetmap.org` (ya existía).
