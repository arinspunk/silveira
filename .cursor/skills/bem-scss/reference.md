# BEM + SCSS — extra notes

## Why avoid deep nesting

Deep nesting increases specificity and makes overrides harder. BEM class names on each node keep specificity flat (mostly single classes).

## When to split a new block

Create a new **block** when:

- The chunk is reusable elsewhere (e.g. `.btn` inside `.hero` is still `.btn`).
- It has its own lifecycle or could appear in isolation.

Keep a single **block** when parts are only ever used together as one widget.

## Sass modules

With `@use 'card'`, local variables stay private; BEM class strings remain unchanged in output CSS.

## Relationship to ITCSS / layers

BEM names components; ITCSS orders **layers** (settings, tools, generic, elements, objects, components, utilities). BEM usually lives in **components** (and sometimes **objects**). Do not mix utility-only classes (e.g. `.u-hidden`) with BEM blocks in the same rule set without a clear convention.
