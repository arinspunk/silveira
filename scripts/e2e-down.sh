#!/usr/bin/env bash
# Stops and removes the E2E environment (containers, network and volumes of the silveira-e2e project).
# Does not touch the development environment (silveira project).
set -euo pipefail
cd "$(dirname "$0")/.."
docker compose -p silveira-e2e -f docker-compose.yml -f docker-compose.e2e.yml --profile tools down -v --remove-orphans
