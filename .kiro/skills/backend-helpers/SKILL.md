---
name: backend-helpers
description: >
  Helpers em Backend/src/Infrastructure/Helper.
  Use quando precisar montar queries com filtros e paginação, formatar saída da API,
  trabalhar com autenticação JWT, autorização por registro, ou usar response builders.
---

# Skill: Backend Helpers

Helpers em `Backend/src/Infrastructure/Helper`.

## Escopo

Use quando precisar:
- Montar queries com filtros e paginação
- Formatar saída da API
- Trabalhar com autenticação JWT
- Trabalhar com autorização por registro
- Usar response builders e paginação

## EntityQueryHelper

Filtros por tipo de campo:
- Texto/nome/email/localização: `LIKE`
- Status: igualdade booleana
- Campos relacionais: `{relation}` ou `{relation}Id`
- Demais: igualdade simples

## EntityFieldsHelper

```php
EntityFieldsHelper::setFieldsFromEntityData($configuration, $entity);
```

## AttributeOutputHelper

- Datas em `America/Sao_Paulo` como `d/m/Y H:i:s`
- Relações como objeto (deepFetch) ou `{campo}Id`
- PASSWORDFIELD nunca na saída

## Response Builders

- `ResponseBuilder::build($message, $statusCode)` → `addData()` → serializa
- `JsonResponseHandler`, `EntityBuilder`, `EntityListBuilder`
- `SimpleDataPaginator`: totalItems, perPage, currentPage, totalPages, etc.

## Auth Helpers

- `JwtAuthenticationHelperTrait`: valida Bearer JWT (HS256, APP_SECRET, issuer, exp)
- `RecordAuthorizationHelperTrait`: ADMIN pode tudo, usuário comum apenas próprios
- `PasswordHashHelperTrait`: hash e verify

## Regras

- Não duplique helpers em controllers
- Use EntityQueryHelper para queries filtradas
- Use AttributeOutputHelper para formatação de saída
