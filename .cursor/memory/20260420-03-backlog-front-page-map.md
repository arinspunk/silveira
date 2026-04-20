# Backlog: Front Page & Interactive Map Integration

**Project:** Silveira WordPress Classic Theme
**Objective:** Implement `front-page.php` as the main site entrance featuring the interactive map, custom filters, and full-screen layout.
**Date Generated:** 2026-04-20
**Source Solution:** Integrates [20260417-02-backlog-interactive-map.md](file:///Users/xulio/Desktop/2023/silveira/.cursor/memory/20260417-02-backlog-interactive-map.md) into a standalone template.

## Summary
- **Total tasks:** 7
- **Completed:** 7
- **Progress:** 100%
- **Last Commit:** `3a8e0bc`

*Legend: ⏳ Pending | 🔄 In Progress | ✅ Done | ⚠️ Blocked*

---

## Phase 1: Structure & Shell
**[1.1]** ✅ Create `front-page.php`
> **Date completed:** 2026-04-20
> **Details:** Implemented with `silveira_hero()` and `.c-filter-bar`.

**[1.2]** ✅ SCSS Foundation: `_map.scss`
> **Date completed:** 2026-04-20
> **Details:** Defined heights and Leaflet popup styling.

**[1.3]** ✅ Automated Geocoding (Nominatim API)
> **Date completed:** 2026-04-20
> **Details:** Robust logic with address cleaning and Galician context.

---

## Phase 2: Map Infrastructure (Leaflet)
**[2.1]** ✅ Asset Enqueueing (Leaflet)
> **Date completed:** 2026-04-20
> **Details:** Registered and enqueued conditionally in `functions.php`.

**[2.2]** ✅ Map Container & Filter Bar Integration
> **Date completed:** 2026-04-20
> **Details:** Integrated taxonomy-based checkboxes for real-time filtering.

---

## Phase 3: Logic & Data
**[3.1]** ✅ Data Localization (PHP to JS)
> **Date completed:** 2026-04-20
> **Details:** Points passed via `silveiraMapPoints` global object.

**[3.2]** ✅ Map Initialization (Javascript)
> **Date completed:** 2026-04-20
> **Details:** Leaflet initialization and real-time category filtering.

---

---

## Technical Notes
- Follows BEM nomenclature: `.c-map`, `.c-map__canvas`, `.c-map__filters`.
- Integrates with ITCSS architecture.
- Replaces the "ACF Block" approach from previous backlog with a direct "Front Page Template" approach for better control of the global layout.
