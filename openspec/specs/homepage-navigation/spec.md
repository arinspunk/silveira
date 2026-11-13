# homepage-navigation Specification

## Purpose

Defines the front-page interaction with three states (collapsed with hero, expanded map and revealed footer) and how to move between them with mouse, touch and keyboard, accessibly.

## Requirements

### Requirement: Map expands from pointer, touch and keyboard
From the collapsed state, the system SHALL switch to the expanded map when the visitor scrolls down with the wheel or a gesture, clicks a filter or a marker, focus enters the filters or the map, or `PageDown`, `Space` or the down arrow is pressed outside a form field.

#### Scenario: Keyboard user tabs into filters
- **WHEN** with the front page collapsed a keyboard user tabs to the first filter
- **THEN** the map expands and the focused filter remains visible and focused

#### Scenario: PageDown
- **WHEN** with the front page collapsed and focus on the document `PageDown` is pressed
- **THEN** the map expands

#### Scenario: Wheel (existing behaviour)
- **WHEN** with the front page collapsed the wheel is turned down
- **THEN** the map expands

### Requirement: Map collapses back to the hero
From the expanded map, the system SHALL return to the collapsed state when the visitor scrolls up with the wheel or a gesture over the filter bar or the header, or presses `PageUp` or `Home` outside a form field and outside the map.

#### Scenario: Home key
- **WHEN** with the map expanded and focus on a filter `Home` is pressed
- **THEN** the front page returns to the collapsed state

### Requirement: Footer is reachable by every input method
From the expanded map, the system SHALL reveal the footer when the mouse moves over its visible area, when the visible strip of the footer is tapped or swiped up on touch devices, when focus enters any of its elements, or when `End` is pressed.

#### Scenario: Touch device
- **WHEN** on a phone with the map expanded the visible strip of the footer is tapped
- **THEN** the whole footer is revealed and its links can be tapped

#### Scenario: Keyboard focus enters footer
- **WHEN** a keyboard user tabs from the last element of the map to the first footer link
- **THEN** the footer is revealed and the focused link is visible

#### Scenario: Footer closes
- **WHEN** the footer is revealed and focus leaves it upwards, `PageUp` is pressed, or the visitor scrolls up with the wheel or a gesture
- **THEN** the footer is hidden and the front page returns to the expanded map

### Requirement: Focused elements are always visible
The system SHALL ensure that the focused element is never outside the visible area or covered by the current state of the front page.

#### Scenario: Shift+Tab from map to header
- **WHEN** with the map expanded the user tabs backwards to a header link
- **THEN** the link is visible and focused

### Requirement: Hidden project list is not focusable
When the project list panel is closed, its elements SHALL NOT be able to receive focus or be announced by screen readers; the button that opens it SHALL expose whether the panel is open or closed.

#### Scenario: Tabbing with closed panel
- **WHEN** the list panel is closed and the user tabs through the front page
- **THEN** focus never enters elements of the list

#### Scenario: Toggle state announced
- **WHEN** a screen reader focuses the list button after opening the list
- **THEN** it announces that it is expanded

### Requirement: Reduced motion
If the user has the reduced motion preference enabled, changes between states SHALL be immediate and the front page SHALL accept the next action without a waiting period.

#### Scenario: Rapid keyboard navigation with reduced motion
- **WHEN** with reduced motion enabled `PageDown` and then `End` are pressed
- **THEN** the map expands and the footer is revealed immediately, without ignoring the second key

### Requirement: Works without JavaScript
If the front page's JavaScript does not load, all of the front page's content (hero, filters, map and footer) SHALL be reachable with the browser's native scrolling.

#### Scenario: Script blocked
- **WHEN** the front page is loaded with JavaScript disabled
- **THEN** the page can be scrolled down to the footer and its links work
