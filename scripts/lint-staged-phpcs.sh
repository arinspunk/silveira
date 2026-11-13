#!/usr/bin/env sh
# PHPCS on the theme's staged PHP files (called by lint-staged from the repo root).
# Fallback chain: local PHP + vendor/ -> Docker (composer:2) -> visible warning and exit 0 (CI will block).
set -eu

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
THEME="$ROOT/wp-content/themes/silveira"
STD="phpcs.xml.dist"

# lint-staged passes absolute paths; convert them to paths relative to the theme.
FILES=""
for f in "$@"; do
	case "$f" in
		"$THEME"/*) rel="${f#"$THEME"/}" ;;
		wp-content/themes/silveira/*) rel="${f#wp-content/themes/silveira/}" ;;
		*) continue ;;
	esac
	FILES="$FILES $rel"
done
[ -z "$FILES" ] && exit 0

# 1. Local PHP.
if command -v php >/dev/null 2>&1 && php -r 'exit(0);' >/dev/null 2>&1 && [ -x "$THEME/vendor/bin/phpcs" ]; then
	cd "$THEME"
	# shellcheck disable=SC2086
	exec vendor/bin/phpcs --standard="$STD" $FILES
fi

# 2. Docker.
if command -v docker >/dev/null 2>&1 && docker info >/dev/null 2>&1; then
	# shellcheck disable=SC2086
	exec docker run --rm -v "$THEME":/app -w /app composer:2 \
		sh -c '[ -x vendor/bin/phpcs ] || composer install -q --no-interaction; exec vendor/bin/phpcs --standard="$0" "$@"' \
		"$STD" $FILES
fi

# 3. Neither PHP nor Docker.
cat >&2 <<'MSG'

  ⚠️  silveira: PHPCS was NOT run (neither local PHP nor Docker is available).
      The commit goes ahead, but CI will fail if there are violations.
      Start Docker Desktop or install PHP and try again.

MSG
exit 0
