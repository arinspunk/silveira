# Design

## Context

Ver proposal.md. Hoy `silveira_geocode_project` corre en `acf/save_post` (prioridad 20), usa `wp_remote_get` con timeout por defecto (5 s), añade ", Galiza, España" a la dirección y guarda el primer resultado si lat/lng están vacías.

## Goals / Non-Goals

**Goals:** resultados correctos en Galiza, actualización al cambiar dirección, respeto a coordenadas manuales, fallos visibles, cumplimiento básico de la política de Nominatim.

**Non-Goals:** geocoding asíncrono/cola (volumen editorial bajo); cambiar de proveedor; selector de mapa en el admin para colocar el punto (posible change futuro); geocoding de eventos.

## Decisions

### D1. Estado del último geocoding en meta privada
`_silveira_geocode` = `{ address: <dirección normalizada>, lat, lng, at }`. Al guardar:

```
sin dirección                         -> nada
sin coordenadas                       -> geocodificar
dirección != meta.address
   y coords == meta.lat/lng (±1e-6)   -> geocodificar
   y coords != meta                   -> manual: no tocar
dirección == meta.address             -> nada
```
Proyectos existentes sin meta y con coordenadas se tratan como manuales (no se tocan) — conservador.
- *Alternativa*: un checkbox ACF "fijar coordenadas" — explícito pero añade un campo y depende de que la gente lo marque.

### D2. Petición
`https://nominatim.openstreetmap.org/search` con `format=jsonv2`, `q`, `limit=1`, `countrycodes=es`, `viewbox=-9.35,43.85,-6.10,41.80`, `bounded=1`, `email` si `SILVEIRA_NOMINATIM_EMAIL` está definida. `wp_remote_get` con `timeout => 8` y `user-agent => 'Silveira/<versión theme> (+<home_url>)'`. Se valida `wp_remote_retrieve_response_code === 200`, `is_numeric` y que el punto cae en la caja.
- La caja va de −9,35 a −6,10 de longitud y de 41,80 a 43,85 de latitud: cubre la comunidad autónoma (Estaca de Bares está en 43,79) y la Galiza exterior (Navia −6,72; Ponferrada −6,60; A Póvoa de Seabra −6,64; el límite este del Bierzo ronda −6,2). `countrycodes=es` se mantiene: todas esas zonas están en el Estado español y así se evitan homónimos en Portugal.
- Se deja de añadir ", Galiza, España" a la consulta: `viewbox`+`bounded` restringen mejor y evitan que "Galiza" (no reconocido siempre) degrade la búsqueda.

### D3. Limpieza por palabra
`preg_replace( '/\b(?:local|baixo|bajo|planta|piso|esquerda|esquerdo|direita|dereita|derecha|izquierda)\b\.?/iu', '', $address )`, colapsar espacios y comas repetidas. Con `u` para acentos.

### D4. Aviso
Si falla, `set_transient( 'silveira_geocode_notice_' . get_current_user_id(), {post_id, reason}, 60 )`; en `admin_notices` de la pantalla de edición del proyecto se muestra y borra. Compatible con el editor de bloques si el guardado de ACF recarga la meta box; si no aparece en Gutenberg, usar `wp.data.dispatch('core/notices')` vía un pequeño script de admin (decidir en implementación según prueba).

### D5. WP-CLI
`wp silveira geocode [--dry-run]`: itera proyectos con dirección y sin coordenadas, reutiliza la misma función, `sleep(1)` entre peticiones, imprime tabla de resultados.

## Risks / Trade-offs

- [La caja ampliada también incluye partes de Asturias, León y Zamora que no son de lengua galega (p. ej. Cangas del Narcea, Bembibre-Astorga)] → aceptable: solo afecta a direcciones homónimas poco probables, y el editor ve el punto en el mapa y puede corregirlo. La caja es filtrable con `silveira_geocode_viewbox`.
- [El aviso no se ve en el editor de bloques] → ver D4; verificación explícita en tareas.
- [Guardado más lento por la petición síncrona] → solo ocurre al cambiar la dirección; timeout 8 s.
