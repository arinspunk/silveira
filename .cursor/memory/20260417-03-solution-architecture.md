# Solution: Site Architecture & Custom Post Types

**Goal:** Establish the foundational architecture for the WP Classic Theme based on the "A Silveira Sitemap v02", defining which entities will be Custom Post Types, Taxonomies, and standard Pages, including their rewrite rules (URL slugs).

## Analysis of the Sitemap

Based on the boxes and the stacked icons (which represent multiple records/entries), we can deduce the following entity models:

### 1. Custom Post Types (CPTs)
- **Projeto (Projects)**
  - **URL Base:** `/projeto` (Archive)
  - **Single Slug:** `/projeto/%postname%`
  - **WP Registration:** CPT `projeto`.
- **Evento (Events)**
  - **URL Base:** `/agenda` (Archive)
  - **Single Slug:** `/agenda/%postname%`
  - **WP Registration:** CPT `evento` with `rewrite => ['slug' => 'agenda']`.
- **Recurso (Resources)**
  - **URL Base:** `/recursos` (Archive)
  - **Taxonomy Structure:** Needs a custom taxonomy (e.g., `categoria_recurso`) to handle the sub-levels: "Famílias", "Docentes", "Agentes".
  - **Single Slug:** `/recursos/%categoria_recurso%/%postname%` (e.g., `/recursos/familias/exemplo`).
  - **WP Registration:** CPT `recurso` with advanced rewrite rules, plus taxonomy `categoria_recurso`.

### 2. Standard Pages (Hierarchical)
The following don't seem to require CPTs and can be managed natively via WP Pages (with Parent -> Child relationships) to generate their URLs:
- **Início** (`/`)
- **Nós** (`/nos`)
  - **Nota imprensa** (`/nos/nota-imprensa`) - *Unless press releases are many, in which case it should be a CPT, but the diagram doesn't show it stacked.*
- **Contato** (`/contato`)
  - **Parte da rede** (`/contato/parte-rede`)
  - **Publicar evento** (`/contato/publicar-evento`)
  - **Sugestons** (`/contato/sugestons`)

## Implementation Proposed (KISS)
1. Delete the "interactive map" standalone backlog and integrate it into the global architecture (e.g., the map will display "Projetos" or whatever CPT you specify).
2. Create modular files in `/inc/` for each CPT:
   - `inc/cpt-projeto.php`
   - `inc/cpt-evento.php`
   - `inc/cpt-recurso.php` (contains both CPT and Taxonomy, plus the rewrite rule filter for `%categoria_recurso%`).
3. Load them in `functions.php`.

## Trade-offs
- Setting up `/recursos/%categoria_recurso%/%postname%` requires a `post_type_link` filter in WP and manual flush of rewrite rules. It's standard but slightly more complex than the default `/recursos/%postname%`.
