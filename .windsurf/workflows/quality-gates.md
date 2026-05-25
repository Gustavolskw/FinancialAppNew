---
description: Executar quality gates do backend e frontend
---

# Quality Gates

Este workflow guia a execução dos quality gates do projeto.

## Quality Gate Backend

### Completo (Recomendado)

// turbo
```bash
./scripts/quality-backend.sh
```

Este script executa:
1. Sobe PostgreSQL e backend via Docker Compose
2. Provisiona usuário do banco
3. Composer validate
4. PHP lint (sintaxe)
5. PHPCS (code style)
6. PHPStan (análise estática)
7. PHPUnit (testes)

### Comandos Individuais

#### Composer Validate
```bash
docker compose exec backend composer validate
```

#### PHP Lint (Sintaxe)
```bash
docker compose exec backend composer check-syntax
```

Ou para arquivo específico:
```bash
php -l Backend/src/path/to/file.php
```

#### PHPCS (Code Style)
```bash
docker compose exec backend composer phpcs
```

Ou para arquivo específico:
```bash
docker compose exec backend vendor/bin/phpcs Backend/src/path/to/file.php
```

#### PHPStan (Análise Estática)
```bash
docker compose exec backend composer phpstan
```

Ou para arquivo específico:
```bash
docker compose exec backend vendor/bin/phpstan analyse Backend/src/path/to/file.php
```

#### PHPUnit (Testes)
```bash
docker compose exec backend composer test
```

Ou para teste específico:
```bash
docker compose exec backend vendor/bin/phpunit Backend/tests/path/to/TestFile.php
```

## Quality Gate Frontend

### Completo (Recomendado)

// turbo
```bash
./scripts/quality-frontend.sh
```

Este script executa:
1. TypeScript typecheck
2. Build com Vite
3. Checagem de code smells:
   - `console.*`
   - `debugger`
   - `@ts-ignore`, `@ts-nocheck`, `@ts-expect-error`
   - `eslint-disable`

### Comandos Individuais

#### TypeScript Typecheck
```bash
cd frontEnd
npm run typecheck
```

#### Build
```bash
cd frontEnd
npm run build
```

#### Quality Gate Completo
```bash
cd frontEnd
npm run quality
```

## CI/CD

Os quality gates são executados automaticamente no GitHub Actions:

### Backend Quality
- Trigger: Push ou PR com alterações em `Backend/**`
- Workflow: `.github/workflows/backend-quality.yml`
- Jobs:
  - Composer validate
  - PHP lint
  - PHPCS
  - PHPStan
  - PHPUnit

### Frontend Quality
- Trigger: Push ou PR com alterações em `frontEnd/**`
- Workflow: `.github/workflows/frontend-quality.yml`
- Jobs:
  - npm ci
  - npm run quality

## Quando Executar

### Antes De Commit
Execute o quality gate do módulo alterado:
- Backend: `./scripts/quality-backend.sh`
- Frontend: `./scripts/quality-frontend.sh`

### Durante Desenvolvimento
Para verificações rápidas, use comandos individuais:
- PHP: `php -l arquivo.php`
- TypeScript: `npm run typecheck`

### Antes De PR
Execute ambos os quality gates:
```bash
./scripts/quality-backend.sh
./scripts/quality-frontend.sh
```

### Após Merge
Os workflows do GitHub Actions executam automaticamente.

## Troubleshooting

### Backend

#### PHPCS Errors
Corrija manualmente ou use:
```bash
docker compose exec backend vendor/bin/phpcbf Backend/src/path/to/file.php
```

#### PHPStan Errors
Corrija os erros reportados. PHPStan não tem auto-fix.

#### PHPUnit Failures
Revise e corrija os testes que falharam.

### Frontend

#### TypeScript Errors
Corrija os erros de tipo reportados.

#### Build Errors
Verifique imports, sintaxe e dependências.

#### Code Smells
Remova:
- `console.log()`, `console.error()`, etc.
- `debugger`
- `@ts-ignore`, `@ts-nocheck`, `@ts-expect-error`
- `eslint-disable`

## Configuração

### Backend
- PHPCS: `Backend/phpcs.xml.dist`
- PHPStan: `Backend/phpstan.neon.dist`
- PHPUnit: `Backend/phpunit.xml.dist`

### Frontend
- TypeScript: `frontEnd/tsconfig.json`
- Vite: `frontEnd/vite.config.ts`
- Quality gate: `frontEnd/scripts/quality-gate.mjs`

## Boas Práticas

1. **Execute antes de commit**: Evite commits que quebram o CI
2. **Corrija imediatamente**: Não acumule erros de quality gate
3. **Não desabilite checks**: Não use `@ts-ignore`, `eslint-disable`, etc.
4. **Mantenha testes atualizados**: Atualize testes quando alterar código
5. **Revise configurações**: Mantenha configs de quality gate atualizadas

## Próximos Passos

- Se quality gate passar, faça commit e push
- Se falhar, corrija os erros antes de commit
- Para CI/CD, verifique workflows em `.github/workflows/`
