# Backend

API Symfony/PHP do AppFinancasNew.

## Responsabilidade

O backend concentra regras de domínio, persistência, autenticação, autorização e formato das respostas da API. O frontend deve consumir esses contratos sem duplicar regra de negócio.

## Stack

- PHP 8.4
- Symfony 8
- Doctrine ORM/Migrations
- PostgreSQL
- PHPUnit
- PHPCS e PHPStan no quality gate do GitHub Actions

## Arquitetura

O fluxo principal segue:

```text
Controller fino -> ActionManager -> Action -> EntityDTO configurável -> ResponseBuilder
```

Pontos principais:

- Controllers recebem payload/query DTOs e delegam.
- `ActionManager` autentica, autoriza e escolhe o fluxo CRUD.
- `Action` executa validação, persistência e hooks.
- EntityDTOs configuram fields, output e relações.
- Helpers centralizam saída, filtros, paginação e autenticação.

## Ambiente

Crie o `.env` a partir do exemplo:

```bash
cp .env.example .env
```

Ou, pela raiz do projeto:

```bash
./scripts/setup-env.sh
```

No Docker, a `DATABASE_URL` é sobrescrita pelo `docker-compose.yml` com base na `.env` da raiz.

## Comandos

Instalar dependências:

```bash
composer install
```

Rodar testes:

```bash
composer test
```

Ver rotas:

```bash
php bin/console debug:router
```
