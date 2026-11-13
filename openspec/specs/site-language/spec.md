# site-language Specification

## Purpose

Sets the language norm for the text the theme shows on the public site and in the admin (galego reintegrado, "umha"/"-çom"/"nom" variant) and makes all of it translatable.

## Requirements

### Requirement: Public interface text uses the site language norm
All interface text the theme shows on the public site SHALL be in galego reintegrado, "umha"/"-çom"/"nom" variant, without text in Spanish, English or standard Portuguese.

#### Scenario: Not found page
- **WHEN** a visitor reaches a search with no results
- **THEN** the message and the search form are shown in galego reintegrado, without forms such as "não", "pueda" or "con palavras-chave"

#### Scenario: Map list toggle
- **WHEN** a visitor opens and closes the map's side list
- **THEN** the button texts and "ver o projeto" are in galego reintegrado

#### Scenario: Navigation labels
- **WHEN** a screen reader announces the header and footer navigation regions
- **THEN** their accessible names are in galego reintegrado

### Requirement: Admin labels defined by the theme use the site language norm
The labels of the content types, taxonomies, menu locations and admin notices defined by the theme SHALL be in galego reintegrado, "umha"/"-çom"/"nom" variant.

#### Scenario: Empty trash message
- **WHEN** an editor opens the empty projects trash
- **THEN** the message uses the site's variant (e.g. with "Nom"), not "Não encontrado na lixeira"

### Requirement: Theme text is translatable
All of the theme's interface text, including the text shown by JavaScript, SHALL be obtained through the WordPress translation functions with the `silveira` domain.

#### Scenario: Translation file overrides a string
- **WHEN** a translation file for the `silveira` domain is installed for the site language that changes the text of the map list button
- **THEN** the map shows the translated text

### Requirement: Archive titles have no generic prefix
Archive page titles SHALL show only the name of the content type, territory or category, without prefixes such as "Arquivo:", "Archivo:" or "Categoria:", in any language configured in WordPress.

#### Scenario: Territory archive
- **WHEN** a visitor opens the archive of the territory "Deza"
- **THEN** the hero title is "Deza"

### Requirement: Search terms are shown verbatim
The search results title SHALL show the searched term as the visitor typed it, escaped only once.

#### Scenario: Search with ampersand
- **WHEN** "horta & mar" is searched
- **THEN** the title shows `horta & mar`, not `horta &amp; mar`
