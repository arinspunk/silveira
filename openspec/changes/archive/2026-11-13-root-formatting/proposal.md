# Proposal

## Why

The only Prettier configuration lives in the theme (`wp-content/themes/silveira/.prettierrc.json`), so the JavaScript at the repo root (`tests/e2e/`, `playwright.config.js`, `lint-staged.config.mjs`) is formatted with Prettier's defaults, or not at all. Nothing checks it: lint-staged only runs Prettier on theme files and CI only runs `format:check` in the theme. Running Prettier at the root reports all three test files, and with the theme's style `home.spec.js`, `playwright.config.js` and `lint-staged.config.mjs` are already out of line.

## What Changes

- **Shared configuration:** move `wp-content/themes/silveira/.prettierrc.json` to the repo root. Prettier looks for its configuration in parent folders, so the theme keeps using the same file and its formatting does not change. The theme's `.prettierignore` stays where it is.
- **Root Prettier:** add `prettier` as a root devDependency pinned to the exact version locked in the theme (`3.8.3`), and add root `format` / `format:check` scripts for `tests/`, `playwright.config.js` and `lint-staged.config.mjs`.
- **Pre-commit:** add a `lint-staged.config.mjs` entry that runs `prettier --write` on those files.
- **CI:** add a step to the `e2e` job, which already runs `npm ci` at the root, that fails if the Prettier versions locked at the root and in the theme differ, followed by `npm run format:check`.
- **One-off reformat:** format the root files that don't match yet (`tests/e2e/home.spec.js`, `playwright.config.js`, `lint-staged.config.mjs`) in a commit of their own with no logic changes, and add its hash to `.git-blame-ignore-revs`. `helpers.js` and `projeto.spec.js` already match the theme style.
- **Docs:** update the README CI section and the `silveira-dev-setup` skill (pre-commit list, reproducing CI locally) to mention the root check.

**Out of scope:**
- ESLint for `tests/` and the root config files; it can be considered separately;
- formatting other root files (Markdown, YAML, `package.json`);
- changing the theme's `prettier` range (`^3.4.2`) or upgrading Prettier.

## Capabilities

### New Capabilities
<!-- None -->

### Modified Capabilities
<!-- None -->

This is developer tooling only: the site, the tests' checks and the theme's formatting stay the same. The change declares `skip_specs: true`.

## Impact

- **Files:**
  - `.prettierrc.json` (moved from `wp-content/themes/silveira/`);
  - `package.json`, `package-lock.json` (root);
  - `lint-staged.config.mjs`, `.github/workflows/ci.yml`, `.git-blame-ignore-revs`;
  - `tests/e2e/home.spec.js`, `playwright.config.js` (formatting only);
  - `README.md`, `.claude/skills/silveira-dev-setup/SKILL.md`.
- **Dependencies:** `prettier@3.8.3` as a root devDependency.
- **CI:** the `e2e` job gets one more step; the required checks (`frontend`, `php`, `e2e`) keep their names.
- **No `assets/` rebuild:** no file under `src/` changes.
