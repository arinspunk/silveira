# JavaScript ES2015+ — reference notes

## `this` in arrows

Arrow functions **do not** bind their own `this`; they inherit from the enclosing scope. Use regular `function` for object methods when `this` must be the receiver, or use class fields with arrows if the team allows.

## Dynamic `import()`

```javascript
const mod = await import('./heavy.js');
```

Use for code-splitting and conditional loading; bundlers (Vite, etc.) emit separate chunks.

## Iteration

- `for...of` for iterables (arrays, `Map`, etc.).
- `for...in` for object keys (watch inherited properties; often prefer `Object.keys`).

## Numeric separators (ES2021)

`1_000_000` for readability only; same value at runtime.

## BigInt

Suffix `n`: `123n`. Use when integers exceed `Number.MAX_SAFE_INTEGER`; do not mix with regular numbers without conversion.

## Aligning with linters

ESLint `env` / `parserOptions.ecmaVersion` should match features used. If the project targets older browsers, rely on the bundler’s transpilation rather than avoiding modern syntax in source unless policy says otherwise.
