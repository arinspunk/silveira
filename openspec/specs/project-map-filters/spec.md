# project-map-filters Specification

## Purpose

Lets visitors narrow down the projects on the front-page map by educational modalidade, comarca and localidade, showing only possible combinations and being operable with mouse, touch and keyboard.

## Requirements

### Requirement: Filter options reflect the territory hierarchy
The "Comarca" filter SHALL list only top-level territories with projects, and the "Localidade" filter SHALL list only territories that have a parent comarca and projects.

#### Scenario: Comarca without localidades
- **WHEN** the comarca "Deza" exists with projects assigned directly and no child localidades
- **THEN** "Deza" appears in "Comarca" and does not appear in "Localidade"

#### Scenario: Localidade option
- **WHEN** the localidade "Lalín", child of "Deza", exists with projects
- **THEN** "Lalín" appears only in "Localidade"

### Requirement: Filters combine across groups and within groups
Within the same filter, checked options SHALL be combined with OR; across different filters they SHALL be combined with AND.

#### Scenario: Two modalidades and one comarca
- **WHEN** the modalidades "Escola" and "Obradoiro" and the comarca "Deza" are checked
- **THEN** the map and the list show the Deza projects that are Escola or Obradoiro

### Requirement: Only compatible options are offered
Each filter SHALL hide the options that would produce no results combined with the selections in the other filters; checked options SHALL remain visible except as stated for localidades. The "Comarca" options SHALL depend only on the modalidade, not on the checked localidade: the localidade is a refinement within the comarca, and choosing another comarca unchecks the incompatible localidades.

#### Scenario: Localidades limited by comarca
- **WHEN** the comarca "Deza" is checked
- **THEN** "Localidade" shows only Deza localidades with projects compatible with the other selections

#### Scenario: Incompatible localidade is unchecked
- **WHEN** the localidade "Lalín" (Deza) is checked and only the comarca "Ordes" is checked
- **THEN** "Lalín" is unchecked and no longer shown, and the map reflects the new selection

### Requirement: Empty result is explained
When the combination of filters returns no project, the side list SHALL show a message saying there are no projects for those filters.

#### Scenario: No matches
- **WHEN** the active filters match no project
- **THEN** there are no markers and the side list shows the no-results message

### Requirement: Filter dropdowns are keyboard and screen-reader operable
Each filter dropdown SHALL be openable and closable with the keyboard, expose its expanded state to assistive technologies and allow options to be checked with the keyboard.

#### Scenario: Open with keyboard
- **WHEN** focus is on the "Comarca" dropdown and Enter or Space is pressed
- **THEN** it opens, its expanded state becomes true and focus moves to the first visible option

#### Scenario: Close with Escape
- **WHEN** a dropdown is open and Escape is pressed
- **THEN** it closes and focus returns to its button

#### Scenario: Only one open at a time
- **WHEN** "Comarca" is open and "Modalidade educativa" is opened
- **THEN** "Comarca" closes

### Requirement: Filter buttons announce their selection
Each filter dropdown button SHALL have as its accessible name its label and, if options are checked, how many, without including the visible placeholder text.

#### Scenario: Nothing selected
- **WHEN** a screen reader reaches the "Modalidade educativa" button with no options checked
- **THEN** it announces it as "Modalidade educativa", without "Que procuras?"

#### Scenario: Options selected
- **WHEN** two modalidades are checked and focus returns to the button
- **THEN** it announces it as "Modalidade educativa, 2 selecionadas"

#### Scenario: Incompatible localidade is unchecked
- **WHEN** the system unchecks a localidade that is incompatible with the chosen comarcas
- **THEN** the number announced by the "Localidade" button is updated
