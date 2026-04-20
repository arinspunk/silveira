# Backlog: Map UX Improvements (Scroll & Sticky Filters)

**Project:** Silveira WordPress Classic Theme
**Objective:** Create a seamless landing experience where the map fills the bottom screen un-interactively, and upon scroll, slides up locking the filters under the header and activating the map.
**Date Generated:** 2026-04-20
**Source Solution:** [20260420-04-solution-map-ux.md](file:///Users/xulio/Desktop/2023/silveira/.cursor/memory/20260420-04-solution-map-ux.md)

## Summary
- **Total tasks:** 2
- **Completed:** 2
- **Progress:** 100%

*Legend: ⏳ Pending | 🔄 In Progress | ✅ Done | ⚠️ Blocked*

---

## Phase 1: CSS & Styling Adjustments
**[1.1]** ✅ Update `_filter-bar.scss` & `_map.scss`
> **What to do:**
> - Make `.c-filter-bar` `position: sticky; top: 50px; z-index: 10;`.
> - Add a modifier class (e.g., `.is-locked`) to `.c-map` which sets `pointer-events: none;`.

---

## Phase 2: JavaScript Logic
**[2.1]** ✅ Implement Scroll Control in `map.js`
> **What to do:**
> - Listen to the scroll event.
> - When user starts scrolling down from top (Y > 0), `window.scrollTo` to align the top of `.c-filter-bar` with the bottom of the header (top: 50px).
> - Enable pointer events on `.c-map` once docked.
> - Allow scrolling back up smoothly.

