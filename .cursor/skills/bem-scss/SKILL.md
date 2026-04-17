---
name: bem-scss
description: Applies Block Element Modifier (BEM) naming and SCSS nesting patterns for maintainable styles. Use when writing or refactoring SCSS/CSS, naming classes, reviewing theme styles, or when the user mentions BEM, blocks, elements, modifiers, or SCSS structure.
---

# BEM in SCSS

## Core rules

| Concept | Pattern | Meaning |
|--------|---------|--------|
| **Block** | `.block-name` | Standalone component (e.g. `.card`, `.site-header`) |
| **Element** | `.block-name__element` | Part of a block, **two underscores** (e.g. `.card__title`) |
| **Modifier** | `.block-name--modifier` or `.block-name__element--modifier` | Variant or state, **two hyphens** (e.g. `.card--featured`, `.btn--disabled`) |

- Use **lowercase** and **hyphens** inside names: `.product-card`, not `.productCard` or `.product_card`.
- **One block per component** in the DOM root of that component; avoid vague blocks like `.wrapper` unless scoped (e.g. `.hero__wrapper` as element).
- **Do not** create chain selectors for hierarchy (no `.card .title`); use `.card__title` instead.

## SCSS: nesting with `&`

Nest **only** to mirror BEM structure and avoid repeating the block name.

**Good**

```scss
.card {
	display: flex;

	&__media {
		flex-shrink: 0;
	}

	&__body {
		padding: 1rem;
	}

	&__title {
		margin: 0;
	}

	&--featured {
		border: 2px solid var(--accent);
	}

	&__title--large {
		font-size: 1.5rem;
	}
}
```

**Avoid**

```scss
// Deep nesting that hides BEM (hard to grep, easy to over-specify)
.card {
	.body {
		.title {
			span {
				// ...
			}
		}
	}
}
```

## Modifiers and states

- **Class modifiers:** `&--modifier` as above.
- **Interactive states** (same element, not a new BEM part): use `&:hover`, `&:focus-visible`, `&.is-active` (optional **state** class with `is-` or `has-` prefix, attached to block or element).

```scss
.accordion__trigger {
	cursor: pointer;

	&:hover {
		color: var(--accent);
	}

	.is-open > & {
		font-weight: 600;
	}
}
```

Prefer **element + state** over new elements when it is the same node: `.menu__link.is-current` is acceptable; keep `is-*` for JS/HTML state hooks.

## Anti-patterns

1. **Grandchild as element chain:** Do not use `.block__elem1__elem2`. Either flatten to a new block or use `.block__elem2` inside `.block` (two separate elements under the same block).
2. **Element without block:** Class `__title` alone is invalid; always include the block: `.card__title`.
3. **Mixing tag selectors with BEM:** Avoid `div.card__inner`; use `.card__inner` only.
4. **ID selectors** for components: avoid `#header`; use `.site-header` as the block.

## File organization (typical)

- One **block** per partial or logical section: `_card.scss`, `_site-header.scss`.
- Use `@use` / `@forward` as per project convention; keep **block** as the top-level selector in that file.

## Quick checklist

- [ ] Class names follow `block`, `block__element`, `block--modifier` / `block__element--modifier`.
- [ ] Nesting depth stays shallow (usually block → element/modifier only).
- [ ] No more than **one** `__` segment per class (no `__foo__bar`).
- [ ] States use `&:` pseudo-classes or optional `is-*` / `has-*` on the same BEM node.

## Optional reference

For a longer rationale and edge cases, see [reference.md](reference.md).
