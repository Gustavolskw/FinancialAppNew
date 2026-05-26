---
description: Workflow especializado em melhorias e otimizações de Actions no backend Symfony
---

# Workflow Especializado em Actions do Backend

Este workflow é focado exclusivamente em melhorias, otimizações e expansão do sistema de Actions do backend Symfony. Use quando precisar trabalhar especificamente no fluxo CRUD, ActionManager, Actions genéricas, SpecificActions e toda a orquestração de operações.

## Pré-requisitos

- Backend rodando via Docker ou localmente
- Banco de dados PostgreSQL configurado
- Composer instalado
- PHP 8.4+

## Fase 1: Preparação e Contexto

### 1. Ler documentação e agentes

```bash
cat /home/gustavo-luis/Documents/AppFinancasNew/AGENTS.md
cat /home/gustavo-luis/Documents/AppFinancasNew/Backend/AGENTS.md
cat /home/gustavo-luis/Documents/AppFinancasNew/.codex/agents/appfinancas-backend.toml
cat /home/gustavo-luis/Documents/AppFinancasNew/agents/appfinancas-backend.md
```

### 2. Ler Skills relacionadas

```bash
cat /home/gustavo-luis/Documents/AppFinancasNew/skills/appfinancasnew-backend-actions/SKILL.md
cat /home/gustavo-luis/Documents/AppFinancasNew/skills/appfinancasnew-backend-entity-dtos/SKILL.md
cat /home/gustavo-luis/Documents/AppFinancasNew/skills/appfinancasnew-backend-helpers/SKILL.md
cat /home/gustavo-luis/Documents/AppFinancasNew/skills/appfinancasnew-backend-fields/SKILL.md
```

### 3. Invocar skills especializadas

```
/skill appfinancasnew-backend-actions
/skill backend-specialist
/skill symfony:cqrs-and-handlers
/skill symfony:strategy-pattern
/skill symfony:doctrine-transactions
/skill symfony:tdd-with-phpunit
/skill symfony:functional-tests
```

### 4. Analisar estrutura atual

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend
tree src/Infrastructure/Handler/Action/
cat src/Infrastructure/Handler/Action/ActionManager.php
cat src/Infrastructure/Handler/Action/Action.php
find src/Infrastructure/Handler/Action/Specific -name "*.php" -type f
```

## Fase 2: Melhorias no ActionManager

### 5. Implementar eventos de Actions

Criar eventos: `BeforeActionEvent`, `AfterActionEvent`, `ActionFailedEvent`

### 6. Adicionar logging estruturado

Implementar `ActionLoggerListener` para registrar todas as operações

### 7. Implementar rate limiting

Adicionar controle de taxa por endpoint e usuário

### 8. Adicionar métricas

Coletar tempo de execução, taxa de sucesso/falha, uso de recursos

### 9. Melhorar tratamento de erros

Criar exceptions customizadas: `ValidationException`, `AuthorizationException`, `BusinessRuleException`, `OptimisticLockException`

### 10. Validar melhorias

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend
php -l src/Infrastructure/Handler/Action/ActionManager.php
phpstan analyse src/Infrastructure/Handler/Action/ActionManager.php --level=9
```

## Fase 3: Melhorias na Action Base

### 11. Implementar transações explícitas

Adicionar método `executeInTransaction()` com rollback automático

### 12. Adicionar validação de estado

Implementar `validateEntityState()` usando Symfony Validator

### 13. Implementar soft delete

Adicionar suporte a `deletedAt` para exclusão lógica

### 14. Adicionar versionamento otimista

Implementar `checkVersion()` para prevenir conflitos de concorrência

### 15. Implementar operações em lote

Adicionar `batchCreate()`, `batchUpdate()`, `batchDelete()`

### 16. Adicionar cache de resultados

Implementar cache para listagens e queries frequentes

### 17. Implementar retry automático

Adicionar `executeWithRetry()` com exponential backoff

### 18. Adicionar auditoria

Registrar quem, quando, o quê em todas as operações

### 19. Validar melhorias

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend
php -l src/Infrastructure/Handler/Action/Action.php
phpstan analyse src/Infrastructure/Handler/Action/Action.php --level=9
```

## Fase 4: Melhorias em SpecificActions

### 20. Padronizar SpecificActions

Criar template padrão com hooks: `beforeCreate`, `afterCreate`, `beforeUpdate`, `afterUpdate`, `beforeDelete`, `afterDelete`

### 21. Melhorar UserSpecificAction

- Validar força da senha
- Hash seguro de senha
- Validar email único
- Criar wallet padrão ao criar usuário
- Impedir exclusão se houver transações

### 22. Melhorar WalletSpecificAction

- Validar nome único por usuário
- Validar saldo inicial
- Criar transação inicial se saldo > 0
- Impedir exclusão se houver transações
- Recalcular saldo após operações

### 23. Melhorar TransactionSpecificAction

- Validar valor positivo
- Atualizar saldo da wallet
- Validar data não futura
- Impedir alteração após reconciliação
- Disparar eventos de mudança de saldo

### 24. Criar SpecificActions faltantes

Criar para: `Expense`, `Entry`, `ExpenseType`, `EntryType`, `PaymentMethod`

### 25. Validar SpecificActions

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend
phpstan analyse src/Infrastructure/Handler/Action/Specific --level=9
```

## Fase 5: Testes de Actions

### 26. Criar testes unitários para ActionManager

Testar: validação JWT, autorização, determinação de ação, execução de operações

### 27. Criar testes unitários para Action base

Testar: validação, persistência, hooks, rollback, paginação

### 28. Criar testes para cada SpecificAction

Testar todos os hooks e regras de negócio específicas

### 29. Criar testes funcionais

Testar fluxo completo: login → create → update → get → delete

### 30. Rodar suite de testes

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend
composer test
composer test -- --filter=Action
composer test -- --coverage-html var/coverage
```

## Fase 6: Performance e Otimização

### 31. Implementar cache estratégico

Cache para listagens, entidades raramente modificadas, cálculos complexos

### 32. Otimizar queries

Identificar N+1, adicionar eager loading, usar partial objects, paginação eficiente

### 33. Implementar operações em lote

Endpoints batch: `POST /api/users/batch`, `PUT /api/users/batch`, `DELETE /api/users/batch`

### 34. Adicionar índices de banco

Índices em foreign keys, colunas de filtro, ordenação e busca

### 35. Validar performance

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend
# Usar Symfony Profiler para identificar gargalos
# Medir tempo de execução de cada Action
```

## Fase 7: Monitoramento e Observabilidade

### 36. Implementar logging estruturado

Logging em início/fim de Actions, erros, operações críticas, mudanças de estado

### 37. Implementar métricas

Coletar: tempo de execução, taxa de sucesso/falha, número de operações, tamanho de payloads

### 38. Implementar auditoria completa

Tabela de auditoria com: usuário, timestamp, operação, valores antigos/novos, IP

### 39. Adicionar alertas

Alertas para: taxa de erro alta, operações lentas, tentativas de acesso não autorizado

## Fase 8: Segurança de Actions

### 40. Revisar autenticação JWT

Validar expiração, implementar refresh token, blacklist, rate limiting

### 41. Revisar autorização

Adicionar Voters, implementar ACL, validar ownership, adicionar roles customizadas

### 42. Implementar Voters

Criar Voters para: `Wallet`, `Transaction`, `Expense`, `Entry`

### 43. Adicionar validações de segurança

CSRF protection, input sanitization, SQL injection prevention, XSS prevention

### 44. Implementar auditoria de segurança

Logging de tentativas de autenticação, acessos negados, mudanças de permissão

## Fase 9: Documentação

### 45. Documentar cada Action

PHPDoc completo com: descrição, parâmetros, retornos, exceptions, exemplos

### 46. Criar diagramas de fluxo

Diagramas para: criação, atualização, exclusão, listagem, autorização

### 47. Documentar regras de negócio

Documentar todas as regras implementadas em SpecificActions

### 48. Criar guia de uso

Guia para desenvolvedores sobre como criar novas Actions e SpecificActions

### 49. Atualizar docs/codex

Atualizar: `project-context.md`, `agent-playbook.md`, `review-notes.md`

## Fase 10: Quality Gates

### 50. Rodar quality gate completo

// turbo
```bash
cd /home/gustavo-luis/Documents/AppFinancasNew
./scripts/quality-backend.sh
```

### 51. Corrigir issues

Corrigir todos os issues de PHPCS, PHPStan, PHPUnit, Doctrine

### 52. Validar em Docker

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew
docker compose up --build -d
docker compose logs -f backend
docker compose down
```

### 53. Review final

Checklist:
- [ ] Arquitetura consistente
- [ ] Separação de responsabilidades
- [ ] Código segue PSR/PER-CS
- [ ] Tipos declarados
- [ ] Validações robustas
- [ ] Autorização implementada
- [ ] Testes com boa cobertura
- [ ] Performance otimizada
- [ ] Documentação completa
- [ ] Quality gates passando

### 54. Atualizar review-notes.md

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend
cat >> docs/codex/review-notes.md << 'EOF'

## Melhorias em Actions - [DATA]

### ActionManager
- Eventos de Actions
- Logging estruturado
- Rate limiting
- Métricas
- Tratamento de erros

### Action Base
- Transações explícitas
- Validação de estado
- Soft delete
- Versionamento otimista
- Operações em lote
- Cache
- Retry automático
- Auditoria

### SpecificActions
- UserSpecificAction melhorado
- WalletSpecificAction melhorado
- TransactionSpecificAction melhorado
- Novas SpecificActions criadas

### Testes
- Testes unitários completos
- Testes funcionais completos
- Cobertura expandida

### Performance
- Cache implementado
- Queries otimizadas
- Operações em lote
- Índices adicionados

### Segurança
- Autenticação JWT melhorada
- Autorização com Voters
- Validações de segurança
- Auditoria completa

### Próximos Passos
- Implementar event sourcing
- Adicionar GraphQL
- Implementar CQRS completo
- Adicionar APM

EOF
```

### 55. Commit das melhorias

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew
git add Backend/
git commit -m "feat(backend): comprehensive Actions improvements

- Enhanced ActionManager with events, logging, rate limiting
- Improved Action base with transactions, validation, caching
- Refactored all SpecificActions with standardized hooks
- Expanded test coverage for all Actions
- Optimized performance with caching and batch operations
- Enhanced security with Voters and authorization
- Added comprehensive monitoring and observability
- Updated documentation

All quality gates passing."
```

## Conclusão

Este workflow aplicou melhorias focadas em Actions usando:

- **Agentes**: appfinancas-backend (oficial e compilado)
- **Skills locais**: actions, entity-dtos, helpers, fields
- **Skills Symfony**: cqrs-and-handlers, strategy-pattern, doctrine-transactions, tdd-with-phpunit, functional-tests

O sistema de Actions agora está:
- ✅ Mais robusto e confiável
- ✅ Melhor testado
- ✅ Otimizado para performance
- ✅ Seguro e auditável
- ✅ Bem documentado
- ✅ Seguindo melhores práticas

## Próximos Passos

1. Implementar CQRS completo
2. Adicionar event sourcing
3. Implementar saga pattern para operações distribuídas
4. Adicionar circuit breaker
5. Implementar feature toggles
6. Adicionar APM e distributed tracing
