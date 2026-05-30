---
inclusion: manual
---

# Quality Gates

## Backend

O quality gate do backend (`./scripts/quality-backend.sh`) executa dentro do container:

1. `composer validate` — Valida metadados do composer.json
2. `php -l` — Sintaxe PHP em todos os arquivos
3. `composer phpcs` — Code style (PSR-12)
4. `composer phpstan` — Análise estática
5. `composer test` — PHPUnit

### Comandos Individuais

```bash
# Gate completo
./scripts/quality-backend.sh

# Sintaxe de um arquivo
php -l Backend/src/path/to/file.php

# PHPCS em arquivo específico
docker compose exec backend vendor/bin/phpcs src/path/to/file.php

# PHPStan em arquivo específico
docker compose exec backend vendor/bin/phpstan analyse src/path/to/file.php

# Testes
docker compose exec backend composer test
```

## Frontend

O quality gate do frontend (`./scripts/quality-frontend.sh` ou `npm run quality`) executa:

1. TypeScript typecheck (`tsc --noEmit`)
2. Build com Vite (`npm run build`)
3. Checagem de code smells:
   - `console.*`
   - `debugger`
   - `@ts-ignore`, `@ts-nocheck`, `@ts-expect-error`
   - `eslint-disable`

### Comandos Individuais

```bash
# Gate completo
./scripts/quality-frontend.sh

# Typecheck
cd frontEnd && npm run typecheck

# Build
cd frontEnd && npm run build

# Quality dentro do frontend
cd frontEnd && npm run quality
```

## CI/CD

- `.github/workflows/backend-quality.yml`: roda em push/PR quando `Backend/**` muda
- `.github/workflows/frontend-quality.yml`: roda em push/PR quando `frontEnd/**` muda

Os gates são separados por módulo. Mudança exclusiva no frontend não dispara testes do backend e vice-versa.

## Quando Usar

- **Mudança pequena em PHP**: `php -l arquivo.php`
- **Mudança ampla no backend**: `./scripts/quality-backend.sh`
- **Mudança pequena no frontend**: `npm run typecheck`
- **Mudança ampla no frontend**: `npm run build` ou `./scripts/quality-frontend.sh`
- **Mudança em Docker/env/scripts**: `docker compose config --quiet`
- **Mudança em documentação**: `git diff --check`
