# project-geocoding Specification

## Purpose

Automatically obtains the coordinates of each project from its address to place it on the map, without overwriting coordinates set by hand and warning when it is not possible.

## Requirements

### Requirement: Coordinates are obtained for new addresses
When a project with an address and no coordinates is saved, the system SHALL obtain the latitude and longitude of the address and save them in the project.

#### Scenario: New project with address
- **WHEN** a project is published with the address "Rúa do Vilar 10, Santiago de Compostela" and no coordinates
- **THEN** the project ends up with coordinates located in Santiago de Compostela

### Requirement: Address noise words are removed as whole words only
Before searching, the system SHALL remove from the address the noise words for premises and floors only when they appear as whole words.

#### Scenario: Word containing a noise word
- **WHEN** the address is "Rúa da Localidade 3, Lalín"
- **THEN** the search keeps "Localidade" intact

#### Scenario: Standalone noise word
- **WHEN** the address is "Rúa Nova 5, Baixo, Ourense"
- **THEN** the search is done without the word "Baixo"

### Requirement: Changed addresses are re-geocoded
When a project's address changes and its coordinates are the ones obtained in the last geocoding, the system SHALL recalculate the coordinates.

#### Scenario: Address edited
- **WHEN** a project geocoded in Lugo changes its address to one in Vigo and is saved
- **THEN** its coordinates move to Vigo

### Requirement: Manually set coordinates are preserved
When a project's coordinates differ from those obtained in its last geocoding, the system SHALL consider them set by hand and not overwrite them, even if the address changes.

#### Scenario: Manual adjustment then address edit
- **WHEN** someone corrects the coordinates by hand and then changes the address
- **THEN** the hand-corrected coordinates are kept

### Requirement: Results outside Galiza are rejected
The system SHALL accept only results within the geographic area of Galiza, including the Galiza exterior (Eo-Navia, Bierzo, As Portelas, Seabra); if there are none, the project keeps its current coordinates.

#### Scenario: Ambiguous place name
- **WHEN** the address only matches a place with the same name outside Galiza
- **THEN** no new coordinates are saved and the failure notice is shown

#### Scenario: Project in the Galiza exterior
- **WHEN** a project is saved with an address in Ponferrada (Bierzo), A Póvoa de Seabra or Navia
- **THEN** the coordinates found are saved and the project appears on the map

### Requirement: Editors are told when geocoding fails
If the search fails (network error, invalid response or no results), the system SHALL show a notice on the project's edit screen after saving, without preventing the save.

#### Scenario: Address not found
- **WHEN** a project is saved with an address that produces no results
- **THEN** the project is saved and the editor sees a notice saying that the address should be reviewed or the coordinates entered by hand

#### Scenario: Service unavailable
- **WHEN** the geocoding service does not respond within the time limit
- **THEN** the project is saved without changes to its coordinates and the notice is shown

### Requirement: Bulk geocoding respects the provider rate limit
The system SHALL offer an administration command that geocodes the projects without coordinates, making at most one request per second and reporting the results.

#### Scenario: Backfill
- **WHEN** the command is run with 5 projects without coordinates
- **THEN** 5 requests are made at least one second apart and it reports how many were geocoded and which failed
