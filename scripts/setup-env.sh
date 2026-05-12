#!/bin/sh
set -eu

ROOT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"

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
    return
  fi

  cp "$example_file" "$env_file"
  echo "Criado: $env_file a partir de $example_file."
}

copy_env "$ROOT_DIR/.env.example" "$ROOT_DIR/.env" "raiz"
copy_env "$ROOT_DIR/Backend/.env.example" "$ROOT_DIR/Backend/.env" "backend"
copy_env "$ROOT_DIR/frontEnd/.env.example" "$ROOT_DIR/frontEnd/.env" "frontend"

echo ""
echo "Ambiente pronto para iniciar."
echo "Para subir em desenvolvimento: ./scripts/start-dev.sh"
echo "Para subir a stack completa com frontend compilado: ./scripts/start-build.sh"
