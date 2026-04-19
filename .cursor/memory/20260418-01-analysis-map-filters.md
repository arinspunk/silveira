# Analysis: Map Filters Component (Figma)

**Date**: 2026-04-18
**Context**: Analyzing the "Filtros" component selected in Figma (node `500:2379`) to prepare for the Interactive Map implementation (detailed in `20260417-02-backlog-interactive-map.md`).

## 1. Structure & Layout
The component represents a horizontal filter bar containing multiple custom select/dropdown inputs.

- **Main Container (`.c-filter-bar`)**:
  - Background color: `#ede5ff` (Matches a soft violet, possibly `violeta-050` or a new token).
  - Border: 1px solid `#9566ff` (Mid-tone violet).
  - Height: 70px.
  - Layout: Likely Flexbox, aligning items centrally.

- **Select Inputs (`.c-select`)**:
  - Dimensions: ~213px wide, 50px high.
  - Background: `#ffffff` (White).
  - Border: 1px solid `#2f0099` (Dark Violet, matches `violeta-800` or `700`).
  - Border-radius: 5px.
  - Content: 
    - Small Label (`.c-select__label`): Top-aligned, 12px Isaac, color `#200066`
    - Placeholder/Value (`.c-select__value`): 14px Isaac, color `#7233ff`

## 2. Dropdown & Checkboxes (Open State)
The component includes the open state of a dropdown (`.c-dropdown`), featuring a list of checkboxes.

- **Dropdown Menu (`.c-dropdown__menu`)**:
  - Background: `#ffffff`.
  - Border: 1px solid `#2f0099`.
  - Border-radius: 5px.
- **Checkbox Items (`.c-checkbox`)**:
  - Height: 40px (Generous touch target).
  - Typography: 16px Isaac, color `#200066`.
  - **Unselected State**: 18x18px box with 2px border-radius, stroke `#3f00cc`.
  - **Selected State**: Filled with `#3f00cc`, inner checkmark icon.

## 3. Integration with Interactive Map Backlog
This component is paramount for Task **[3.3] Category Filtering & BEM Polish** of the Interactive Map backlog.

**Architecture Proposal**:
Instead of attaching these styles directly block-specific, we should create reusable SCSS components in Phase 3 of our CSS system:
- `6-components/_forms.scss`: to handle `.c-select` and `.c-checkbox`.
- `6-components/_filter-bar.scss`: for the `.c-filter-bar` layout.

This allows the filters to be used both in the Interactive Map and in standard archive pages later on.

## 4. Next Steps
When we are ready to implement:
1. Verify exact Figma colors against our `_colors.scss` tokens.
2. Build the `.c-select` and `.c-checkbox` markup in the `template-kitchen-sink.php` for isolation.
3. Write the SCSS, ensuring hover and focus states are handled for accessibility.
