# Backlog: Hero Component

**Project:** Silveira WordPress Classic Theme
**Objective:** Implement a versatile Hero component with a 2-column layout (6+6) for site-wide use.
**Date Generated:** 2026-04-20
**Source Solution:** (Based on Figma node 308:392)

## Summary
- **Total tasks:** 6
- **Completed:** 6
- **Progress:** 100%

*Legend: ⏳ Pending | 🔄 In Progress | ✅ Done | ⚠️ Blocked*

---

## Phase 1: Tokens & Settings
**[1.1]** ✅ Update Typography Scale
> **What to do:** Add `$font-size-xxl: 4rem; // 64px` to `_typography.scss` and map it to `--sil-fs-xxl` in `_tokens.scss`.

---

## Phase 2: SCSS Styling
**[2.1]** ✅ Create Component: `6-components/_hero.scss`
> **What to do:** Define styles for `.c-hero`, its inner row, and specific groups.
> **Specs:** 230px fixed height, background white, title 64px `$verde-800`, subtitle 20px `$verde-700`.

**[2.2]** ✅ Handle Hero Image Alignment
> **What to do:** Ensure the image in the right column is centered horizontally and "stuck to the base" (aligned bottom) of the hero block.

**[2.3]** ✅ Import in `main.scss`
> **What to do:** Register the new hero component.

---

## Phase 3: Template Integration
**[3.1]** ✅ Create Reusable Template Part
> **What to do:** Implement `template-parts/content-hero.php` to handle dynamic title, subtitle, and image using basic variables or ACF (if needed).

---

## Phase 4: Implementation & QA
**[4.1]** ✅ Update Page Templates
> **What to do:** Integrate the hero component into `page.php` or a global header logic to ensure it appears on nearly all pages.
