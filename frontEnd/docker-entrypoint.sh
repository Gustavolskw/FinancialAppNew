#!/bin/sh
set -eu

MODE="${FRONTEND_RUNTIME_MODE:-development}"
HOST="${FRONTEND_HOST:-0.0.0.0}"
APP_PORT="${PORT:-5173}"

case "$MODE" in
  development|dev)
    export NODE_ENV=development
    export PORT="$APP_PORT"
    exec npm run dev -- --host "$HOST" --port "$APP_PORT"
    ;;
  production|prod)
    export NODE_ENV=production
    export PORT="$APP_PORT"

    if [ ! -f build/server/index.js ]; then
      npm run build
    fi

    exec npm run start
    ;;
  *)
    echo "FRONTEND_RUNTIME_MODE must be development or production. Current value: $MODE"
    exit 1
    ;;
esac
