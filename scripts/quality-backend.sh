#!/bin/sh
set -eu

ROOT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"

if ! command -v docker >/dev/null 2>&1; then
  echo "Erro: comando obrigatório não encontrado: docker"
  exit 127
fi

"$ROOT_DIR/scripts/provision-db-user.sh"

cd "$ROOT_DIR"

echo "==> Backend: subindo ambiente Docker necessário para o quality gate"
docker compose up -d --build backend

echo ""
echo "==> Backend: rodando quality gate dentro do container backend"
docker compose exec -T backend sh -lc '
  set -eu
  cd /var/www

  if [ ! -d vendor ] || [ ! -x vendor/bin/phpcs ] || [ ! -x vendor/bin/phpstan ]; then
    echo "==> Backend/container: instalando dependências Composer"
    composer install --prefer-dist --no-interaction --no-progress
  fi

  echo "==> Backend/container: validando composer.json"
  composer validate --strict

  echo ""
  echo "==> Backend/container: validando sintaxe PHP"
  find src tests -name "*.php" -print0 | xargs -0 -n 1 php -l

  echo ""
  echo "==> Backend/container: rodando PHPCS"
  vendor/bin/phpcs --standard=phpcs.xml.dist

  echo ""
  echo "==> Backend/container: rodando PHPStan"
  vendor/bin/phpstan analyse --configuration=phpstan.neon.dist --no-progress --memory-limit=1G

  echo ""
  echo "==> Backend/container: rodando testes unitários"
  composer test
'

echo ""
echo "Quality gate do backend finalizado com sucesso dentro do container."
