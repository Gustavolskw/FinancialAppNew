#!/bin/sh
set -eu

ROOT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"

"$ROOT_DIR/scripts/setup-env.sh"

cd "$ROOT_DIR"
FRONTEND_RUNTIME_MODE=production docker compose up -d --build

echo ""
echo "Ambiente completo iniciado em modo build."
echo "O frontend foi configurado com FRONTEND_RUNTIME_MODE=production."
echo "Acesse: https://localhost"
echo "Logs: docker compose logs -f"
