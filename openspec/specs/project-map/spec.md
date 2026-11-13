# project-map Specification

## Purpose

Interactive map on the front page that shows the published projects that have coordinates, with a popup per marker and a side list, presenting their data safely and faithfully to the edited content.

## Requirements

### Requirement: Project content is displayed as text
The map SHALL show each project's title, lema, excerpt and context text (modalidade/territory) as plain text in the popup and in the side list: any HTML markup in those fields is removed and never interpreted.

#### Scenario: Title containing HTML markup
- **WHEN** a published project has the title `<img src=x onerror=alert(1)>Horta`
- **THEN** the popup and the list show `Horta` (without the tag), no `<img>` element is created from it and no script runs

#### Scenario: Lema containing a script terminator
- **WHEN** a project's lema contains `</script><script>alert(1)</script>`
- **THEN** the front page loads without errors, the map shows every project with coordinates, no script runs and the lema is shown without the tags (empty if it only contained `<script>`)

### Requirement: Typographic characters render correctly
The map SHALL show apostrophes, quotes, ampersands and accented characters as the reader sees them on the project page, never as visible HTML entities.

#### Scenario: Title with apostrophe and ampersand
- **WHEN** a project is titled `A Horta d'Ana & Cía`
- **THEN** the popup and the list show `A Horta d’Ana & Cía` (or with a straight apostrophe), without sequences such as `&#8217;` or `&amp;`

### Requirement: Only safe URLs are used for links and images
The "Ver projeto" links and the list items SHALL point only to `http` or `https` URLs, and the popup image SHALL be loaded only from `http` or `https` URLs; if a URL does not comply, the link or image is omitted.

#### Scenario: Valid permalink and thumbnail
- **WHEN** a project has a normal permalink and featured image
- **THEN** the popup shows the image and the "Ver projeto" link leads to the project page

#### Scenario: Unsafe URL scheme
- **WHEN** a point's URL starts with `javascript:` or `data:`
- **THEN** no link or image is rendered with that URL

#### Scenario: Project without featured image
- **WHEN** a project has no featured image
- **THEN** the popup is shown without an image block and without broken images

### Requirement: Image alternative text
The popup image SHALL have the project title, as plain text, as its alternative text.

#### Scenario: Alt text with special characters
- **WHEN** the project title contains double quotes
- **THEN** the image's `alt` attribute contains the full title without breaking the attribute or adding extra attributes

### Requirement: Selecting a marker expands the map
When a marker is clicked while the map is collapsed, the system SHALL expand the map and open the project's popup, also in the production build.

#### Scenario: Marker click in production build
- **WHEN** with compiled assets (no Vite dev server) a visitor clicks a marker while the map is collapsed
- **THEN** the map becomes expanded, the "Ver a lista" button appears and the project's popup is shown
