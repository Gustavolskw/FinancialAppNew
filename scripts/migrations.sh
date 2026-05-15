#!/bin/sh
set -eu

ROOT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"

compose() {
  docker compose "$@"
}

backend_console() {
  compose exec -T backend php bin/console "$@"
}

ensure_backend() {
  "$ROOT_DIR/scripts/setup-env.sh"

  cd "$ROOT_DIR"
  compose up -d postgres-fin-new-app </dev/null
  wait_database
  "$ROOT_DIR/scripts/provision-db-user.sh"
  compose up -d --build backend </dev/null
}

wait_database() {
  attempts=0

  printf 'Aguardando PostgreSQL ficar disponível'
  while ! compose exec -T postgres-fin-new-app pg_isready </dev/null >/dev/null 2>&1; do
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
}

confirm() {
  message="$1"

  printf '%s\n' "$message"
  printf 'Digite "CONFIRMAR" para continuar: '
  read -r answer

  [ "$answer" = "CONFIRMAR" ]
}

run_new_migrations() {
  backend_console doctrine:migrations:migrate --no-interaction --allow-no-migration
}

rerun_all_migrations() {
  if ! confirm "Esta ação tentará voltar todas as migrations para a versão 0 e executá-las novamente. Dados podem ser perdidos."; then
    echo "Operação cancelada."
    return
  fi

  backend_console doctrine:migrations:migrate 0 --no-interaction
  backend_console doctrine:migrations:migrate --no-interaction
}

reset_database() {
  if ! confirm "Esta ação vai excluir a base configurada, recriar a base e rodar todas as migrations. Todos os dados serão perdidos."; then
    echo "Operação cancelada."
    return
  fi

  backend_console doctrine:database:drop --force --if-exists
  backend_console doctrine:database:create --if-not-exists
  backend_console doctrine:migrations:migrate --no-interaction
}

drop_database() {
  if ! confirm "Esta ação vai excluir a base configurada. Todos os dados serão perdidos e a aplicação ficará sem banco até recriar a base."; then
    echo "Operação cancelada."
    return
  fi

  backend_console doctrine:database:drop --force --if-exists
}

create_migration() {
  backend_console make:migration
}

print_menu() {
  cat <<'MENU'

Gerenciador de migrations Doctrine

1) Somente rodar novos scripts
2) Rodar todos novamente
3) Resetar base
4) Excluir base
5) Criar migration nova
0) Sair

MENU
}

main() {
  ensure_backend

  while true; do
    print_menu
    printf 'Selecione uma opção: '
    read -r option

    case "$option" in
      1)
        run_new_migrations
        ;;
      2)
        rerun_all_migrations
        ;;
      3)
        reset_database
        ;;
      4)
        drop_database
        ;;
      5)
        create_migration
        ;;
      0)
        echo "Saindo."
        exit 0
        ;;
      *)
        echo "Opção inválida."
        ;;
    esac
  done
}

main "$@"
