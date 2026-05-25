---
description: Executar migrations do Doctrine no backend
---

# Executar Migrations Do Doctrine

Este workflow guia a execução de migrations do Doctrine no backend.

## Opção 1: Menu Interativo (Recomendado)

// turbo
```bash
./scripts/migrations.sh
```

Este script apresenta um menu interativo com as seguintes opções:

1. **Verificar status das migrations**: Mostra quais migrations foram executadas e quais estão pendentes
2. **Executar migrations pendentes**: Aplica todas as migrations que ainda não foram executadas
3. **Gerar nova migration**: Cria uma nova migration baseada nas diferenças entre entidades e schema do banco
4. **Reverter última migration**: Desfaz a última migration executada
5. **Sair**: Fecha o menu

## Opção 2: Comandos Manuais

### Verificar status

```bash
docker compose exec backend php bin/console doctrine:migrations:status
```

### Gerar nova migration

```bash
docker compose exec backend php bin/console doctrine:migrations:diff
```

### Executar migrations pendentes

```bash
docker compose exec backend php bin/console doctrine:migrations:migrate
```

### Reverter última migration

```bash
docker compose exec backend php bin/console doctrine:migrations:migrate prev
```

### Executar migration específica

```bash
docker compose exec backend php bin/console doctrine:migrations:execute --up Version{timestamp}
```

## Workflow Completo Para Nova Entidade

### 1. Criar ou modificar entidade

Edite a entidade em `Backend/src/Entity/`.

### 2. Validar schema

```bash
docker compose exec backend php bin/console doctrine:schema:validate
```

### 3. Gerar migration

```bash
docker compose exec backend php bin/console doctrine:migrations:diff
```

Ou use o menu interativo:
```bash
./scripts/migrations.sh
```

### 4. Revisar migration gerada

Abra o arquivo gerado em `Backend/migrations/` e revise o SQL.

### 5. Executar migration

```bash
docker compose exec backend php bin/console doctrine:migrations:migrate
```

Ou use o menu interativo.

### 6. Verificar status

```bash
docker compose exec backend php bin/console doctrine:migrations:status
```

## Troubleshooting

### Migration falha

Se uma migration falhar:

1. Verifique os logs de erro
2. Reverta a migration se necessário:
   ```bash
   docker compose exec backend php bin/console doctrine:migrations:migrate prev
   ```
3. Corrija o problema na entidade ou migration
4. Tente novamente

### Schema fora de sincronia

Se o schema estiver fora de sincronia:

```bash
# Validar schema
docker compose exec backend php bin/console doctrine:schema:validate

# Ver diferenças
docker compose exec backend php bin/console doctrine:schema:update --dump-sql

# CUIDADO: Atualizar diretamente (não recomendado em produção)
docker compose exec backend php bin/console doctrine:schema:update --force
```

### Resetar banco de dados (desenvolvimento apenas)

**ATENÇÃO**: Isso apaga todos os dados!

```bash
# Parar containers
docker compose down

# Remover volume do banco
docker volume rm appfinancasnew_postgres_data

# Subir novamente
docker compose up -d

# Provisionar usuário
./scripts/provision-db-user.sh

# Executar migrations
./scripts/migrations.sh
```

## Boas Práticas

1. **Sempre revise migrations geradas**: O Doctrine pode gerar SQL incorreto em alguns casos
2. **Teste em desenvolvimento primeiro**: Nunca execute migrations não testadas em produção
3. **Faça backup antes de migrations em produção**: Sempre tenha um backup do banco
4. **Commits separados**: Commite migrations separadamente do código
5. **Nomes descritivos**: Edite o nome da migration se necessário para ser mais descritivo
6. **Reversibilidade**: Sempre implemente o método `down()` quando possível

## Próximos Passos

- Após executar migrations, teste o CRUD afetado
- Execute quality gate: `./scripts/quality-backend.sh`
- Documente mudanças significativas em `Backend/docs/codex/`
