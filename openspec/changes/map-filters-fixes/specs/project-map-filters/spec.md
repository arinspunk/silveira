# Spec Delta

## Purpose

Permite acotar los proyectos del mapa de la portada por modalidade educativa, comarca y localidade, mostrando solo combinaciones posibles y siendo operable con ratón, táctil y teclado.

## ADDED Requirements

### Requirement: Filter options reflect the territory hierarchy
El filtro "Comarca" SHALL listar solo territorios de primer nivel con proyectos, y el filtro "Localidade" SHALL listar solo territorios que tienen una comarca padre y proyectos.

#### Scenario: Comarca without localidades
- **WHEN** existe la comarca "Deza" con proyectos asignados directamente y sin localidades hijas
- **THEN** "Deza" aparece en "Comarca" y no aparece en "Localidade"

#### Scenario: Localidade option
- **WHEN** existe la localidade "Lalín" hija de "Deza" con proyectos
- **THEN** "Lalín" aparece solo en "Localidade"

### Requirement: Filters combine across groups and within groups
Dentro de un mismo filtro las opciones marcadas SHALL combinarse como O; entre filtros distintos SHALL combinarse como Y.

#### Scenario: Two modalidades and one comarca
- **WHEN** se marcan las modalidades "Escola" y "Obradoiro" y la comarca "Deza"
- **THEN** el mapa y la lista muestran los proyectos de Deza que son Escola u Obradoiro

### Requirement: Only compatible options are offered
Cada filtro SHALL ocultar las opciones que no producirían resultados combinadas con las selecciones de los otros filtros; las opciones marcadas SHALL permanecer visibles salvo lo indicado para localidades.

#### Scenario: Localidades limited by comarca
- **WHEN** se marca la comarca "Deza"
- **THEN** "Localidade" muestra solo localidades de Deza con proyectos compatibles con las demás selecciones

#### Scenario: Incompatible localidade is unchecked
- **WHEN** está marcada la localidade "Lalín" (Deza) y se marca solo la comarca "Ordes"
- **THEN** "Lalín" se desmarca y deja de mostrarse, y el mapa refleja la nueva selección

### Requirement: Empty result is explained
Cuando la combinación de filtros no devuelve ningún proyecto, la lista lateral SHALL mostrar un mensaje indicando que no hay proyectos para esos filtros.

#### Scenario: No matches
- **WHEN** los filtros activos no coinciden con ningún proyecto
- **THEN** no hay marcadores y la lista lateral muestra el mensaje de sin resultados

### Requirement: Filter dropdowns are keyboard and screen-reader operable
Cada desplegable de filtro SHALL poder abrirse y cerrarse con teclado, exponer su estado expandido a tecnologías de asistencia y permitir marcar opciones con teclado.

#### Scenario: Open with keyboard
- **WHEN** el foco está en el desplegable "Comarca" y se pulsa Enter o Espacio
- **THEN** se abre, su estado expandido pasa a verdadero y el foco va a la primera opción visible

#### Scenario: Close with Escape
- **WHEN** un desplegable está abierto y se pulsa Escape
- **THEN** se cierra y el foco vuelve a su botón

#### Scenario: Only one open at a time
- **WHEN** "Comarca" está abierto y se abre "Modalidade educativa"
- **THEN** "Comarca" se cierra
