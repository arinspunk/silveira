#!/usr/bin/env sh
# PHPCS sobre los PHP staged del theme (lo llama lint-staged desde la raíz).
# Cascada: PHP local + vendor/ -> Docker (composer:2) -> aviso visible y exit 0 (la CI bloqueará).
set -eu

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
THEME="$ROOT/wp-content/themes/silveira"
STD="phpcs.xml.dist"

# lint-staged pasa rutas absolutas; las convertimos a relativas al theme.
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

# 1. PHP local.
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

# 3. Ni PHP ni Docker.
cat >&2 <<'MSG'

  ⚠️  silveira: PHPCS NO se ha ejecutado (no hay PHP local ni Docker disponible).
      El commit continúa, pero la CI fallará si hay infracciones.
      Arranca Docker Desktop o instala PHP y vuelve a probar.

MSG
exit 0
