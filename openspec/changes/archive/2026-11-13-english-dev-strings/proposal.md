# Proposal

## Why

The docs and code comments were moved to English in #21, but developer-facing text that is not a comment is still in Spanish. That includes CI step names and error messages, script output, Playwright test titles and assertion messages, and the Spanish paragraph in the Kitchen Sink style guide. Contributors who don't read Spanish still meet it in CI logs, hook output and test reports. Together with two comment-only files that #21 missed, it is the last Spanish text in the repo outside the historical records (archived changes and `.cursor/memory/`).

## What Changes

- **CI** (`.github/workflows/ci.yml`): step names and the `::error::` and `echo` messages in English.
- **Scripts:**
  - output and error messages of `scripts/build-assets.sh` and `scripts/lint-staged-phpcs.sh` (the "PHPCS was not run" warning);
  - the messages printed by the PHP embedded in `scripts/e2e-seed.sh` (`WP_Error` text, summary lines);
  - the errors and summary line of the theme's `scripts/icons-subset.mjs`.
- **Playwright tests** (`tests/e2e/`): `test()` / `test.describe()` titles and the messages passed to `expect()`.
- **Kitchen Sink** (`template-kitchen-sink.php`): the Spanish heading "Cards (listados)" and the paragraph describing the card grid.
- **Comments missed by #21:** the header comment of `.git-blame-ignore-revs` and the comments in the root `.gitignore`.
- **References that quote the renamed steps:** `.claude/skills/silveira-dev-setup/SKILL.md` quotes the «Descargar imagen de Playwright» step, so it must quote the new name.

**Out of scope (unchanged):**
- the site's interface text (gettext, galego reintegrado), including the Galician sample values in the Kitchen Sink filters ("Que procuras?", "Nom formal"…), which mimic the real site;
- e2e seed content (project titles, lemas, "Extracto de <slug>.", "Não sai no mapa"), because it is test content, not developer text;
- the WP-CLI command's output (`wp silveira geocode`), which is admin text in galego reintegrado;
- the archived changes (`openspec/changes/archive/`) and the planning notes in `.cursor/memory/`: both are historical records and stay in Spanish;
- the theme description in `style.css` (shown in the WordPress admin, written in galego reintegrado);
- the CI job names (`frontend`, `php`, `e2e`): branch protection requires them, and they are already in English.

## Capabilities

### New Capabilities
<!-- None -->

### Modified Capabilities
<!-- None -->

This change does not alter any system behaviour: CI, scripts and tests do the same checks and the site's text does not change. The change declares `skip_specs: true`.

## Impact

- **Files:**
  - `.github/workflows/ci.yml`;
  - `scripts/build-assets.sh`, `scripts/lint-staged-phpcs.sh`, `scripts/e2e-seed.sh`;
  - `wp-content/themes/silveira/scripts/icons-subset.mjs`;
  - `tests/e2e/*.js`;
  - `wp-content/themes/silveira/template-kitchen-sink.php`;
  - `.claude/skills/silveira-dev-setup/SKILL.md`;
  - `.git-blame-ignore-revs` and `.gitignore` (comments only).
- **CI:** renaming steps does not affect the required checks, which are job names. Old runs keep their old step names.
- **No `assets/` rebuild:** no file under `src/` changes.
