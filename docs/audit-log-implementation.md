# Implementação: Audit Log para Exclusão de Transações

**Data:** 2026-05-30  
**Projeto:** AppFinancasNew  
**Status:** Implementado e migrado

## Objetivo

Salvar backup automático de transações excluídas (Entry e Expense) em tabelas de auditoria, mantendo rastro completo dos dados deletados.

## Decisões de Design

1. **Estrutura das tabelas:** colunas planas (não JSON), sem FK constraints, apenas IDs.
2. **Dados armazenados:** IDs originais + campos da Transaction + metadados (`deleted_at`, `deleted_by_user_id`, `deleted_by_user_name`).
3. **Fluxo:** `beforeDelete` coleta dados, `afterDelete` persiste (apenas se delete suceder).
4. **Atomicidade:** `delete()` envolto em transação (garante delete + audit ou rollback).
5. **Configurações:** criadas para futura leitura via admin frontend.

## Arquivos Criados/Modificados

### Backend
- `Backend/src/Entity/EntryAuditLog.php` – entidade com colunas: `original_entry_id`, `original_transaction_id`, `entry_type_id`, campos da Transaction, `deleted_by_user_id`, `deleted_by_user_name`, `deleted_at`.
- `Backend/src/Entity/ExpenseAuditLog.php` – similar + `expense_type_id`, `payment_method_id`, `installments`.
- `Backend/src/Repository/EntryAuditLogRepository.php` – repository padrão.
- `Backend/src/Repository/ExpenseAuditLogRepository.php` – repository padrão.
- `Backend/src/Infrastructure/DTO/Configuration/EntryAuditLogConfiguration.php` – Configuration para futuros endpoints.
- `Backend/src/Infrastructure/DTO/Configuration/ExpenseAuditLogConfiguration.php` – Configuration para futuros endpoints.
- `Backend/src/Infrastructure/Handler/Action/Specific/EntrySpecificAction.php` – modificado: `beforeDelete` cria `EntryAuditLog`, `afterDelete` persiste.
- `Backend/src/Infrastructure/Handler/Action/Specific/ExpenseSpecificAction.php` – modificado: mesmo padrão.
- `Backend/src/Infrastructure/Handler/Action/Action.php` – modificado: `delete()` agora envolto em `beginTransaction`/`commit`/`rollBack`.
- `Backend/migrations/Version20260530150000.php` – migration criando as duas tabelas (sem FK).

## Execução

- Migration aplicada via `docker compose exec backend php bin/console doctrine:migrations:migrate`.
- Sintaxe PHP validada (todos os arquivos OK).
- Tabelas criadas no banco PostgreSQL.

## Como Funciona

1. **Exclusão de Entry:**
   - `EntrySpecificAction::beforeDelete()` coleta dados do Entry e Transaction
   - Cria `EntryAuditLog` com snapshot
   - Remove Transaction (cascade)
   - `afterDelete()` persiste audit log
   - Transação garante atomicidade

2. **Exclusão de Expense:**
   - Mesmo fluxo com `ExpenseSpecificAction` e `ExpenseAuditLog`

## Próximos Passos Possíveis

1. **Testes:** validar exclusão real e verificar persistência do audit log.
2. **Endpoints de leitura:** criar rotas admin (`GET /audit/entries`, `GET /audit/expenses`).
3. **Frontend admin:** interface para visualizar logs de exclusão.
4. **Filtros:** por período, usuário, tipo.

## Referências

- Arquitetura Backend: `docs/codex/project-context.md`
- Padrões Symfony: `.kiro/steering/symfony-patterns.md`
- Skill Backend Actions: `.kiro/skills/backend-actions/SKILL.md`

## Notas de Segurança

- Nenhum dado sensível exposto (senhas, tokens).
- `deleted_by_user_name` armazena `User->getName()` (não email).
- IDs são inteiros sem FK; se registro original for deletado, audit permanece.

---

*Documento atualizado em: 2026-05-30*