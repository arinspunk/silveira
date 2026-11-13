# Spec Delta

## ADDED Requirements

### Requirement: JavaScript libraries are served from the site
Las librerías de JavaScript y CSS que usa el theme SHALL servirse desde el dominio del sitio; el navegador SHALL NOT solicitarlas a CDNs de terceros.

#### Scenario: Any page
- **WHEN** se carga cualquier página pública con las herramientas de red del navegador abiertas
- **THEN** no hay peticiones a `unpkg.com` ni a otros CDNs de librerías

### Requirement: Map library loads only where there is a map
El código y estilos de la librería de mapas SHALL cargarse solo en páginas que contienen un mapa.

#### Scenario: Page without map
- **WHEN** se carga la ficha de un proyecto
- **THEN** no se descarga el código ni la hoja de estilos de la librería de mapas

#### Scenario: Front page
- **WHEN** se carga la portada
- **THEN** el mapa se muestra con sus controles y marcadores como antes

### Requirement: Map tiles are attributed and configurable
El mapa SHALL mostrar la atribución exigida por el proveedor de teselas, con enlace a su página de licencia, y el proveedor SHALL poder cambiarse por configuración del sitio sin modificar el JavaScript.

#### Scenario: Default provider
- **WHEN** se carga la portada sin configuración adicional
- **THEN** el mapa usa teselas de OpenStreetMap y muestra "© OpenStreetMap contributors" enlazado a la página de copyright de OpenStreetMap

#### Scenario: Alternative provider
- **WHEN** se configura otro proveedor con su URL de teselas y su atribución
- **THEN** el mapa pide las teselas a ese proveedor y muestra su atribución
