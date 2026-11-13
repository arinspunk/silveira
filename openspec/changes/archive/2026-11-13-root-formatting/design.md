# Design

## Context

See proposal.md for motivation. Current state:

- The theme has `prettier` `^3.4.2` in `package.json`, locked at `3.8.3`. Its `format` / `format:check` scripts list explicit globs, and its `.prettierignore` excludes `assets/`, `node_modules/` and `package-lock.json`.
- The root `package.json` only has Husky, lint-staged and `@playwright/test`. A root `npx prettier` downloads whatever is latest (3.10.1 today) and uses the defaults (spaces, double quotes).
- The pre-commit hook runs `npx lint-staged --concurrent false` from the root. lint-staged reads `lint-staged.config.mjs` from the working tree, so a new entry already applies to the commit that adds it.
- The `e2e` CI job runs `npm ci` at the root before starting the stack. The `frontend` job only works inside the theme.

## Goals / Non-Goals

**Goals:**
- One Prettier configuration file for the whole repo, with identical results in the theme.
- Root JS is checked on commit and in CI, with the same Prettier version as the theme.
- The reformat sits in its own commit that `git blame` can skip.

**Non-Goals:**
- Changing any formatting option, or which theme files are formatted.
- A root `.prettierignore`: the root scripts list explicit paths, so nothing else gets formatted.

## Decisions

**Move the file instead of copying or extending it.** Prettier resolves `.prettierrc.json` by walking up from each formatted file, so `wp-content/themes/silveira/**` finds the root file once the theme copy is gone. A copy would drift. An `overrides` block or a shared package is more machinery than one small file needs. `.prettierignore` is different: Prettier only reads it from the working directory, so the theme's file stays in the theme and keeps working for `npm run format*` and for lint-staged's `cd wp-content/themes/silveira && npx prettier --write`.

**Pin the root to `3.8.3` exactly.** The root has no lockfile churn from the theme, so an exact pin is the simplest way to match the version the theme has locked. Alternative: use the theme's binary from the root scripts (`wp-content/themes/silveira/node_modules/.bin/prettier`). That would make the root check depend on `npm ci` in the theme, which the `e2e` job does not run. The cost: when the theme's lockfile moves to a newer Prettier, the root pin has to be bumped by hand (see Risks).

**CI enforces the version match.** Right before `format:check`, the `e2e` step reads the `prettier` version from the root `package-lock.json` and from `wp-content/themes/silveira/package-lock.json` (`node -p "require('<lockfile>').packages['node_modules/prettier'].version"`) and fails with `::error::` if they differ. Both lockfiles are in the checkout, so nothing extra is installed. Relying on a formatting failure is not enough: most Prettier minor versions format the same, so the two checks could drift apart silently.

**Root scripts with explicit paths.** `format` / `format:check` target `tests/`, `playwright.config.js` and `lint-staged.config.mjs`, mirroring the theme scripts. They do not use `.` with an ignore file, so Markdown, YAML and the OpenSpec files stay out of scope.

**CI step in `e2e`, before the stack starts.** It is the only job that already installs root dependencies. The step goes right after `npm ci`, so a formatting error fails in seconds, before Docker is started. A separate job would need a new required check in branch protection.

**Commit order.** Because lint-staged applies its config to the commit that adds it, the order keeps the reformat isolated:
1. Move `.prettierrc.json` and add the root devDependency (no JS staged, so no formatting happens).
2. Reformat `tests/e2e/home.spec.js`, `playwright.config.js` and `lint-staged.config.mjs` with the root `prettier --write`, and nothing else.
3. Add the scripts, the lint-staged entry, the CI step and the docs. `lint-staged.config.mjs` is already formatted, so the new entry does not reformat it again.
4. Add the hash of commit 2 to `.git-blame-ignore-revs`. The hash stays valid because the merge uses a merge commit.
5. Archive the change.

## Risks / Trade-offs

- [The theme's lockfile moves Prettier to a new version and the root pin stays behind] → the version check in the `e2e` step fails on the first PR where they differ; the README and the dev-setup skill explain how to bump the root pin.
- [Editors that format on save now pick up the root config for root Markdown/YAML files] → YAML cannot use tabs, so Prettier ignores `useTabs` there; for Markdown it is not certain (nested list indentation may use tabs). Task 1.3 checks it; if tabs appear, `.prettierrc.json` gets an `overrides` block with `useTabs: false` for `*.md`, `*.yml` and `*.yaml`, which does not affect JS. No script or hook formats those files.
- [Moving the file changes the theme's output] → Check before and after the move: the theme's `npm run format:check` must report "All matched files use Prettier code style!" both times, and `npx prettier --find-config-path src/js/map.js` in the theme must resolve to the root file.
