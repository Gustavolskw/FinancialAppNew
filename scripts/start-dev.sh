#!/bin/sh
set -eu

ROOT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
FRONTEND_ENV="$ROOT_DIR/frontEnd/.env"

set_frontend_runtime_mode() {
  mode="$1"
  tmp_file="$(mktemp)"

  if grep -q '^FRONTEND_RUNTIME_MODE=' "$FRONTEND_ENV"; then
    sed "s/^FRONTEND_RUNTIME_MODE=.*/FRONTEND_RUNTIME_MODE=$mode/" "$FRONTEND_ENV" > "$tmp_file"
  else
    cp "$FRONTEND_ENV" "$tmp_file"
    printf '\nFRONTEND_RUNTIME_MODE=%s\n' "$mode" >> "$tmp_file"
  fi

  mv "$tmp_file" "$FRONTEND_ENV"
}

"$ROOT_DIR/scripts/setup-env.sh"
set_frontend_runtime_mode "development"
"$ROOT_DIR/scripts/provision-db-user.sh"

cd "$ROOT_DIR"
echo ""
echo "Ambiente completo iniciando em modo desenvolvimento."
echo "O frontEnd/.env foi configurado com FRONTEND_RUNTIME_MODE=development."
echo "Acesse: https://localhost"
echo "Logs do Docker ficarão anexados neste terminal. Use Ctrl+C para parar."

FRONTEND_RUNTIME_MODE=development docker compose up
