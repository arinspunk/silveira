#!/usr/bin/env sh
# Rebuilds the theme's assets/ and adds them to the commit (called by lint-staged once per commit).
set -eu

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
THEME="$ROOT/wp-content/themes/silveira"

if [ ! -d "$THEME/node_modules" ]; then
	echo "silveira: theme dependencies are missing; run: cd wp-content/themes/silveira && npm ci" >&2
	exit 1
fi

echo "silveira: rebuilding assets/ (npm run build)…"
(cd "$THEME" && npm run build --silent)
git -C "$ROOT" add -A wp-content/themes/silveira/assets
