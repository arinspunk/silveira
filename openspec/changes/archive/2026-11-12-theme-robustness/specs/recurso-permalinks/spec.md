# Spec Delta

## Purpose

Define la estructura de URLs de los recursos y sus categorías, garantizando que todo recurso publicado tenga una URL que resuelve.

## ADDED Requirements

### Requirement: Every recurso has a category
Al guardar un recurso sin ninguna categoría asignada, el sistema SHALL asignarle la categoría `geral`, creándola si no existe.

#### Scenario: Recurso saved without category
- **WHEN** se publica un recurso sin marcar ninguna categoría
- **THEN** el recurso queda asignado a la categoría `geral`

#### Scenario: Recurso with category
- **WHEN** se publica un recurso con la categoría `guias`
- **THEN** no se le añade `geral`

### Requirement: Recurso URLs resolve
La URL de un recurso SHALL tener la forma `/recursos/<categoria>/<recurso>/` y abrir su ficha; `/recursos/<categoria>/` SHALL abrir el archivo de la categoría y `/recursos/` el archivo general.

#### Scenario: Single recurso
- **WHEN** un visitante abre el enlace de un recurso de la categoría `guias`
- **THEN** la URL es `/recursos/guias/<slug>/` y se muestra la ficha con estado 200

#### Scenario: Category and general archives
- **WHEN** un visitante abre `/recursos/guias/` o `/recursos/`
- **THEN** se muestra el listado correspondiente con estado 200

#### Scenario: Paginated category archive
- **WHEN** una categoría tiene más recursos que los mostrados por página y se abre `/recursos/guias/page/2/`
- **THEN** se muestra la segunda página del archivo, no un 404
