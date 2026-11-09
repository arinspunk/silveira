# Spec Delta

## Purpose

Define qué recursos de terceros puede solicitar el navegador al visitar el sitio y garantiza que los recursos propios (iconos, tipografías, librerías) se sirven desde el propio sitio.

## ADDED Requirements

### Requirement: Icons and typefaces are served from the site
La fuente de iconos y las tipografías del theme SHALL servirse desde el dominio del sitio; el navegador SHALL NOT solicitarlas a servicios de fuentes de terceros.

#### Scenario: Any page
- **WHEN** se carga cualquier página pública con las herramientas de red del navegador abiertas
- **THEN** no hay peticiones a `fonts.googleapis.com` ni `fonts.gstatic.com`, ni `preconnect` a esos dominios en el HTML

#### Scenario: Icons render
- **WHEN** se carga una página con iconos (flechas, `location_on`, `expand_more`)
- **THEN** los iconos se ven como glifos, no como el texto de su nombre

### Requirement: Design typefaces are applied
Los textos del sitio SHALL mostrarse con las tipografías del diseño (Isaac para texto, Akkurat-Mono para monoespaciado), mostrando texto con la fuente de reserva mientras se descargan.

#### Scenario: Body text
- **WHEN** se carga una página y se inspecciona la fuente renderizada de un párrafo
- **THEN** la fuente usada es Isaac

#### Scenario: Monospaced text
- **WHEN** se inspecciona la fuente renderizada de un texto con estilo monoespaciado
- **THEN** la fuente usada es Akkurat-Mono
