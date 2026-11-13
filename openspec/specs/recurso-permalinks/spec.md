# recurso-permalinks Specification

## Purpose

Defines the URL structure of resources and their categories, ensuring that every published resource has a URL that resolves.

## Requirements

### Requirement: Every recurso has a category
When a resource is saved without any category, the system SHALL assign it the `geral` category, creating it if it does not exist.

#### Scenario: Recurso saved without category
- **WHEN** a resource is published without any category checked
- **THEN** the resource is assigned to the `geral` category

#### Scenario: Recurso with category
- **WHEN** a resource is published with the `guias` category
- **THEN** `geral` is not added to it

### Requirement: Recurso URLs resolve
A resource's URL SHALL have the form `/recursos/<categoria>/<recurso>/` and open its page; `/recursos/<categoria>/` SHALL open the category archive and `/recursos/` the general archive.

#### Scenario: Single recurso
- **WHEN** a visitor opens the link of a resource in the `guias` category
- **THEN** the URL is `/recursos/guias/<slug>/` and its page is shown with status 200

#### Scenario: Category and general archives
- **WHEN** a visitor opens `/recursos/guias/` or `/recursos/`
- **THEN** the corresponding listing is shown with status 200

#### Scenario: Paginated category archive
- **WHEN** a category has more resources than are shown per page and `/recursos/guias/page/2/` is opened
- **THEN** the second page of the archive is shown, not a 404
