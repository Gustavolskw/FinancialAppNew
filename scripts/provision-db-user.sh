#!/bin/sh
set -eu

ROOT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"

if ! command -v docker >/dev/null 2>&1; then
  echo "Erro: comando obrigatório não encontrado: docker"
  exit 127
fi

"$ROOT_DIR/scripts/setup-env.sh"

cd "$ROOT_DIR"

echo "==> Banco: subindo PostgreSQL"
docker compose up -d postgres-fin-new-app

attempts=0
printf 'Aguardando PostgreSQL ficar disponível'
while ! docker compose exec -T postgres-fin-new-app pg_isready >/dev/null 2>&1; do
  attempts=$((attempts + 1))

  if [ "$attempts" -ge 30 ]; then
    printf '\n'
    echo "Erro: banco de dados não ficou disponível a tempo."
    exit 1
  fi

  printf '.'
  sleep 2
done

printf '\nPostgreSQL disponível.\n'

echo "==> Banco: provisionando usuário de aplicação"
docker compose exec -T postgres-fin-new-app /docker-entrypoint-initdb.d/init.sh

echo "Usuário de aplicação do banco provisionado com sucesso."
