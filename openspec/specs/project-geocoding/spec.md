# project-geocoding Specification

## Purpose

Obtiene automáticamente las coordenadas de cada proyecto a partir de su dirección para situarlo en el mapa, sin pisar coordenadas fijadas a mano y avisando cuando no es posible.

## Requirements

### Requirement: Coordinates are obtained for new addresses
Al guardar un proyecto con dirección y sin coordenadas, el sistema SHALL obtener latitud y longitud de la dirección y guardarlas en el proyecto.

#### Scenario: New project with address
- **WHEN** se publica un proyecto con dirección "Rúa do Vilar 10, Santiago de Compostela" y sin coordenadas
- **THEN** el proyecto queda con coordenadas situadas en Santiago de Compostela

### Requirement: Address noise words are removed as whole words only
Antes de buscar, el sistema SHALL eliminar de la dirección las palabras de ruido de locales y pisos solo cuando aparecen como palabras completas.

#### Scenario: Word containing a noise word
- **WHEN** la dirección es "Rúa da Localidade 3, Lalín"
- **THEN** la búsqueda conserva "Localidade" íntegra

#### Scenario: Standalone noise word
- **WHEN** la dirección es "Rúa Nova 5, Baixo, Ourense"
- **THEN** la búsqueda se hace sin la palabra "Baixo"

### Requirement: Changed addresses are re-geocoded
Cuando cambia la dirección de un proyecto y sus coordenadas son las obtenidas en el último geocoding, el sistema SHALL recalcular las coordenadas.

#### Scenario: Address edited
- **WHEN** un proyecto geocodificado en Lugo cambia su dirección a una de Vigo y se guarda
- **THEN** sus coordenadas pasan a estar en Vigo

### Requirement: Manually set coordinates are preserved
Cuando las coordenadas de un proyecto difieren de las obtenidas en su último geocoding, el sistema SHALL considerarlas fijadas a mano y no sobrescribirlas, aunque cambie la dirección.

#### Scenario: Manual adjustment then address edit
- **WHEN** alguien corrige a mano las coordenadas y después cambia la dirección
- **THEN** se mantienen las coordenadas corregidas a mano

### Requirement: Results outside Galiza are rejected
El sistema SHALL aceptar solo resultados dentro de la zona geográfica de Galiza, incluida la Galiza exterior (Eo-Navia, Bierzo, As Portelas, Seabra); si no hay ninguno, el proyecto conserva sus coordenadas actuales.

#### Scenario: Ambiguous place name
- **WHEN** la dirección solo coincide con un lugar homónimo fuera de Galiza
- **THEN** no se guardan coordenadas nuevas y se muestra el aviso de fallo

#### Scenario: Project in the Galiza exterior
- **WHEN** se guarda un proyecto con dirección en Ponferrada (Bierzo), A Póvoa de Seabra o Navia
- **THEN** se guardan las coordenadas encontradas y el proyecto aparece en el mapa

### Requirement: Editors are told when geocoding fails
Si la búsqueda falla (error de red, respuesta no válida o sin resultados), el sistema SHALL mostrar un aviso en la pantalla de edición del proyecto tras guardar, sin impedir el guardado.

#### Scenario: Address not found
- **WHEN** se guarda un proyecto con una dirección que no produce resultados
- **THEN** el proyecto se guarda y la persona editora ve un aviso indicando que hay que revisar la dirección o introducir coordenadas a mano

#### Scenario: Service unavailable
- **WHEN** el servicio de geocoding no responde en el tiempo límite
- **THEN** el proyecto se guarda sin cambios en coordenadas y se muestra el aviso

### Requirement: Bulk geocoding respects the provider rate limit
El sistema SHALL ofrecer un comando de administración que geocodifica los proyectos sin coordenadas haciendo como máximo una petición por segundo e informando de los resultados.

#### Scenario: Backfill
- **WHEN** se ejecuta el comando con 5 proyectos sin coordenadas
- **THEN** se hacen 5 peticiones separadas al menos un segundo y se informa de cuántos se geocodificaron y cuáles fallaron
