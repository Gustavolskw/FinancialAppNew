#!/bin/sh
set -eu

ROOT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
FRONTEND_DIR="$ROOT_DIR/frontEnd"

if ! command -v npm >/dev/null 2>&1; then
  echo "Erro: comando obrigatório não encontrado: npm"
  exit 127
fi

if [ ! -d "$FRONTEND_DIR/node_modules" ]; then
  echo "Erro: dependências do frontend não encontradas."
  echo "Rode: cd frontEnd && npm install"
  exit 1
fi

if [ -d "$FRONTEND_DIR/.react-router/types" ] && [ ! -w "$FRONTEND_DIR/.react-router/types" ]; then
  echo "Erro: cache gerado do React Router está sem permissão de escrita para o usuário atual."
  echo "Isso costuma acontecer quando o frontend foi executado em container e gerou arquivos como outro usuário."
  echo "Corrija as permissões e rode novamente:"
  echo "  sudo chown -R $(id -u):$(id -g) frontEnd/.react-router"
  exit 1
fi

cd "$FRONTEND_DIR"

echo "==> Frontend: rodando typecheck, build e code smells"
npm run quality

echo ""
echo "Quality gate do frontend finalizado com sucesso."
