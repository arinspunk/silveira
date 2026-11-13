# Spec Delta

## ADDED Requirements

### Requirement: Filter buttons announce their selection
El botón de cada desplegable de filtro SHALL tener como nombre accesible su etiqueta y, si hay opciones marcadas, cuántas, sin incluir el texto de ejemplo visible.

#### Scenario: Nothing selected
- **WHEN** un lector de pantalla llega al botón de "Modalidade educativa" sin opciones marcadas
- **THEN** lo anuncia como "Modalidade educativa", sin "Que procuras?"

#### Scenario: Options selected
- **WHEN** se marcan dos modalidades y el foco vuelve al botón
- **THEN** lo anuncia como "Modalidade educativa, 2 selecionadas"

#### Scenario: Incompatible localidade is unchecked
- **WHEN** el sistema desmarca una localidade incompatible con las comarcas elegidas
- **THEN** el número anunciado por el botón de "Localidade" se actualiza
