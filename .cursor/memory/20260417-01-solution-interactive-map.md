# Solution: Interactive Map with CPT & Custom HTML Markers

**Goal:** Create a lightweight, interactive map for the homepage displaying custom post types ("Locations") using OpenStreetMap/Leaflet. The map will feature custom HTML markers (`divIcon`), popovers with post information, and frontend filtering by taxonomy. ACF fields (lat/long) will be managed via WP Admin by the user.

## Proposed Approach (KISS)
Since the total number of locations is <250, the simplest and most performant approach is to pass the data directly to the frontend on page load. A REST API is overkill. We will parse the WP Post data, send it to a localized JS object, and let Vanilla ES6 Leaflet handle rendering and filtering instantly on the client side.

## Implementation Steps

### 1. WordPress Backend Structure
- **CPT & Taxonomy:** Register a `location` post type and a `location_category` taxonomy via `init` hook in `inc/cpt-locations.php`.
- **Data Entry:** User will manually create ACF fields (`latitude`, `longitude`) assigned to the `location` CPT via the WP Admin interface.

### 2. Block Registration (Backend)
- Centralize the logic in `blocks/interactive-map/`.
- Use `block.json` to register the block.
- In `template.php`, execute a single `WP_Query` fetching all `location` posts.
- Extract title, excerpt, taxonomy terms, latitude, and longitude.
- Convert to a clean JSON array and pass it to the frontend via `<script>` directly or `wp_localize_script() / wp_add_inline_script()` attached to `SilveiraMapData`.

### 3. Frontend Map Logic
- **Initialization:** Load Leaflet.js conditionally. Initialize the map inside the `#interactive-map` container.
- **Custom HTML Markers:** Loop through `SilveiraMapData.locations`. Use Leaflet's `L.divIcon` to render markers as raw HTML (e.g., `<div class="marker marker--category-slug"></div>`). This enables SCSS state styling (color changes per category).
- **Popovers:** Create popups dynamically combining HTML string templates and binding them to the markers `bindPopup()`.
- **Filtering System:** Buttons populated with data attributes (`data-filter="category-slug"`) next to the map. Clicking a button runs `.filter()` on the JS array, clears the map layer, and redraws the matching markers instantly.

## Trade-offs
- **Performance vs Scalability:** Sending ~250 points in JSON embedded in the HTML adds around 10-20KB to the initial payload. This is vastly superior speed-wise to AJAX fetching after render. It scales poorly *only* if points grow to >1000, which we established won't happen.
- **Frontend filtering vs Backend filtering:** Filtering in JS means no server requests, resulting in instant UX. The trade-off is the initial payload is absolute (all data loaded at once).
- **Custom UI creation:** Relying on `L.divIcon` means we must manually position the anchor points of our custom CSS pins otherwise they might slightly offset when zooming.
