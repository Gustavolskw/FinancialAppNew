# Contexto Do Projeto

## Visão Geral

Backend Symfony para um aplicativo de finanças pessoais. O domínio modelado inclui usuários, carteiras, transações, despesas, entradas, tipos de despesa, tipos de entrada e métodos de pagamento.

O projeto está em fase inicial/intermediária: as entidades e uma migration principal existem, o CRUD genérico de usuário está parcialmente implementado, e a camada `Infrastructure` define um framework interno para repetir o mesmo padrão em outras entidades.

## Stack E Frameworks

- PHP `>=8.4`
- Symfony `8.0.*`
- Doctrine ORM `^3.6`
- Doctrine Migrations Bundle `^3.0`
- Doctrine Bundle `^3.2`
- Nelmio CORS Bundle `^2.6`
- Symfony Security Bundle
- Symfony Validator
- Symfony Serializer, PropertyAccess e PropertyInfo
- Symfony MakerBundle em dev
- Docker com `php:8.4-fpm`, Nginx e Xdebug
- PostgreSQL esperado pela configuração DBAL/Doctrine

## Pastas

Antes de alterar qualquer módulo coberto por Skill local, consulte também [docs/codex/skills.md](skills.md).

### `src/Controller`

Contém endpoints HTTP. Hoje existem `UserController` para CRUD de usuário e `AccessControlController` para controle de acesso.

Rotas atuais de usuário:

- `GET /user`: lista usuários com paginação e filtros de query.
- `GET /user/{id}`: visualiza um usuário.
- `POST /user`: cria usuário.
- `POST /user/admin`: cria usuário administrador; rota bloqueada para não administradores e única rota permitida para criação de admins.
- `PUT /user`: cria ou atualiza conforme presença de `id`.
- `PATCH /user`: atualiza parcialmente conforme `id`.
- `PATCH /user/{id}/status`: altera o status do usuário.

Usuário não expõe delete físico; a desativação deve ser feita por status.

Rotas atuais de carteira:

- `GET /wallet`: lista carteiras.
- `GET /wallet/user/{userId}`: lista carteiras vinculadas ao usuário informado, preservando query params como `page` e `perPage` e aplicando filtro relacional por `userId`.
- `GET /wallet/{id}`: visualiza uma carteira.
- `POST /wallet`: cria carteira.
- `PUT /wallet`: cria ou atualiza conforme presença de `id`.
- `PATCH /wallet`: atualiza parcialmente conforme `id`.
- `PATCH /wallet/{id}/status`: altera o status da carteira.

Carteira não expõe delete físico; a desativação deve ser feita por status.

Rotas atuais de transação:

- `GET /transaction`: lista transações.
- `GET /transaction/wallet/{walletId}`: lista transações vinculadas à carteira informada, preservando query params como `page` e `perPage` e aplicando filtro relacional por `walletId`.
- `GET /transaction/{id}`: visualiza uma transação.
- `POST /transaction`: cria transação.
- `PUT /transaction`: cria ou atualiza conforme presença de `id`.
- `PATCH /transaction`: atualiza parcialmente conforme `id`.
- `DELETE /transaction/{id}`: exclui uma transação.

Rotas CRUD com delete físico:

- `/entry-type`
- `/expense-type`
- `/payment-method`
- `/entry`
- `/expense`

Rotas atuais de controle de acesso:

- `POST /login`: autentica por email e senha, retorna token JWT e dados básicos do usuário.
- `POST /logoff`: confirma o encerramento de sessão no padrão stateless; o cliente deve descartar o token.

### `src/Entity`

Entidades Doctrine:

- `User`: nome, email, senha, status, role de acesso, timestamps e relação one-to-one com `Wallet`.
- `Wallet`: título, descrição, status, timestamps, relação one-to-one obrigatória com `User` e relação one-to-many inversa com `Transaction`.
- `Transaction`: valor decimal, local, descrição, data, mês, ano e relações one-to-one com despesa ou entrada.
- `Expense`: relaciona uma transação a tipo de despesa, método de pagamento e parcelas.
- `Entry`: relaciona uma transação a tipo de entrada.
- `ExpenseType`: catálogo de tipos de despesa.
- `EntryType`: catálogo de tipos de entrada.
- `PaymentMethod`: catálogo de métodos de pagamento.

### `src/Repository`

Repositories Doctrine gerados, sem lógica customizada. Use-os para consultas específicas quando `EntityQueryHelper` não for suficiente.

### `src/Infrastructure/DTO/EntityDto`

Skill obrigatória antes de alterar esta área: [appfinancasnew-backend-entity-dtos](../../skills/appfinancasnew-backend-entity-dtos/SKILL.md).

Camada central de configuração de entidade para API.

- `ConfigurableEntity`: guarda `FieldsAttributeInterface`, repository e entity manager; resolve query builder com `EntityQueryHelper`; fornece `BaseSpecificAction` por padrão; também fornece os defaults genéricos de `output()` via `AttributeOutputHelper` e `setFieldValues()` por loop sobre os campos configurados.
- `MainConfigurableEntity`: adiciona `createdAt` e `updatedAt`.
- `User`: define campos de saída/entrada, validação de senha, role via `RolesEnum`, relação com `Wallet`, termos de resposta `users`/`user` e `UserSpecificAction`.
- `Wallet`: define campos de carteira e relação com usuário; a coleção inversa de transações não é exposta no EntityDTO enquanto não houver field/output próprio para coleções.
- `EntryType`, `ExpenseType` e `PaymentMethod`: definem catálogos/tipos do domínio financeiro.
- `Entry` e `Expense`: definem objetos específicos vinculados a transações e catálogos.
- `Transaction`: define a transação geral, com valor, local, descrição, data, mês, ano, relação obrigatória com carteira e relações opcionais com despesa ou entrada.

Para novas APIs, siga esse padrão: cada entidade Doctrine exposta deve ter um DTO configurável com `ENTITYCLASS`, `LISTDATATERM`, `SINGLEDATATERM`, `configureFields()`, `setFieldsFromEntityData()`, `getEntityClass()` e `build()`. Use os defaults herdados de `ConfigurableEntity` para `output()` e `setFieldValues()`, sobrescrevendo apenas quando houver uma regra específica.

### `src/Infrastructure/DTO/EntityAttributes`

Skill obrigatória antes de alterar esta área: [appfinancasnew-backend-fields](../../skills/appfinancasnew-backend-fields/SKILL.md).

Sistema de metadados de campos:

- `FieldTypeEnum`: tipos lógicos (`IDFIELD`, `NAMEFIELD`, `EMAILFIELD`, `PASSWORDFIELD`, `RELATIONALFIELD`, `ENUMFIELD`, etc.) e regras simples de tipo/tamanho.
- `Enum/RolesEnum`: define os níveis de acesso `USER = 1` e `ADM = 2`, persistidos como inteiro em `User.role` e retornados na API como `User`/`Admin`.
- `Enum/Interface/EntityFieldEnumInterface`: convenção para enums usados por `EnumFieldDto`; exige `match(int)`, `value()` e `name()`.
- `FieldsAttribute`: coleção de campos com factories fluentes (`setIdField`, `setNameField`, `setTextField`, `setPassword`, `setEnumField`, `setRelationalField`, etc.).
- `Fields/*`: DTOs de campo com validação, valor, getter da entidade e tipo.
- `EnumFieldDto`: recebe a classe de um enum via `setEnumField(..., EnumClass::class)`, valida por reflection se é enum backed e implementa `EntityFieldEnumInterface`, usa `match(int)` para resolver a instância do enum, recebe e persiste inteiro via `getRawValue()`, e a saída da API usa `name()`.

Campos obrigatórios e validações extras devem ser declarados em `configureFields()`.

### `src/Infrastructure/DTO/Forms`

DTOs de payload HTTP, usados com `#[MapRequestPayload]`.

- `FormDtoInterface`: marcador.
- `StatusFormDto`: payload de status.
- `LoginFormDto`: payload de login com `email` e `password`.
- `UserPostFormDto`: criação de usuário comum sem `role` no payload; o backend define `role = USER` no hook específico.
- `UserAdminPostFormDto`: criação de usuário administrador pela rota `POST /user/admin`; força `role = ADM`.
- `UserInsertEditFormDto`: PUT com `id` opcional, incluindo `role` opcional.
- `UserEditFormDto`: PATCH com `id` opcional, incluindo `role` opcional.
- `UserFormDto`: DTO genérico de usuário ainda não usado pelo controller atual.
- Form DTOs por entidade em `src/Infrastructure/DTO/Forms/{Entity}` para `Wallet`, `EntryType`, `ExpenseType`, `PaymentMethod`, `Entry`, `Expense` e `Transaction`.

### `src/Infrastructure/DTO/Params`

DTOs de query string, usados com `#[MapQueryString]`.

- `PaginatorQueryParamsDto`: `page` e `perPage`.
- `EntityQueryParamsDto`: filtros de usuário (`name`, `email`, `status`) mais paginação.
- `QueryParams`: separa parâmetros de paginação de filtros.
- `ParamDto`: par nome/valor.

### `src/Infrastructure/Handler/Action`

Skill obrigatória antes de alterar esta área: [appfinancasnew-backend-actions](../../skills/appfinancasnew-backend-actions/SKILL.md).

Orquestra CRUD.

- `ActionManager`: permite `POST /user` público para cadastro normal, valida Bearer JWT nas demais rotas CRUD/status, aplica autorização por dono/ADMIN e escolhe fluxo conforme método HTTP.
- `Action`: implementa `listView`, `view`, `save`, `edit`, `delete`, `status`.
- `PrimaryAction/AccessControlAction`: implementa ações primárias de autenticação (`login` e `logoff`) fora do CRUD genérico.
- `Specific`: hooks específicos por entidade antes/depois de salvar, atualizar, deletar ou trocar status.

Estado atual:

- `save` recebe campos já preenchidos pelo `ActionManager`, valida fields, executa `preActionValidation`, executa `specificAction` somente no fluxo de criação, aplica campos na entidade, executa `preSave`, reaplica fields que hooks possam ter alterado, persiste/flush e executa `afterAction`.
- `edit` recebe campos já preenchidos pelo `ActionManager`, valida apenas campos informados, executa `preActionValidation` e `beforeUpdate`, aplica campos na entidade pelo próprio `Action.php`, executa `preUpdate`, reaplica fields que hooks possam ter alterado, faz flush e depois executa `afterUpdate` dentro de transação. O update não chama `specificAction`.
- `preActionValidation` no `BaseSpecificAction` valida ids de `RELATIONALFIELD` informados antes de create/update prosseguir.
- `Action::applyFieldsToEntity()` resolve `RELATIONALFIELD` por id e aplica a entidade relacionada usando o setter derivado do getter configurado no field.
- `UserSpecificAction::afterAction()` cria automaticamente uma carteira padrão ativa para todo usuário recém-criado.
- `TransactionSpecificAction::beforeDelete()` remove `Entry` ou `Expense` dependente antes de excluir uma `Transaction`.
- `Action::listView()` aceita uma restrição opcional de `QueryBuilder` para limitar listagens ao usuário autenticado quando o caller não é ADMIN.
- `listView` aplica filtros, pagina, monta analytics simples e resposta.
- `delete` localiza por id, executa hooks `beforeDelete`/`afterDelete`, remove e faz flush.
- `status` localiza por id, valida campo `status`, executa hooks `beforeChangeStatus`/`afterChangeStatus`, chama `setStatus()` e faz flush.
- Hooks de delete/status recebem o DTO configurável preenchido com os dados atuais da entidade; em status, o campo `status` já contém o novo valor solicitado.

### `src/Infrastructure/Handler/Response`

Padroniza resposta JSON.

- `JsonResponseHandler`: transforma `JsonSerializable` em `JsonResponse` usando o `statusCode` serializado como status HTTP.
- `ResponseTest`: helper legado de resposta simples.

### `src/Infrastructure/DTO/Response`

Builder de resposta:

- `ResponseBuilder::build($message, $statusCode)`
- `addData($title, EntityClassCollection $data)`
- serializa para `message`, `statusCode`, `data`.
- `AuthSessionDataDto`: estrutura o retorno de autenticação com token, tipo, expiração e dados básicos do usuário.

### `src/Infrastructure/Handler/Paginator`

Pagina resultados em memória depois da query paginada. `SimpleDataPaginator` gera:

- `totalItems`
- `mappedItems`
- `perPage`
- `totalPages`
- `previousPage`
- `currentPage`
- `nextPage`
- `lastPage`

Quando `Action::listView()` aplica filtros ou restrição de segurança, o paginator deve receber o total da query filtrada para não expor contagem global de registros de outros usuários.

### `src/Infrastructure/Handler/Analytics`

Analytics simples sobre os DTOs retornados:

- contagem de itens
- percentual por campo
- soma por campo

Hoje `listView` usa apenas `countAnalyses()`.

### `src/Infrastructure/Helper`

Skill obrigatória antes de alterar esta área: [appfinancasnew-backend-helpers](../../skills/appfinancasnew-backend-helpers/SKILL.md).

Helpers para consulta, output e resposta:

- `EntityQueryHelper`: monta query Doctrine com filtros por campos configurados e paginação; também aceita filtros por relação via `{relation}` ou `{relation}Id`, usando o getter configurado em `setRelationalField()` para resolver a propriedade Doctrine.
- `EntityFieldsHelper`: popula DTO configurável a partir de entidade Doctrine; para relações, aceita uma classe DTO única ou um mapa por nome de campo quando há múltiplas relações.
- `AttributeOutputHelper`: formata saída, datas em `America/Sao_Paulo` e relações como objeto ou `{campo}Id`.
- `EntityBuilder`/`EntityListBuilder`: convertem DTOs para arrays.
- `PasswordHashHelperTrait`: `password_hash` e `password_verify`; usado no hash de cadastro/edição de usuário e na verificação do login.
- `Auth/JwtAuthenticationHelperTrait`: valida o header `Authorization: Bearer <token>` emitido por `/login`, confirmando assinatura HS256 com `APP_SECRET`, issuer, payload obrigatório e expiração.
- `Auth/RecordAuthorizationHelperTrait`: valida se o usuário autenticado pode acessar, criar, alterar, excluir ou mudar status do registro; ADMIN pode tudo, usuário comum fica restrito ao próprio usuário, à própria carteira e aos registros financeiros vinculados à carteira.

### `config`

Configuração Symfony:

- Rotas por atributos em controllers.
- Serviços com autowire/autoconfigure para `App\`.
- Doctrine com mappings por atributos em `src/Entity`.
- CORS aceitando métodos CRUD e headers `Content-Type`/`Authorization`.
- Security Bundle instalado, mas ainda sem firewall/autenticador próprio; as rotas CRUD/status que passam pelo `ActionManager` validam o bearer token com `JwtAuthenticationHelperTrait` e autorização de registro com `RecordAuthorizationHelperTrait`.
- `APP_SECRET` é usado para assinar o JWT gerado por `/login` e validar o bearer token nas rotas protegidas; precisa estar configurado no ambiente.

### `migrations`

Migration principal cria tabelas do domínio financeiro. Atualize migrations quando alterar entidades.

### `Dockerfile`, `docker/nginx`, `start.sh`

Container PHP-FPM + Nginx + Xdebug. O build copia o projeto, instala dependências e executa Nginx em foreground com PHP-FPM em background.

## Fluxo De CRUD Atual

Antes de alterar este fluxo, leia [appfinancasnew-backend-actions](../../skills/appfinancasnew-backend-actions/SKILL.md), [appfinancasnew-backend-entity-dtos](../../skills/appfinancasnew-backend-entity-dtos/SKILL.md) e, quando houver mudança em campos, [appfinancasnew-backend-fields](../../skills/appfinancasnew-backend-fields/SKILL.md).

Criar usuário:

1. `POST /user` é público, sem Bearer token, e recebe `UserPostFormDto`.
2. `ActionManager::handle()` chama `handleSave()`.
3. `User::setFieldValues()` copia propriedades do DTO para os campos configurados.
4. `Action::save()` valida campos.
5. `Action::save()` executa `preActionValidation()` e `specificAction()` do `SpecificAction`.
6. `Action::applyFieldsToEntity()` aplica setters e timestamps.
7. `UserSpecificAction::preSave()` aplica hash na senha.
8. `Action::applyFieldsToEntity()` reaplica os fields alterados pelo hook antes do flush.
9. Doctrine persiste e faz flush.
10. `afterAction()` roda depois do flush dentro de transação.
11. Resposta vem com `data.user`.

Listar usuários:

1. `GET /user` recebe `EntityQueryParamsDto`.
2. `QueryParams::fromArray()` separa paginação e filtros.
3. `EntityQueryHelper::buildSearchQuery()` cria query com `LIKE` para texto/email/nome e igualdade para status/outros.
4. Resultado vira DTO configurável.
5. Resposta vem com `data.users`, `data.pagination` e `data.analytics`.

Login:

1. `POST /login` recebe `LoginFormDto`.
2. `AccessControlController` cria o DTO configurável com `User::build($entityManager)`.
3. `AccessControlAction::build()` recebe o `BaseEntityClassInterface`, seguindo o padrão de build usado em `Action`.
4. `AccessControlAction::login()` busca o usuário por `email`.
5. A senha enviada é comparada com o hash salvo usando `PasswordHashHelperTrait::passwordMatches()`.
6. Usuário inexistente ou senha inválida retornam `401`; usuário inativo retorna `403`.
7. A action gera JWT HS256 stateless com `APP_SECRET`, `iat`, `exp`, `jti`, `sub`, `email` e `role`.
8. Resposta vem com `data.auth`, contendo token, tipo `Bearer`, expiração e uma fração do usuário (`id`, `name`, `email`, `role`, `status`).

Logoff:

1. `POST /logoff` chama `AccessControlAction::logoff()`.
2. Como o token atual é stateless e não há blacklist/persistência de sessão, o endpoint apenas confirma o encerramento para o cliente descartar o token.

Rotas protegidas por `ActionManager`:

1. Controllers CRUD repassam o `Request` para `ActionManager::handle()` ou `handleStatus()`.
2. `POST /user` normal é a única rota pública do CRUD para cadastro de usuário comum; não aceita `role` no payload e usa default `USER`.
3. Nas demais rotas, antes de criar `Action::build(...)`, o `ActionManager` chama `JwtAuthenticationHelperTrait::authenticateRequest()`.
4. A trait exige `Authorization: Bearer <token>` e valida estrutura, algoritmo `HS256`, issuer `AppFinancasNew`, assinatura com `APP_SECRET`, campos obrigatórios (`sub`, `email`, `exp`) e expiração.
5. Falhas retornam resposta padronizada com `401`; ausência de `APP_SECRET` retorna `500`.
6. Depois da autenticação, o `ActionManager` chama `RecordAuthorizationHelperTrait::authorizeRecordAccess()` para bloquear acesso fora do dono do registro.
7. ADMIN (`RolesEnum::ADM`) pode executar ações administrativas, mas criação de outro admin só é permitida pela rota especial `POST /user/admin`; criação normal de usuário não aceita `role` no payload e sempre cai no default `USER`.
8. Usuário comum só pode operar o próprio `User`, a própria `Wallet` e `Transaction`/`Entry`/`Expense` ligadas à própria carteira.
9. Cadastros globais (`EntryType`, `ExpenseType`, `PaymentMethod`) são leitura para usuários autenticados, mas escrita apenas para ADMIN.
10. Listagens de `User`, `Wallet`, `Transaction`, `Entry` e `Expense` recebem uma restrição de `QueryBuilder` para retornar apenas registros do usuário comum autenticado.
11. `/login` continua público para emitir token; `/logoff` permanece stateless e fora do CRUD genérico.

## Convenções De Saída

- Datas são formatadas como `d/m/Y H:i:s` ou `d/m/Y`.
- Timezone de saída: `America/Sao_Paulo`.
- Relações podem sair como objeto quando `deepFetch=true` ou como `{relationName}Id` quando não há deep fetch.
- `LISTDATATERM` e `SINGLEDATATERM` controlam o nome das chaves em `data`.
- O retorno de login usa `data.auth` e não expõe a entidade Doctrine completa nem a senha.

## Funcionalidades Implementadas

- CRUD parcial de usuário.
- Validação de senha forte no DTO configurável de usuário.
- Hash de senha antes de salvar/atualizar usuário.
- Role de acesso do usuário com `RolesEnum` (`USER = 1`, `ADM = 2`), default `USER` na criação quando não informado e saída como `User`/`Admin`.
- Controle de acesso inicial com `POST /login` e `POST /logoff`.
- Geração de JWT stateless assinado com `APP_SECRET` no login.
- Proteção das rotas CRUD/status que passam pelo `ActionManager` por Bearer JWT stateless e autorização por dono/ADMIN.
- Listagem paginada e filtrada de usuário.
- Resposta JSON padronizada.
- EntityDTOs configuráveis para `EntryType`, `ExpenseType`, `PaymentMethod`, `Entry`, `Expense` e `Transaction`, criados na ordem de dependência do domínio.
- Controllers e Form DTOs para `Wallet`, `EntryType`, `ExpenseType`, `PaymentMethod`, `Entry`, `Expense` e `Transaction`.
- Entidades financeiras principais modeladas no Doctrine.
- Docker básico para PHP-FPM/Nginx/Xdebug.

## Funcionalidades Ainda A Completar

- Migração para firewall/autenticador bearer token nativo do Symfony, se o projeto decidir usar o Security Bundle completo.
- Revogação server-side de tokens ou estratégia de sessão se o logoff precisar invalidar tokens no backend.
- Testes automatizados.
- Tratamento centralizado de erros.
- Field/output próprio para coleções inversas (`OneToMany`), como `Wallet.walletTransactions`.
