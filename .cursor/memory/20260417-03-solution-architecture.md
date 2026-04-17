# Solution: Site Architecture & Custom Post Types

**Goal:** Establish the foundational architecture for the WP Classic Theme based on the "A Silveira Sitemap v02", defining which entities will be Custom Post Types, Taxonomies, and standard Pages. Features advanced shared taxonomies for mapping and filtering.

## Analysis of the Sitemap & Data

### 1. Shared Taxonomies (for filtering and mapping)
Since locations (Comarca -> Concello) and categorization (Modalidade) apply to multiple models, they will be registered independently and associated with arrays of post types:
- **Territorio (`territorio`)**: Taxonomía jerárquica para reflejar Comarca (Parent) -> Localidade/Concello (Child). Ej: `Terra Chá` -> `Vilalba`. Vinculada a `projeto` y `evento`.
- **Modalidade (`modalidade`)**: Taxonomía jerárquica (Formal, Nom formal, Informal). Vinculada a `projeto` (y posiblemente a `evento` a futuro).

### 2. Custom Post Types (CPTs)
- **Projeto (Projects)**
  - **Archive:** `/projeto` | **Single:** `/projeto/%postname%`
  - Usa taxonomías: `territorio`, `modalidade`.
- **Evento (Events)**
  - **Archive:** `/agenda` | **Single:** `/agenda/%postname%`
  - **Rewrite:** `'slug' => 'agenda'`
  - Usa taxonomías: `territorio`.
- **Recurso (Resources)**
  - **Taxonomy:** `categoria_recurso` (Famílias, Docentes, Agentes)
  - **Single Slug:** `/recursos/%categoria_recurso%/%postname%`
  - Requisito de filtro en `post_type_link` avanzado.

### 3. Template Hierarchy Strategy
To keep the theme maintainable and modular (KISS), we will follow the standard hierarchy but abstract the loop logic:
- **Archives:** `archive-{post_type}.php` (projeto, evento, recurso).
- **Singles:** `single-{post_type}.php` (projeto, evento, recurso).
- **Taxonomies:** `taxonomy-{taxonomy}.php` (territorio, categoria_recurso, modalidade_projeto).
- **Partials:** Use `template-parts/content-{post_type}.php` for the loop items to separate "The Loop" logic from the container structure.

### 4. Standard Pages
Se usarán sub-páginas nativas de WP para URLs estáticas como:
- **Início** (`/`)
- **Nós** (`/nos`) -> **Nota imprensa** (`/nos/nota-imprensa`)
- **Contato** (`/contato`) -> **Parte da rede** (`/contato/parte-rede`), **Publicar evento** (`/contato/publicar-evento`), **Sugestons** (`/contato/sugestons`)

## Implementation Proposed (KISS)
1. Modulizar cada tipo de dato por separado en la carpeta `/inc/`.
2. Crear los archivos de plantilla base en la raíz del tema.
3. Archivos a crear (Lógica):
   - `inc/tax-shared.php`, `inc/cpt-projetos.php`, `inc/cpt-eventos.php`, `inc/cpt-recursos.php`.
4. Incluir todos los archivos desde el `functions.php` central.
