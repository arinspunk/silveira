# Tasks

## 1. Shared configuration and root Prettier (commit 1)

- [x] 1.1 Before moving anything, run `npm run format:check` in `wp-content/themes/silveira` and record the result ("All matched files use Prettier code style!"); then `git mv wp-content/themes/silveira/.prettierrc.json .prettierrc.json`, leaving the theme's `.prettierignore` in place; verify that the theme's `npm run format:check` gives the same result and that `npx prettier --find-config-path src/js/map.js` in the theme prints the root file (`../../../.prettierrc.json`)
- [x] 1.2 Add `prettier` `3.8.3` (exact, the version locked in the theme's `package-lock.json`) as a root devDependency with `npm install --save-dev --save-exact prettier@3.8.3`; verify with `npx prettier --version` at the root (prints `3.8.3`) and that `git diff` only touches the root `package.json` and `package-lock.json`
- [x] 1.3 Check Markdown with the moved config: pipe an OpenSpec `.md` file with a nested list through the root Prettier without writing it (`npx prettier --stdin-filepath openspec/specs/project-map/spec.md < openspec/specs/project-map/spec.md`, or any `.md` with a nested list), so it resolves the root config; if the output indents with tabs, add to `.prettierrc.json` an `overrides` entry `{ "files": ["*.md", "*.yml", "*.yaml"], "options": { "useTabs": false } }` and repeat the check; verify in both cases that the theme's `npm run format:check` result is unchanged. Commit 1.1–1.3 together

## 2. One-off reformat (commit 2)

- [x] 2.1 Run `npx prettier --write tests/ playwright.config.js lint-staged.config.mjs` at the root and commit only that result as `style: format root JS with the shared Prettier config`; verify that the commit only touches `tests/e2e/home.spec.js`, `playwright.config.js` and `lint-staged.config.mjs`, that `git diff -w HEAD~1` shows only line wrapping and quotes (no logic changes), and that with `npm run e2e:up && npm run e2e:seed`, `npx playwright test` gives 4 passed

## 3. Checks and docs (commit 3)

- [x] 3.1 Add root scripts `format` (`prettier --write`) and `format:check` (`prettier --check`) for `"tests/**/*.js" playwright.config.js lint-staged.config.mjs`; verify that `npm run format:check` at the root passes, and that it fails after adding a double-quoted string to a test file (then revert it)
- [x] 3.2 Add a `lint-staged.config.mjs` entry for `tests/**/*.js`, `playwright.config.js` and `lint-staged.config.mjs` that runs `prettier --write` from the root; verify by staging a deliberately misformatted test file and running `npx lint-staged --concurrent false`, which must rewrite it (then restore the file)
- [x] 3.3 Add a step to the `e2e` job in `.github/workflows/ci.yml`, right after `npm ci` and before the stack starts, that compares the `prettier` version in the root and theme `package-lock.json` files and fails with `::error::` if they differ (design), followed by `npm run format:check`; verify that the job and artifact names are unchanged, and that the version check fails when the root pin is changed temporarily to another version (revert it)
- [x] 3.4 Update the docs: the `e2e` row of the CI table and the "reproduce locally" block in `README.md` (root `npm ci && npm run format:check`), and the pre-commit list and "Reproduce CI locally" section in `.claude/skills/silveira-dev-setup/SKILL.md`, including that the root `prettier` pin must match the version locked in the theme; verify that the commands in both docs run as written. Commit 3.1–3.4 together

## 4. Blame (commit 4)

- [x] 4.1 Add the hash of the commit from 2.1 to `.git-blame-ignore-revs` with its subject as a comment, following the existing entries; verify that `git blame --ignore-revs-file .git-blame-ignore-revs tests/e2e/home.spec.js` attributes the reformatted lines to earlier commits

## 5. Close

- [ ] 5.1 Push the branch, open the PR and verify that `frontend`, `php` and `e2e` are green and that the `e2e` log shows the new format step

## Workflow follow-up

- Archive the change as the last commit of its branch (no spec sync: `skip_specs: true`).
- Ask for permission before merging; merge with a merge commit (no squash or rebase) so the hash in `.git-blame-ignore-revs` stays valid.
