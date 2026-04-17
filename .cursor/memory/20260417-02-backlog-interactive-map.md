# Backlog: Interactive Map

**Project:** Silveira WordPress Classic Theme
**Objective:** Create an interactive Leaflet map featuring custom taxonomy filtering and popup logic for the CPT "Location".
**Date Generated:** 2026-04-17
**Source Solution:** `.cursor/memory/20260417-01-solution-interactive-map.md`

## Summary
- **Total tasks:** 6
- **Completed:** 0
- **Progress:** 0%

*Legend: ⏳ Pending | 🔄 In Progress | ✅ Done | ⚠️ Blocked*

---

## Phase 1: Data Backend Setup

**[1.1]** ⏳ Register CPT and Taxonomy
> **What to do:** Create `inc/cpt-locations.php`, register the `location` post type and `location_category` taxonomy via the `init` action hook according to the WP KI best practices. Include the file in `functions.php`.
> **Date completed:** -
> **Work done:** -

---

## Phase 2: Block Architecture & Data Export

**[2.1]** ⏳ Create ACF Block Structure
> **What to do:** Set up the modular directory `/blocks/interactive-map/`. Add `block.json`, `template.php`, `script.js`, and `style.scss`. Ensure dependencies are configured.
> **Date completed:** -
> **Work done:** -

**[2.2]** ⏳ Build WP_Query & Localize Script
> **What to do:** Inside `template.php`, perform a `WP_Query` fetching all `location` records. Pull ACF lat/lng fields safely and taxonomy structure. Echo a localized JSON object `SilveiraMapData` into the DOM.
> **Date completed:** -
> **Work done:** -

---

## Phase 3: Frontend Map Logic (Leaflet + ES6 + BEM)

**[3.1]** ⏳ Initialize Leaflet Map
> **What to do:** Add Leaflet JS & CSS via `wp_enqueue_script`/`style`. Inside `script.js`, instantiate `L.map` inside the `#interactive-map__canvas` element using OpenStreetMap generic tiles.
> **Date completed:** -
> **Work done:** -

**[3.2]** ⏳ Custom HTML Markers and Popovers
> **What to do:** Iterate through `SilveiraMapData`. Construct custom marker `.map-marker__pin` using `L.divIcon` binding category classes for individual styling. Utilize ES6 template literals to attach popovers to each.
> **Date completed:** -
> **Work done:** -

**[3.3]** ⏳ Category Filtering & BEM Polish
> **What to do:** Render filter buttons `[data-category]` in HTML. In `script.js`, add `click` event listeners to dynamically filter the JS objects and redraw the markers natively. Finalize BEM styling.
> **Date completed:** -
> **Work done:** -

---

## Dependencies & Notes
- Requires user to build ACF Field Group directly in wp-admin and associate it with "Location" after Task [1.1] is complete. 
- Ensure CSS and scripts are only loaded when block is present to obey WP KI.
