#!/usr/bin/env bash
# Para y borra el entorno E2E (contenedores, red y volúmenes del proyecto silveira-e2e).
# No toca el entorno de desarrollo (proyecto silveira).
set -euo pipefail
cd "$(dirname "$0")/.."
docker compose -p silveira-e2e -f docker-compose.yml -f docker-compose.e2e.yml --profile tools down -v --remove-orphans
