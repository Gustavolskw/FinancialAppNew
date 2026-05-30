---
name: backend-helpers
description: >
  Helpers em src/Infrastructure/Helper.
  Use quando precisar montar queries com filtros e paginação, formatar saída da API,
  trabalhar com autenticação JWT, autorização por registro, ou usar response builders.
---

# Skill: Backend Helpers

Helpers em `src/Infrastructure/Helper`.

## EntityQueryHelper

Filtros: texto/nome/email → LIKE, status → booleano, relações → {relation}Id, demais → igualdade.

## EntityFieldsHelper

```php
EntityFieldsHelper::setFieldsFromEntityData($configuration, $entity);
```

## AttributeOutputHelper

Datas em America/Sao_Paulo, relações como objeto ou {campo}Id, PASSWORDFIELD nunca na saída.

## Response Builders

ResponseBuilder, JsonResponseHandler, EntityBuilder, EntityListBuilder, SimpleDataPaginator.

## Auth Helpers

- `JwtAuthenticationHelperTrait`: valida Bearer JWT (HS256, APP_SECRET)
- `RecordAuthorizationHelperTrait`: ADMIN pode tudo, usuário comum apenas próprios
- `PasswordHashHelperTrait`: hash e verify

## Regras

- Não duplique helpers em controllers
- Use EntityQueryHelper para queries filtradas
- Use AttributeOutputHelper para formatação de saída
