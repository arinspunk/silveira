# content-templates Specification

## Purpose

Defines the common structure of the listing and detail pages for the site's content (projects, events, resources and territories), so that they are consistent and accessible.

## Requirements

### Requirement: One main heading per page
Every public page SHALL have exactly one level-1 heading, which is the page title shown in the hero.

#### Scenario: Project page
- **WHEN** a visitor opens a project page
- **THEN** the page contains a single `h1` with the project title

#### Scenario: Archive page
- **WHEN** a visitor opens the project archive with 10 projects
- **THEN** the page contains a single `h1` and each project is presented with a lower-level heading

#### Scenario: Not found page
- **WHEN** a visitor opens a URL that does not exist
- **THEN** the page responds 404 and contains a single `h1` saying that the page was not found

### Requirement: Heading levels do not skip
Within the content of the theme's templates, headings SHALL go down one level at a time.

#### Scenario: Project sidebar
- **WHEN** the sidebar of a project page is inspected
- **THEN** its headings ("Endereço", "Território") are level 2

### Requirement: Archives list items as cards
The archive pages for projects, events, resources and any territory or category SHALL show each item as a card with a title linked to its page, the featured image if there is one, context and excerpt, with pagination when there are more items than fit on one page.

#### Scenario: Project archive
- **WHEN** a visitor opens `/projeto/`
- **THEN** they see cards with a linked title, modalidade and territory, and excerpt, without the full content of each project

#### Scenario: Territory archive
- **WHEN** a visitor opens the archive of a territory with projects and events
- **THEN** they see cards of both types, each linking to its page

#### Scenario: Archive title
- **WHEN** a visitor opens `/projeto/` or a territory archive
- **THEN** the hero title is just the archive name (e.g. "Projetos" or the territory name), as text, without prefixes such as "Archives:" or visible HTML tags

#### Scenario: Empty archive
- **WHEN** an archive has no items
- **THEN** the "nothing found" message is shown

### Requirement: Every content page has a hero inside the main region
Project, event and resource pages and all archive pages SHALL show the hero with the page title inside the document's main region.

#### Scenario: Event page
- **WHEN** a visitor opens an event page
- **THEN** they see the hero with the event's title and featured image, inside `<main>`

### Requirement: Comments are not offered on projects
The project page SHALL NOT show a comment form or comment list.

#### Scenario: Project page
- **WHEN** a visitor opens a project page
- **THEN** no comments section appears
