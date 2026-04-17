---
name: javascript-es6
description: Applies modern JavaScript (ES2015+) patterns—modules, const/let, arrows, async/await, destructuring—for browser and Node tooling. Use when writing or refactoring JS, Vite/Webpack configs, theme src/, lint rules, or when the user mentions ES6, ES modules, async, or modern JavaScript.
---

# JavaScript (ES2015+)

## Prefer by default

| Topic | Preference |
|-------|------------|
| Bindings | `const` first; `let` when reassigned; avoid `var` |
| Functions | Arrow functions for callbacks and short lambdas; named `function` when hoisting/`this` binding matters |
| Async | `async`/`await` over raw `.then()` chains for linear flow |
| Modules | `import` / `export` (ESM); match project `"type": "module"` or bundler rules |
| Equality | Strict `===` / `!==` unless coercion is intentional |

## Modules

```javascript
// Named exports
export function parseId(s) {
	return String(s).trim();
}
export const DEFAULT_LIMIT = 20;

// Default + named
export default function createStore() {}
```

```javascript
import createStore, { parseId, DEFAULT_LIMIT } from './store.js';
import * as util from './util.js';
```

- Use **explicit `.js` extensions** when the runtime or bundler requires it (Node ESM, some Vite paths).
- Side-effect imports: `import './styles.css';`

## Destructuring and rest/spread

```javascript
const { name, id = 'anon' } = user;
const [first, ...rest] = items;
const merged = { ...defaults, ...options };
const combined = [...a, ...b];
```

## Template literals

```javascript
const label = `Hello, ${name} (id: ${id})`;
const multiline = `
  Line one
  Line two
`;
```

## Async / await

```javascript
async function loadData(url) {
	const res = await fetch(url);
	if (!res.ok) throw new Error(`HTTP ${res.status}`);
	return res.json();
}
```

- Wrap parallel work with `Promise.all` when independent:

```javascript
const [a, b] = await Promise.all([fetchA(), fetchB()]);
```

## Optional chaining and nullish coalescing

```javascript
const city = user?.address?.city;
const port = config.port ?? 8080;
```

Use for **optional** paths and **default only when nullish** (`null`/`undefined`), not for every falsy value.

## Classes (when used)

```javascript
class Model {
	#privateField = 0;

	constructor(id) {
		this.id = id;
	}

	get label() {
		return String(this.id);
	}
}
```

Prefer **composition** over deep inheritance chains in application code.

## What to avoid in new code

- `var`, loose `==` with mixed types
- Mutating function arguments in place when immutability clarifies intent
- Empty `catch` blocks; at minimum log or rethrow
- `arguments` object; use rest parameters: `function f(...args) {}`

## Checklist (quick)

- [ ] `const`/`let`; no accidental globals
- [ ] ESM imports/exports consistent with the file’s module system
- [ ] Async errors handled (`try/catch` or `.catch()` on promises)
- [ ] No reliance on non-transpiled syntax if the build target is old browsers (check `browserslist` / Vite target)

## Additional resources

- Edge cases and migration notes: [reference.md](reference.md)
