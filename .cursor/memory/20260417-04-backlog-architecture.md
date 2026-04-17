# Backlog: Site Architecture & Custom Post Types

**Project:** Silveira WordPress Classic Theme
**Objective:** Set up the main Custom Post Types, advanced URL rewriting, and Shared Hierarchical Taxonomies.
**Date Generated:** 2026-04-17
**Source Solution:** `.cursor/memory/20260417-03-solution-architecture.md`

## Summary
- **Total tasks:** 10
- **Completed:** 10
- **Progress:** 100%

*Legend: ⏳ Pending | 🔄 In Progress | ✅ Done | ⚠️ Blocked*

---

## Phase 1: Foundational Models

**[1.1]** ✅ Create Shared Taxonomies
> **What to do:** Create `inc/tax-shared.php` to register `territorio` (Comarca -> Concello). Associate it to `array('projeto', 'evento')`.
> **Date completed:** 2026-04-17
> **Work done:** Created file and registered hierarchical taxonomy 'territorio'.

**[1.2]** ✅ Create CPT Projeto
> **What to do:** Create `inc/cpt-projetos.php` containing the `init` hook to register the `projeto` post type, as well as its exclusive taxonomy `modalidade_projeto`.
> **Date completed:** 2026-04-17
> **Work done:** Created file, registered 'modalidade_projeto' taxonomy and 'projeto' CPT.

**[1.3]** ✅ Create CPT Evento (Agenda)
> **What to do:** Create `inc/cpt-eventos.php` to register the `evento` post type with `rewrite => array('slug' => 'agenda')`.
> **Date completed:** 2026-04-17
> **Work done:** Created file and registered 'evento' CPT with custom slug.

---

## Phase 2: Complex Custom Post Type (Recursos)

**[2.1]** ✅ Create CPT Recurso & Category
> **What to do:** Create `inc/cpt-recursos.php`. Register taxonomy `categoria_recurso` and CPT `recurso`. Set CPT rewrite `'slug' => 'recursos/%categoria_recurso%'`.
> **Date completed:** 2026-04-17
> **Work done:** Set up CPT 'recurso' and taxonomy 'categoria_recurso' using dynamic rewrite slug.

**[2.2]** ✅ Implement Rewrite Dynamic Filter
> **What to do:** Inside `inc/cpt-recursos.php`, add `post_type_link` filter so `%categoria_recurso%` is correctly substituted by the term object's slug.
> **Date completed:** 2026-04-17
> **Work done:** Added filter `silveira_recurso_post_link` returning taxonomy slug with fallback to 'geral'.

---

## Phase 3: Global Integration

**[3.1]** ✅ Include and Flush Rules
> **What to do:** Require all `inc/*.php` files inside `functions.php`. Setup temporary flush rewrite rules logic for URL routing to work.
> **Date completed:** 2026-04-17
> **Work done:** Added require_once calls to functions.php and implemented silveira_rewrite_flush logic.

---

## Phase 4: Base Template Hierarchy

**[4.1]** ✅ Projeto Templates (Archive/Single)
> **What to do:** Create `archive-projeto.php` and `single-projeto.php` with a basic loop structure using BEM classes and Google Fonts as per design guidelines.
> **Date completed:** 2026-04-17
> **Work done:** Created archive and single templates for projects using get_template_part.

**[4.2]** ✅ Evento Templates (Archive/Single)
> **What to do:** Create `archive-evento.php` and `single-evento.php` for the Agenda views.
> **Date completed:** 2026-04-17
> **Work done:** Created archive and single templates for events.

**[4.3]** ✅ Recurso Templates (Archive/Single)
> **What to do:** Create `archive-recurso.php` and `single-recurso.php`.
> **Date completed:** 2026-04-17
> **Work done:** Created archive and single templates for resources.

**[4.4]** ✅ Taxonomy & Partials Base
> **What to do:** Create `taxonomy-territorio.php` and base `template-parts/content.php` logic to serve as a modular foundation for content rendering.
> **Date completed:** 2026-04-17
> **Work done:** Created taxonomy template and modular content/none template parts following BEM.
