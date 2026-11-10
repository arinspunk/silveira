#!/usr/bin/env sh
# Recompila assets/ del theme y los añade al commit (lo llama lint-staged una vez por commit).
set -eu

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
THEME="$ROOT/wp-content/themes/silveira"

if [ ! -d "$THEME/node_modules" ]; then
	echo "silveira: faltan dependencias del theme; ejecuta: cd wp-content/themes/silveira && npm ci" >&2
	exit 1
fi

echo "silveira: recompilando assets/ (npm run build)…"
(cd "$THEME" && npm run build --silent)
git -C "$ROOT" add -A wp-content/themes/silveira/assets
