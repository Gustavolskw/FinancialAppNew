---
inclusion: manual
---

# Complexity Tiers (Symfony)

Adapte o nível de detalhe automaticamente com base na complexidade do projeto.

## Simple

**Sinais**: Single bundle, CRUD básico, sem async.
**Exemplo**: Criar entidade + controller + validação básica.
**Abordagem**: Routes list/create/edit, Doctrine entities + forms, controllers limpos.

## Medium

**Sinais**: API Platform, DTOs, validação, services.
**Exemplo**: Resource + DTO + processor/provider + tests.
**Abordagem**: Resources + filters, DTOs + processors/providers, API versionada, cobertura de testes.

## Complex

**Sinais**: CQRS, Messenger, múltiplos bounded contexts.
**Exemplo**: Command/handler + async transport + domain services.
**Abordagem**: API + admin + public, Messenger, CQRS, escalabilidade, fronteiras de domínio claras.

## AppFinancasNew

O projeto atual está entre **Simple** e **Medium**:
- CRUD genérico próprio (não usa API Platform)
- DTOs configuráveis com Fields
- Autenticação JWT stateless
- Sem async/Messenger por enquanto
- Sem CQRS

Trate como **Medium** para decisões de arquitetura.
