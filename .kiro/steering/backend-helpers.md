---
inclusion: fileMatch
fileMatchPattern: "**/Infrastructure/Helper/**"
---

# Helpers

Em `Backend/src/Infrastructure/Helper`.

## EntityQueryHelper

Filtros: texto/nome/email → LIKE, status → booleano, relações → {relation}Id, demais → igualdade.

## EntityFieldsHelper

Popula DTO configurável a partir de entidade Doctrine.

## AttributeOutputHelper

Datas em America/Sao_Paulo, relações como objeto ou {campo}Id, PASSWORDFIELD nunca na saída.

## Response Builders

ResponseBuilder, JsonResponseHandler, EntityBuilder, EntityListBuilder, SimpleDataPaginator.

## Auth Helpers

- `JwtAuthenticationHelperTrait`: valida Bearer JWT (HS256, APP_SECRET)
- `RecordAuthorizationHelperTrait`: ADMIN pode tudo, usuário comum apenas próprios
- `PasswordHashHelperTrait`: hash e verify
