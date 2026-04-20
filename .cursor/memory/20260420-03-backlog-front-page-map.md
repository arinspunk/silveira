# Backlog: Front Page & Interactive Map Integration

**Project:** Silveira WordPress Classic Theme
**Objective:** Implement `front-page.php` as the main site entrance featuring the interactive map, custom filters, and full-screen layout.
**Date Generated:** 2026-04-20
**Source Solution:** Integrates [20260417-02-backlog-interactive-map.md](file:///Users/xulio/Desktop/2023/silveira/.cursor/memory/20260417-02-backlog-interactive-map.md) into a standalone template.

## Summary
- **Total tasks:** 6
- **Completed:** 0
- **Progress:** 0%

*Legend: ⏳ Pending | 🔄 In Progress | ✅ Done | ⚠️ Blocked*

---

## Phase 1: Structure & Shell
**[1.1]** ⏳ Create `front-page.php`
> **What to do:** Initialize the file with `get_header()`, a `<main>` container for the map, and `get_footer()`.
> **Note:** This template will not use the standard `silveira_hero()` to allow the map to be the focus.

**[1.2]** ⏳ SCSS Foundation: `_map.scss`
> **What to do:** Create `src/scss/6-components/_map.scss` and register it in `main.scss`.
> **Specs:** Define `.c-map` container with flexible height (ideally `calc(100vh - header_height)`) for a full-screen experience.

**[1.3]** ⏳ Automated Geocoding (Nominatim API)
> **What to do:** Implement a `save_post` hook in `functions.php` to detect when the `address` field is updated. Call the Nominatim API to fetch coordinates and automatically populate the `lat` and `lng` fields.


---

## Phase 2: Map Infrastructure (Leaflet)
**[2.1]** ⏳ Asset Enqueueing (Leaflet)
> **What to do:** Modify `inc/assets.php` to enqueue Leaflet CSS/JS. Ensure it only loads on the front page or when needed.

**[2.2]** ⏳ Map Container & Filter Bar Integration
> **What to do:** Implement the HTML structure for `#map-canvas` in `front-page.php`. Integrate the already developed `.c-filter-bar` component into the top/bottom section of the map view.
> **Status:** HTML/CSS of `.c-filter-bar` is already available in `_filter-bar.scss`.


---

## Phase 3: Logic & Data
**[3.1]** ⏳ Data Localization (PHP to JS)
> **What to do:** Use `wp_localize_script` to pass project/event locations from WordPress to the map script.

**[3.2]** ⏳ Map Initialization (Javascript)
> **What to do:** Create `src/js/components/map.js`, initialize Leaflet, and render the first set of markers.

---

## Technical Notes
- Follows BEM nomenclature: `.c-map`, `.c-map__canvas`, `.c-map__filters`.
- Integrates with ITCSS architecture.
- Replaces the "ACF Block" approach from previous backlog with a direct "Front Page Template" approach for better control of the global layout.
