---
description: Workflow completo de melhorias no backend Symfony com todos os agentes e skills
---

# Workflow de Melhorias no Backend

Este workflow aplica melhorias completas no backend Symfony usando todos os agentes especializados e skills disponíveis. Use quando precisar refatorar, otimizar ou evoluir o backend de forma abrangente.

## Pré-requisitos

- Backend rodando via Docker ou localmente
- Banco de dados PostgreSQL configurado
- Composer instalado
- PHP 8.4+

## Fase 1: Preparação e Análise

### 1. Ler documentação base do projeto

Leia os documentos fundamentais para entender o contexto completo:

```bash
# Documentos na raiz
cat /home/gustavo-luis/Documents/AppFinancasNew/AGENTS.md
cat /home/gustavo-luis/Documents/AppFinancasNew/docs/codex/project-context.md
cat /home/gustavo-luis/Documents/AppFinancasNew/docs/codex/agent-playbook.md
cat /home/gustavo-luis/Documents/AppFinancasNew/docs/codex/docker.md
cat /home/gustavo-luis/Documents/AppFinancasNew/docs/codex/skills.md
cat /home/gustavo-luis/Documents/AppFinancasNew/docs/codex/review-notes.md

# Documentos do backend
cat /home/gustavo-luis/Documents/AppFinancasNew/Backend/AGENTS.md
cat /home/gustavo-luis/Documents/AppFinancasNew/Backend/docs/codex/project-context.md
cat /home/gustavo-luis/Documents/AppFinancasNew/Backend/docs/codex/agent-playbook.md
cat /home/gustavo-luis/Documents/AppFinancasNew/Backend/docs/codex/skills.md
cat /home/gustavo-luis/Documents/AppFinancasNew/Backend/docs/codex/review-notes.md
```

### 2. Ler agentes especializados

```bash
# Agentes oficiais
cat /home/gustavo-luis/Documents/AppFinancasNew/.codex/agents/appfinancas-backend.toml
cat /home/gustavo-luis/Documents/AppFinancasNew/.agents/appfinancas-backend.md
cat /home/gustavo-luis/Documents/AppFinancasNew/agents/appfinancas-backend.md
```

### 3. Ler todas as Skills do backend

```bash
# Skill de Fields
cat /home/gustavo-luis/Documents/AppFinancasNew/skills/appfinancasnew-backend-fields/SKILL.md

# Skill de Configurations
cat /home/gustavo-luis/Documents/AppFinancasNew/skills/appfinancasnew-backend-entity-dtos/SKILL.md

# Skill de Actions
cat /home/gustavo-luis/Documents/AppFinancasNew/skills/appfinancasnew-backend-actions/SKILL.md

# Skill de Helpers
cat /home/gustavo-luis/Documents/AppFinancasNew/skills/appfinancasnew-backend-helpers/SKILL.md

# Skill do projeto completo
cat /home/gustavo-luis/Documents/AppFinancasNew/skills/appfinancasnew-project/SKILL.md
```

### 4. Ler Skills Symfony relevantes

Use as skills Symfony conforme a área de melhoria:

- **backend-specialist**: Agregação de todas as skills backend
- **symfony:doctrine-relations**: Melhorias em relações Doctrine
- **symfony:doctrine-migrations**: Melhorias em migrations
- **symfony:doctrine-batch-processing**: Otimização de processamento em lote
- **symfony:api-platform-resources**: Melhorias em recursos API Platform
- **symfony:api-platform-security**: Melhorias em segurança API
- **symfony:api-platform-serialization**: Melhorias em serialização
- **symfony:symfony-voters**: Melhorias em autorização
- **symfony:form-types-validation**: Melhorias em validação
- **symfony:tdd-with-phpunit**: Melhorias em testes
- **symfony:functional-tests**: Melhorias em testes funcionais
- **php-modernization**: Modernização PHP 8.4+

### 5. Analisar estado atual do backend

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Verificar estrutura
tree -L 3 src/

# Verificar rotas
php bin/console debug:router

# Verificar serviços
php bin/console debug:container

# Validar schema Doctrine
php bin/console doctrine:schema:validate

# Verificar migrations pendentes
php bin/console doctrine:migrations:status
```

## Fase 2: Melhorias em Fields (EntityAttributes)

### 6. Aplicar skill appfinancasnew-backend-fields

Invoque a skill para contexto:

```
/skill appfinancasnew-backend-fields
```

### 7. Analisar e melhorar Fields existentes

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Listar todos os Fields
find src/Infrastructure/DTO/EntityAttributes -name "*.php" -type f

# Analisar cada Field para melhorias:
# - Validações mais robustas
# - Enums bem definidos
# - Campos relacionais otimizados
# - Output formatado corretamente
# - Documentação PHPDoc completa
```

### 8. Criar novos Fields se necessário

Baseado na análise, crie Fields faltantes seguindo o padrão:

- Herdar de `FieldsAttribute`
- Usar `FieldTypeEnum` apropriado
- Definir validações com Symfony Validator
- Implementar `getOutputValue()` quando necessário
- Adicionar enums para campos com valores fixos

### 9. Validar Fields com PHPStan

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend
phpstan analyse src/Infrastructure/DTO/EntityAttributes --level=9
```

## Fase 3: Melhorias em Configurations

### 10. Aplicar skill appfinancasnew-backend-entity-dtos

```
/skill appfinancasnew-backend-entity-dtos
```

### 11. Analisar e melhorar Configurations existentes

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Listar todos os Configurations
find src/Infrastructure/DTO/Configuration -name "*DTO.php" -type f

# Para cada DTO, verificar:
# - configureFields() completo e correto
# - setFieldValues() hidratando corretamente
# - output() retornando estrutura adequada
# - setFieldsFromEntityData() mapeando todos os campos
# - Relações carregadas eficientemente
```

### 12. Otimizar hidratação e output

Melhorias específicas:

- Usar `EntityHydrationHelper` consistentemente
- Aplicar `AttributeOutputHelper` para output padronizado
- Evitar N+1 queries em relações
- Implementar lazy loading quando apropriado
- Adicionar cache de output quando relevante

### 13. Validar Configurations

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend
phpstan analyse src/Infrastructure/DTO/Configuration --level=9
composer test -- --filter=DTO
```

## Fase 4: Melhorias em Actions

### 14. Aplicar skill appfinancasnew-backend-actions

```
/skill appfinancasnew-backend-actions
```

### 15. Analisar ActionManager e Action base

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Analisar ActionManager
cat src/Infrastructure/Handler/Action/ActionManager.php

# Analisar Action base
cat src/Infrastructure/Handler/Action/Action.php

# Melhorias potenciais:
# - Validação de autorização mais granular
# - Tratamento de erros mais robusto
# - Logging de operações críticas
# - Eventos/hooks para extensibilidade
# - Transações Doctrine explícitas
```

### 16. Melhorar SpecificActions

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Listar todas as SpecificActions
find src/Infrastructure/Handler/Action/Specific -name "*.php" -type f

# Para cada SpecificAction:
# - Validar regras de negócio específicas
# - Otimizar queries customizadas
# - Adicionar validações extras
# - Implementar hooks beforeCreate/afterUpdate quando necessário
# - Garantir idempotência quando possível
```

### 17. Adicionar testes para Actions

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Criar testes unitários para cada Action
# Usar skill symfony:tdd-with-phpunit
```

```
/skill symfony:tdd-with-phpunit
```

```bash
# Rodar testes
composer test -- --filter=Action
```

## Fase 5: Melhorias em Helpers

### 18. Aplicar skill appfinancasnew-backend-helpers

```
/skill appfinancasnew-backend-helpers
```

### 19. Analisar e melhorar Helpers existentes

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Listar todos os Helpers
find src/Infrastructure/Helper -name "*.php" -type f

# Melhorias por Helper:
# - EntityHydrationHelper: otimizar hidratação, evitar N+1
# - AttributeOutputHelper: padronizar formatação de output
# - QueryFilterHelper: adicionar filtros complexos, validação
# - ResponseBuilder: enriquecer estrutura de resposta
# - PaginationHelper: otimizar queries de contagem
# - PasswordHashHelper: garantir algoritmo seguro
```

### 20. Criar Helpers faltantes

Baseado na análise, criar Helpers úteis:

- `CacheHelper`: para cache de queries frequentes
- `ValidationHelper`: validações customizadas reutilizáveis
- `DateHelper`: formatação e manipulação de datas
- `FileUploadHelper`: se houver upload de arquivos
- `NotificationHelper`: para notificações futuras

### 21. Validar Helpers

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend
phpstan analyse src/Infrastructure/Helper --level=9
composer test -- --filter=Helper
```

## Fase 6: Melhorias em Doctrine

### 22. Aplicar skills Doctrine

```
/skill symfony:doctrine-relations
/skill symfony:doctrine-migrations
/skill symfony:doctrine-batch-processing
```

### 23. Otimizar entidades Doctrine

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Analisar entidades
find src/Entity -name "*.php" -type f

# Melhorias:
# - Adicionar índices em colunas frequentemente consultadas
# - Otimizar fetch modes (LAZY, EAGER, EXTRA_LAZY)
# - Adicionar lifecycle callbacks quando necessário
# - Documentar relações complexas
# - Validar constraints de banco
```

### 24. Otimizar Repositories

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Analisar repositories
find src/Repository -name "*.php" -type f

# Adicionar queries customizadas:
# - Queries com JOIN otimizados
# - Queries com filtros complexos
# - Queries de agregação
# - Queries com paginação eficiente
```

### 25. Validar e criar migrations

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Validar schema
php bin/console doctrine:schema:validate

# Gerar migration se houver mudanças
php bin/console make:migration

# Revisar migration gerada
cat migrations/VersionXXXXXXXXXXXXXX.php

# Executar migration
php bin/console doctrine:migrations:migrate --no-interaction
```

## Fase 7: Melhorias em Segurança e Autorização

### 26. Aplicar skills de segurança

```
/skill symfony:api-platform-security
/skill symfony:symfony-voters
```

### 27. Revisar autenticação JWT

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Analisar JwtAuthenticationHelperTrait
cat src/Infrastructure/Helper/JwtAuthenticationHelperTrait.php

# Melhorias:
# - Validar expiração de token
# - Adicionar refresh token
# - Implementar blacklist de tokens
# - Adicionar rate limiting
# - Logging de tentativas de autenticação
```

### 28. Revisar autorização

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Analisar RecordAuthorizationHelperTrait
cat src/Infrastructure/Helper/RecordAuthorizationHelperTrait.php

# Melhorias:
# - Adicionar Voters para autorização granular
# - Implementar ACL para recursos complexos
# - Validar ownership em todas as operações
# - Adicionar roles customizadas
# - Logging de tentativas de acesso negado
```

### 29. Implementar Voters se necessário

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Criar Voters para entidades principais
# Exemplo: WalletVoter, TransactionVoter, etc.
# Seguir padrão Symfony de Voters
```

## Fase 8: Melhorias em Validação

### 30. Aplicar skill de validação

```
/skill symfony:form-types-validation
```

### 31. Revisar validações em DTOs

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Analisar DTOs de formulário
find src/Infrastructure/DTO -name "*FormDTO.php" -type f

# Melhorias:
# - Adicionar constraints Symfony Validator
# - Validações customizadas quando necessário
# - Mensagens de erro descritivas
# - Validações condicionais (grupos)
# - Validações cross-field
```

### 32. Criar validações customizadas

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Criar validators customizados em src/Validator
# Exemplo: UniqueWalletName, ValidTransactionAmount, etc.
```

## Fase 9: Melhorias em Testes

### 33. Aplicar skills de testes

```
/skill symfony:tdd-with-phpunit
/skill symfony:functional-tests
/skill symfony:test-doubles-mocking
```

### 34. Expandir cobertura de testes unitários

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Analisar cobertura atual
composer test -- --coverage-text

# Adicionar testes faltantes:
# - Testes para todos os Fields
# - Testes para todos os Configurations
# - Testes para todas as Actions
# - Testes para todos os Helpers
# - Testes para Validators customizados
```

### 35. Adicionar testes funcionais

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Criar testes funcionais para endpoints principais
# Usar WebTestCase do Symfony
# Testar fluxos completos:
# - Login e autenticação
# - CRUD de cada entidade
# - Autorização e permissões
# - Validações de negócio
# - Casos de erro
```

### 36. Rodar suite completa de testes

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend
composer test
```

## Fase 10: Modernização PHP

### 37. Aplicar skill de modernização PHP

```
/skill php-modernization
```

### 38. Aplicar features PHP 8.4+

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Melhorias de código:
# - Usar property hooks quando apropriado
# - Usar enums nativos para valores fixos
# - Usar readonly properties quando imutável
# - Usar union types e intersection types
# - Usar named arguments em chamadas complexas
# - Usar match expressions ao invés de switch
# - Usar nullsafe operator (?->)
# - Usar attributes ao invés de annotations
```

### 39. Aplicar PSR e PER-CS

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Rodar PHP-CS-Fixer
phpcs --standard=phpcs.xml.dist

# Aplicar correções automáticas se disponível
phpcbf --standard=phpcs.xml.dist
```

### 40. Rodar PHPStan nível máximo

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Aumentar nível do PHPStan gradualmente
phpstan analyse --configuration=phpstan.neon.dist --level=9 --no-progress
```

## Fase 11: Performance e Otimização

### 41. Aplicar skill de cache

```
/skill symfony:symfony-cache
```

### 42. Implementar cache estratégico

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Adicionar cache para:
# - Queries de leitura frequentes
# - Output de Configurations pesados
# - Resultados de cálculos complexos
# - Dados de configuração
# - Listas de enums e tipos

# Usar Symfony Cache Component
# Configurar adapters apropriados (Redis, APCu, etc.)
```

### 43. Otimizar queries N+1

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Identificar queries N+1 com Doctrine Profiler
# Adicionar joins apropriados
# Usar batch loading quando necessário
# Implementar DataLoader pattern se relevante
```

### 44. Adicionar índices de banco

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Analisar queries lentas
# Adicionar índices em:
# - Foreign keys
# - Colunas de filtro frequente
# - Colunas de ordenação
# - Colunas de busca

# Gerar migration com índices
php bin/console make:migration
```

## Fase 12: Documentação e API

### 45. Melhorar documentação de código

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Adicionar/melhorar PHPDoc em:
# - Todas as classes públicas
# - Todos os métodos públicos
# - Propriedades complexas
# - Parâmetros e retornos
# - Exceptions lançadas
```

### 46. Documentar API com OpenAPI

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Se usar API Platform, gerar documentação OpenAPI
# Caso contrário, adicionar NelmioApiDocBundle
# Documentar todos os endpoints
# Adicionar exemplos de request/response
```

### 47. Atualizar README e docs

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Atualizar documentação em docs/codex/
# Documentar novas features
# Atualizar diagramas se houver
# Documentar decisões arquiteturais
```

## Fase 13: Quality Gates

### 48. Rodar quality gate completo

// turbo
```bash
cd /home/gustavo-luis/Documents/AppFinancasNew
./scripts/quality-backend.sh
```

### 49. Corrigir issues encontrados

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Corrigir todos os issues reportados por:
# - PHPCS
# - PHPStan
# - PHPUnit
# - Doctrine schema validation
```

### 50. Validar em ambiente Docker

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew

# Subir stack completa
docker compose up --build -d

# Testar endpoints principais
curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"test123"}'

# Verificar logs
docker compose logs -f backend

# Parar containers
docker compose down
```

## Fase 14: Review e Documentação Final

### 51. Aplicar skill de review

```
/skill backend-review
```

### 52. Review completo do código

Revisar seguindo checklist:

- [ ] Arquitetura mantida consistente
- [ ] Separação de responsabilidades clara
- [ ] Código segue PSR e PER-CS
- [ ] Tipos declarados corretamente
- [ ] Validações robustas
- [ ] Autorização implementada
- [ ] Testes com boa cobertura
- [ ] Performance otimizada
- [ ] Documentação completa
- [ ] Quality gates passando

### 53. Atualizar review-notes.md

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew/Backend

# Documentar:
# - Melhorias implementadas
# - Decisões técnicas tomadas
# - Riscos identificados
# - Próximos passos recomendados
# - Débitos técnicos restantes

cat >> docs/codex/review-notes.md << 'EOF'

## Melhorias Implementadas - [DATA]

### Fields
- [listar melhorias]

### Configurations
- [listar melhorias]

### Actions
- [listar melhorias]

### Helpers
- [listar melhorias]

### Doctrine
- [listar melhorias]

### Segurança
- [listar melhorias]

### Testes
- [listar melhorias]

### Performance
- [listar melhorias]

### Próximos Passos
- [listar recomendações]

EOF
```

### 54. Commit das melhorias

```bash
cd /home/gustavo-luis/Documents/AppFinancasNew

# Adicionar arquivos modificados
git add Backend/

# Commit com mensagem descritiva
git commit -m "feat(backend): comprehensive improvements across all modules

- Enhanced Fields with better validations and enums
- Optimized Configurations hydration and output
- Improved Actions with better error handling
- Refactored Helpers for better reusability
- Optimized Doctrine queries and relations
- Enhanced security and authorization
- Expanded test coverage
- Applied PHP 8.4+ modernization
- Improved performance with caching
- Updated documentation

All quality gates passing."
```

## Conclusão

Este workflow aplicou melhorias abrangentes no backend usando:

- **Agentes**: appfinancas-backend (oficial e compilado)
- **Skills locais**: fields, entity-dtos, actions, helpers, project
- **Skills Symfony**: doctrine, api-platform, security, validation, testing
- **Skills gerais**: php-modernization, backend-specialist, backend-review

O backend agora está:
- ✅ Mais robusto e seguro
- ✅ Melhor testado
- ✅ Otimizado para performance
- ✅ Modernizado com PHP 8.4+
- ✅ Bem documentado
- ✅ Seguindo melhores práticas

## Próximos Passos Recomendados

1. Implementar monitoramento e observabilidade
2. Adicionar testes E2E com Panther/Playwright
3. Implementar CI/CD completo
4. Adicionar feature flags
5. Implementar event sourcing se relevante
6. Adicionar GraphQL se necessário
7. Implementar rate limiting por endpoint
8. Adicionar APM (Application Performance Monitoring)
