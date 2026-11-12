# theme-dependencies Specification

## Purpose

Define cómo se comporta el sitio cuando falta un plugin del que depende el theme (ACF), para que nunca produzca un error fatal y avise a quien administra el sitio.

## Requirements

### Requirement: Public site works without ACF
Cuando ACF no está activo, todas las páginas públicas SHALL renderizarse sin errores fatales, omitiendo solo la información que proviene de campos ACF.

#### Scenario: Front page without ACF
- **WHEN** ACF está desactivado y un visitante abre la portada
- **THEN** la página carga con hero, filtros y mapa, y el mapa no muestra marcadores

#### Scenario: Project page without ACF
- **WHEN** ACF está desactivado y un visitante abre la ficha de un proyecto
- **THEN** la página muestra título, contenido y territorios, sin lema ni dirección, y sin errores

### Requirement: Administrators are warned when ACF is missing
Cuando ACF no está activo, el panel de administración SHALL mostrar un aviso a los usuarios con capacidad de gestionar plugins indicando que los campos de proyecto no están disponibles.

#### Scenario: Admin notice
- **WHEN** ACF está desactivado y una persona administradora entra en el escritorio
- **THEN** ve un aviso que nombra ACF como plugin requerido

#### Scenario: No notice for editors
- **WHEN** ACF está desactivado y entra un usuario sin capacidad `activate_plugins`
- **THEN** no se muestra el aviso
