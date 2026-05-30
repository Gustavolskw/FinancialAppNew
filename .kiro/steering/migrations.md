---
inclusion: manual
---

# Migrations Doctrine

## Menu Interativo (Recomendado)

```bash
./scripts/migrations.sh
```

Opções:
1. **Rodar novos scripts**: executa `doctrine:migrations:migrate --no-interaction`
2. **Rodar todos novamente**: rollback para versão 0 e roda todas
3. **Resetar base**: dropa, recria e executa todas as migrations
4. **Excluir base**: dropa a base configurada
5. **Criar migration nova**: executa `make:migration`

Opções destrutivas exigem confirmação digitando `CONFIRMAR`.

## Comandos Manuais

```bash
# Criar migration a partir de diff
docker compose exec backend php bin/console doctrine:migrations:diff

# Executar migrations pendentes
docker compose exec backend php bin/console doctrine:migrations:migrate

# Status das migrations
docker compose exec backend php bin/console doctrine:migrations:status

# Validar schema
docker compose exec backend php bin/console doctrine:schema:validate

# Rollback última migration
docker compose exec backend php bin/console doctrine:migrations:migrate prev
```

## Workflow Para Nova Entidade

1. Criar/alterar entidade em `Backend/src/Entity`
2. Validar schema: `doctrine:schema:validate`
3. Gerar migration: `doctrine:migrations:diff`
4. Revisar SQL gerado em `Backend/migrations/`
5. Executar: `doctrine:migrations:migrate`
6. Validar novamente: `doctrine:schema:validate`

## Troubleshooting

- **Migration falha por constraint**: verifique dados existentes antes de adicionar NOT NULL
- **Schema out of sync**: rode `doctrine:schema:validate` para identificar diferenças
- **Volume vazio**: migrations rodam automaticamente no primeiro `start-dev.sh`
