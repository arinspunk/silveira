# Solution: Map UX (Interactive Scroll & Sticky Filters)

## 1. Goal
Improve the UX on the front page map so that on load, the map takes up the remaining space down to the bottom of the screen but is non-interactive. When the user scrolls down, automatically and smoothly scroll the page so the filter bar docks under the sticky header, the map fills the remaining viewport, and the map becomes interactive.

## 2. Approach (KISS)

### Phase 1: Styling Updates
- Update `.c-map` height. Since the filter bar will be sticky and the header is sticky, the final height will be `100vh - (header_height + filter_bar_height)`.
- Make `.c-filter-bar` `position: sticky; top: 50px; z-index: 10;`.
- Add a `.is-locked` modifier to `.c-map` or use Leaflet options to disable map interactions (dragging, zooming) initially. CSS `pointer-events: none;` on a wrapper is easiest to block all interactions until scrolled.

### Phase 2: JS Scroll Logic
- In `src/js/map.js` (or a dedicated `scroll.js`), listen for scroll events.
- If scroll position > small threshold and currently in "top" state:
  - smoothly `window.scrollTo` to the vertical position where `.c-filter-bar` sticks (which is `Hero height`).
  - enable map `pointer-events: auto;`.
- If scroll position < threshold when map is active:
  - smooth scroll back to top.
  - disable map `pointer-events`.
- Alternatively, we can use IntersectionObserver to simplify the trigger instead of pure scroll listeners.

## 3. Risks & Edge Cases
- Mobile height (`100vh`) issues with browser bars. `svh` or `dvh` units can be used.
- "Smooth scroll" hijacking can sometimes feel annoying if not tuned right. We will stick to a snappy `scrollTo({top: target, behavior: 'smooth'})`.

## 4. Next Step
Wait for user approval, then create the backlog and execute.
