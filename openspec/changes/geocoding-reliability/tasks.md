# Tasks

## 1. Búsqueda

- [ ] 1.1 Extraer `silveira_geocode_address( $address )` que limpia por palabra (design D3), construye la petición acotada (D2) y devuelve `['lat','lng']` o `WP_Error` con motivo; verificar con `wp eval` sobre "Rúa da Localidade 3, Lalín", "Rúa Nova 5, Baixo, Ourense", una dirección en Ponferrada, otra en A Póvoa de Seabra y una inexistente que los resultados y errores son los esperados
- [ ] 1.2 Añadir identificación (User-Agent con versión y `home_url`, `email` opcional por `SILVEIRA_NOMINATIM_EMAIL`) y documentar la constante en el README; verificar inspeccionando la petición con el filtro `pre_http_request` en una prueba con `wp eval`

## 2. Decisión al guardar

- [ ] 2.1 Implementar la tabla de decisión de design D1 con la meta `_silveira_geocode` en el hook de guardado; verificar los escenarios "New project with address", "Address edited" y "Manual adjustment then address edit" en el admin
- [ ] 2.2 Verificar que un proyecto existente con coordenadas y sin meta no cambia al re-guardarlo

## 3. Avisos

- [ ] 3.1 Implementar el aviso de fallo (design D4); verificar los escenarios "Address not found" y "Service unavailable" (simulado con `pre_http_request` devolviendo `WP_Error`) en el editor de bloques, y que el proyecto se guarda igualmente

## 4. Lote

- [ ] 4.1 Registrar `wp silveira geocode [--dry-run]` (design D5); verificar con varios proyectos de prueba sin coordenadas que respeta ≥ 1 s entre peticiones (marca de tiempo en la salida) y que el resumen es correcto
- [ ] 4.2 Documentar el comando en el README; verificar que se ejecuta tal cual con `docker compose --profile tools run --rm wpcli wp silveira geocode --dry-run`
