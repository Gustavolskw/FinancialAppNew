#!/bin/sh
set -eu

ROOT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"

generate_secret() {
  bytes="${1:-32}"

  if command -v openssl >/dev/null 2>&1; then
    openssl rand -hex "$bytes"
    return
  fi

  if [ -r /dev/urandom ]; then
    od -An -N "$bytes" -tx1 /dev/urandom | tr -d ' \n'
    printf '\n'
    return
  fi

  echo "Erro: não foi possível gerar segredo forte. Instale openssl ou disponibilize /dev/urandom."
  exit 1
}

get_env_value() {
  env_file="$1"
  key="$2"

  if [ ! -f "$env_file" ]; then
    return
  fi

  grep "^$key=" "$env_file" | tail -n 1 | cut -d '=' -f 2-
}

set_env_value() {
  env_file="$1"
  key="$2"
  value="$3"
  tmp_file="${env_file}.tmp"

  if grep -q "^$key=" "$env_file"; then
    awk -v key="$key" -v value="$value" '
      BEGIN { updated = 0 }
      index($0, key "=") == 1 {
        print key "=" value
        updated = 1
        next
      }
      { print }
      END {
        if (updated == 0) {
          print key "=" value
        }
      }
    ' "$env_file" > "$tmp_file"
    mv "$tmp_file" "$env_file"
    return
  fi

  printf '%s=%s\n' "$key" "$value" >> "$env_file"
}

is_weak_secret() {
  value="$1"

  case "$value" in
    ''|postgres|password|admin|root|secret|test-secret|change-me|financial_app_password|financial-app-password|changeme)
      return 0
      ;;
  esac

  if [ "${#value}" -lt 32 ]; then
    return 0
  fi

  return 1
}

ensure_generated_secret() {
  env_file="$1"
  key="$2"
  label="$3"
  value="$(get_env_value "$env_file" "$key")"

  if is_weak_secret "$value"; then
    set_env_value "$env_file" "$key" "$(generate_secret 32)"
    echo "Gerado: $label em $env_file."
  fi
}

build_database_url() {
  root_env_file="$1"

  postgres_host="$(get_env_value "$root_env_file" POSTGRES_HOST)"
  postgres_port="$(get_env_value "$root_env_file" POSTGRES_CONTAINER_PORT)"
  postgres_db="$(get_env_value "$root_env_file" POSTGRES_DB)"
  postgres_app_user="$(get_env_value "$root_env_file" POSTGRES_APP_USER)"
  postgres_app_password="$(get_env_value "$root_env_file" POSTGRES_APP_PASSWORD)"
  postgres_server_version="$(get_env_value "$root_env_file" POSTGRES_SERVER_VERSION)"
  postgres_charset="$(get_env_value "$root_env_file" POSTGRES_CHARSET)"

  printf 'postgresql://%s:%s@%s:%s/%s?serverVersion=%s&charset=%s\n' \
    "$postgres_app_user" \
    "$postgres_app_password" \
    "$postgres_host" \
    "$postgres_port" \
    "$postgres_db" \
    "$postgres_server_version" \
    "$postgres_charset"
}

copy_env() {
  example_file="$1"
  env_file="$2"
  label="$3"

  if [ ! -f "$example_file" ]; then
    echo "Erro: arquivo de exemplo não encontrado para $label: $example_file"
    exit 1
  fi

  if [ -f "$env_file" ]; then
    echo "Mantido: $env_file já existe."
    sync_missing_env_keys "$example_file" "$env_file"
    return
  fi

  cp "$example_file" "$env_file"
  echo "Criado: $env_file a partir de $example_file."
}

sync_missing_env_keys() {
  example_file="$1"
  env_file="$2"

  while IFS= read -r line || [ -n "$line" ]; do
    case "$line" in
      ''|\#*)
        continue
        ;;
    esac

    key="${line%%=*}"

    if [ "$key" = "$line" ]; then
      continue
    fi

    if ! grep -q "^$key=" "$env_file"; then
      printf '%s\n' "$line" >> "$env_file"
      echo "Adicionado: $key em $env_file."
    fi
  done < "$example_file"
}

copy_env "$ROOT_DIR/.env.example" "$ROOT_DIR/.env" "raiz"
copy_env "$ROOT_DIR/Backend/.env.example" "$ROOT_DIR/Backend/.env" "backend"
copy_env "$ROOT_DIR/frontEnd/.env.example" "$ROOT_DIR/frontEnd/.env" "frontend"

ensure_generated_secret "$ROOT_DIR/.env" "POSTGRES_PASSWORD" "senha administrativa do PostgreSQL"
ensure_generated_secret "$ROOT_DIR/.env" "POSTGRES_APP_PASSWORD" "senha do usuário de aplicação do PostgreSQL"
ensure_generated_secret "$ROOT_DIR/Backend/.env" "APP_SECRET" "segredo JWT/Symfony do backend"

set_env_value "$ROOT_DIR/Backend/.env" "DATABASE_URL" "$(build_database_url "$ROOT_DIR/.env")"
echo "Sincronizado: DATABASE_URL em $ROOT_DIR/Backend/.env com o usuário de aplicação do banco."

echo ""
echo "Ambiente pronto para iniciar."
echo "Para subir em desenvolvimento: ./scripts/start-dev.sh"
echo "Para subir a stack completa com frontend compilado: ./scripts/start-build.sh"
