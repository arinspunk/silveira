# theme-dependencies Specification

## Purpose

Defines how the site behaves when a plugin the theme depends on (ACF) is missing, so that it never produces a fatal error and warns whoever administers the site.

## Requirements

### Requirement: Public site works without ACF
When ACF is not active, every public page SHALL render without fatal errors, omitting only the information that comes from ACF fields.

#### Scenario: Front page without ACF
- **WHEN** ACF is deactivated and a visitor opens the front page
- **THEN** the page loads with hero, filters and map, and the map shows no markers

#### Scenario: Project page without ACF
- **WHEN** ACF is deactivated and a visitor opens a project page
- **THEN** the page shows title, content and territories, without lema or address, and without errors

### Requirement: Administrators are warned when ACF is missing
When ACF is not active, the admin panel SHALL show a notice to users who can manage plugins saying that the project fields are not available.

#### Scenario: Admin notice
- **WHEN** ACF is deactivated and an administrator opens the dashboard
- **THEN** they see a notice naming ACF as a required plugin

#### Scenario: No notice for editors
- **WHEN** ACF is deactivated and a user without the `activate_plugins` capability logs in
- **THEN** the notice is not shown
