# external-resources Specification

## Purpose

Define qué recursos de terceros puede solicitar el navegador al visitar el sitio y garantiza que los recursos propios (iconos, tipografías, librerías) se sirven desde el propio sitio.

## Requirements

### Requirement: Icons and typefaces are served from the site
La fuente de iconos y las tipografías del theme SHALL servirse desde el dominio del sitio; el navegador SHALL NOT solicitarlas a servicios de fuentes de terceros.

#### Scenario: Any page
- **WHEN** se carga cualquier página pública con las herramientas de red del navegador abiertas
- **THEN** no hay peticiones a `fonts.googleapis.com` ni `fonts.gstatic.com`, ni `preconnect` a esos dominios en el HTML

#### Scenario: Icons render
- **WHEN** se carga una página con iconos (flechas, `location_on`, `expand_more`)
- **THEN** los iconos se ven como glifos, no como el texto de su nombre

### Requirement: Design typefaces are applied
Los textos del sitio SHALL mostrarse con la tipografía del diseño, Isaac, con sus estilos reales de negrita y cursiva, mostrando texto con la fuente de reserva mientras se descarga.

#### Scenario: Body text
- **WHEN** se carga una página y se inspecciona la fuente renderizada de un párrafo
- **THEN** la fuente usada es Isaac

#### Scenario: Emphasis
- **WHEN** se inspecciona la fuente renderizada del lema de una ficha de proyecto (cursiva) y de una etiqueta de modalidade (negrita)
- **THEN** se usan los ficheros Isaac Italic e Isaac Bold respectivamente, no estilos sintetizados por el navegador

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
