# external-resources Specification

## Purpose

Defines which third-party resources the browser may request when visiting the site and ensures that the site's own resources (icons, typefaces, libraries) are served from the site itself.

## Requirements

### Requirement: Icons and typefaces are served from the site
The theme's icon font and typefaces SHALL be served from the site's domain; the browser SHALL NOT request them from third-party font services.

#### Scenario: Any page
- **WHEN** any public page is loaded with the browser's network tools open
- **THEN** there are no requests to `fonts.googleapis.com` or `fonts.gstatic.com`, and no `preconnect` to those domains in the HTML

#### Scenario: Icons render
- **WHEN** a page with icons (arrows, `location_on`, `expand_more`) is loaded
- **THEN** the icons are shown as glyphs, not as the text of their name

### Requirement: Design typefaces are applied
The site's text SHALL be shown in the design typeface, Isaac, with its real bold and italic styles, showing text in the fallback font while it downloads.

#### Scenario: Body text
- **WHEN** a page is loaded and the rendered font of a paragraph is inspected
- **THEN** the font used is Isaac

#### Scenario: Emphasis
- **WHEN** the rendered font of a project page's lema (italic) and of a modalidade tag (bold) is inspected
- **THEN** the Isaac Italic and Isaac Bold files are used respectively, not styles synthesized by the browser

### Requirement: JavaScript libraries are served from the site
The JavaScript and CSS libraries used by the theme SHALL be served from the site's domain; the browser SHALL NOT request them from third-party CDNs.

#### Scenario: Any page
- **WHEN** any public page is loaded with the browser's network tools open
- **THEN** there are no requests to `unpkg.com` or other library CDNs

### Requirement: Map library loads only where there is a map
The map library's code and styles SHALL be loaded only on pages that contain a map.

#### Scenario: Page without map
- **WHEN** a project page is loaded
- **THEN** neither the map library's code nor its stylesheet is downloaded

#### Scenario: Front page
- **WHEN** the front page is loaded
- **THEN** the map is shown with its controls and markers as before

### Requirement: Map tiles are attributed and configurable
The map SHALL show the attribution required by the tile provider, linked to its license page, and the provider SHALL be changeable through site configuration without modifying the JavaScript.

#### Scenario: Default provider
- **WHEN** the front page is loaded without additional configuration
- **THEN** the map uses OpenStreetMap tiles and shows "© OpenStreetMap contributors" linked to the OpenStreetMap copyright page

#### Scenario: Alternative provider
- **WHEN** another provider is configured with its tile URL and attribution
- **THEN** the map requests the tiles from that provider and shows its attribution
