---
inclusion: fileMatch
fileMatchPattern: "**/Infrastructure/Helper/**"
---

# Helpers

Em `src/Infrastructure/Helper`.

## EntityQueryHelper

Monta query Doctrine com filtros por campos configurados e paginação.

- Texto, nome, email, localização: `LIKE`
- Status: igualdade booleana
- Campos relacionais: aceita `{relation}` ou `{relation}Id`, filtra pelo id
- Demais: igualdade simples

## EntityFieldsHelper

Popula DTO configurável a partir de entidade Doctrine.

- Para relações: aceita classe DTO única ou mapa por nome de campo
- `setFieldsFromEntityData($configuration, $entity)`

## AttributeOutputHelper

Formata saída da API:

- Datas em `America/Sao_Paulo` como `d/m/Y H:i:s` ou `d/m/Y`
- Relações como objeto (deepFetch) ou `{campo}Id`
- `PASSWORDFIELD` nunca aparece na saída

## EntityBuilder / EntityListBuilder

Convertem DTOs configuráveis para arrays serializáveis.

## PasswordHashHelperTrait

- `password_hash()` para criar hash
- `password_verify()` para validar
- Usado no cadastro/edição de usuário e no login

## Auth/JwtAuthenticationHelperTrait

Valida `Authorization: Bearer <token>`:
- Estrutura, algoritmo HS256, issuer `AppFinancasNew`
- Assinatura com `APP_SECRET`
- Campos obrigatórios: `sub`, `email`, `exp`
- Expiração

## Auth/RecordAuthorizationHelperTrait

Autorização por registro:
- ADMIN pode tudo
- Usuário comum: próprio User, própria Wallet, registros financeiros da própria carteira
- Catálogos: defaults + próprios; edição/exclusão apenas dos próprios não default

## ActionManager Traits

Em `src/Infrastructure/Helper/ActionManager/`:
- Dispatch HTTP
- Leitura de payload/id
- Criação de resposta padronizada
- `ActionManagerDispatchTrait`: leitura cacheada de GET e invalidação após mutações

## Regras

- Não duplique helpers em controllers
- Use `EntityQueryHelper` para queries filtradas
- Use `AttributeOutputHelper` para formatação de saída
- Mantenha auth helpers como traits do ActionManager
