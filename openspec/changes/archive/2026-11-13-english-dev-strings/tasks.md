# Tasks

## 1. CI

- [x] 1.1 Translate the step names in `.github/workflows/ci.yml` (e.g. "Descargar imagen de Playwright" → "Pull Playwright image") and the `::error::` / `echo` messages in its `run:` blocks, leaving job names (`frontend`, `php`, `e2e`), artifact names (`assets-ci`, `playwright-report`), env names and commands untouched; verify with `grep -nE "name:|echo" .github/workflows/*.yml` that no Spanish remains, and that `git diff` shows only those text changes
- [x] 1.2 Update the quoted step name in `.claude/skills/silveira-dev-setup/SKILL.md` («Descargar imagen de Playwright») to its new English name; verify with `grep -rn "Descargar imagen" .` that nothing quotes the old name

## 2. Scripts

- [x] 2.1 Translate the messages in `scripts/build-assets.sh` and the "PHPCS was not run" warning in `scripts/lint-staged-phpcs.sh`; verify by running `bash -n` on both and by reading the warning with `sed -n '/<<.MSG/,/^MSG/p'`
- [x] 2.2 Translate the messages printed by the PHP heredoc in `scripts/e2e-seed.sh`: the `WP_Error` text, the `fwrite( STDERR, … )` error and the two summary `echo` lines. Leave the seed content (titles, lemas, "Extracto de <slug>.") as it is. Verify with `npm run e2e:seed`: the output shows the English summary and the seed still creates the same 5 projects
- [x] 2.3 Translate the `throw new Error(...)` messages and the summary `console.log` in `wp-content/themes/silveira/scripts/icons-subset.mjs`; verify with `npm run lint` and `npx prettier --check scripts/icons-subset.mjs` in the theme (the script itself is not run, because it downloads from Google Fonts)

## 3. Tests and Kitchen Sink

- [x] 3.1 Translate the `test()` / `test.describe()` titles and the `expect(…, message)` messages in `tests/e2e/*.js`, without touching selectors, the asserted site texts or the regexes; verify with `npx playwright test --list` (new titles) and `npx playwright test` (4 passed)
- [x] 3.2 Translate the Spanish heading "Cards (listados)" and the card-grid paragraph in `template-kitchen-sink.php`, leaving the Galician sample values of the filters unchanged; verify with `composer run lint` (via Docker) at 0 errors and by opening a page that uses the Kitchen Sink template

## 4. Comments missed by #21

- [x] 4.1 Translate the header comment of `.git-blame-ignore-revs` and the comments in the root `.gitignore`, without touching the commit hashes or the ignore patterns; verify with `git diff` that only `#` lines change and that `git blame --ignore-revs-file .git-blame-ignore-revs README.md` still works

## 5. Close

- [x] 5.1 Check that no Spanish developer text remains in the whole repo: `git grep -nIwE "que|los|las|del|para|con|sin|una|cuando|hay|no se|debe" -- . ':!openspec/changes/archive' ':!.cursor/memory' ':!**/vendor/**' ':!**/node_modules/**' ':!**/assets/**'`, reviewing by hand the matches that are site text (galego reintegrado) or seed content; and verify that `frontend`, `php` and `e2e` are green on the PR

## Workflow follow-up

- Archive the change as the last commit of its branch (no spec sync: `skip_specs: true`) and merge with a merge commit.
