#!/usr/bin/env sh
# Runs PHPCS on staged PHP paths from repo root. Skips if Composer deps are missing.
set -eu
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PHPCS="$ROOT/wp-content/themes/silveira/vendor/bin/phpcs"
STD="$ROOT/wp-content/themes/silveira/phpcs.xml.dist"
if [ ! -x "$PHPCS" ]; then
	echo "silveira: skip PHPCS (run: cd wp-content/themes/silveira && composer install)"
	exit 0
fi
if ! command -v php >/dev/null 2>&1; then
	echo "silveira: skip PHPCS (php not in PATH)"
	exit 0
fi
if ! php -r 'exit(0);' >/dev/null 2>&1; then
	echo "silveira: skip PHPCS (PHP not runnable; fix local PHP or run phpcs via Docker)"
	exit 0
fi
exec "$PHPCS" --standard="$STD" "$@"
