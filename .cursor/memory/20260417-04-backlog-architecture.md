# Backlog: Site Architecture & Custom Post Types

**Project:** Silveira WordPress Classic Theme
**Objective:** Set up the main Custom Post Types and their URL rewrite structures.
**Date Generated:** 2026-04-17
**Source Solution:** `.cursor/memory/20260417-03-solution-architecture.md`

## Summary
- **Total tasks:** 5
- **Completed:** 0
- **Progress:** 0%

*Legend: ⏳ Pending | 🔄 In Progress | ✅ Done | ⚠️ Blocked*

---

## Phase 1: Basic Custom Post Types

**[1.1]** ⏳ Create CPT Projeto
> **What to do:** Create `inc/cpt-projetos.php` containing the `init` hook to register the `projeto` post type. Ensure standard supports (title, editor, thumbnail).
> **Date completed:** -
> **Work done:** -

**[1.2]** ⏳ Create CPT Evento (Agenda)
> **What to do:** Create `inc/cpt-eventos.php` to register the `evento` post type. Important: define the rewrite rule as `'rewrite' => array('slug' => 'agenda')` to match the sitemap exactly.
> **Date completed:** -
> **Work done:** -

---

## Phase 2: Complex Custom Post Type (Recursos)

**[2.1]** ⏳ Create CPT Recurso & Taxonomy
> **What to do:** Create `inc/cpt-recursos.php`. Register the taxonomy `categoria_recurso` and the CPT `recurso`. Set the rewrite rule for the CPT to `'slug' => 'recursos/%categoria_recurso%'`.
> **Date completed:** -
> **Work done:** -

**[2.2]** ⏳ Implement Rewrite Dynamic Filter
> **What to do:** In `inc/cpt-recursos.php`, add a filter to `post_type_link` that dynamically replaces `%categoria_recurso%` in the generated URL with the actual selected taxonomy term for that post.
> **Date completed:** -
> **Work done:** -

---

## Phase 3: Global Integration

**[3.1]** ⏳ Include and Flush Rules
> **What to do:** In the main `functions.php`, add `require_once` statements for the three new CPT files. Add a temporary hook to `flush_rewrite_rules()` or prompt the manual flush to make the advanced taxonomy URL changes take effect immediately in the database.
> **Date completed:** -
> **Work done:** -
