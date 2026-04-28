# Instruções Para Agentes Codex

Este repositório é o backend do AppFinancasNew. Ele usa PHP 8.4, Symfony 8, Doctrine ORM/Migrations, Nelmio CORS, Validator, Serializer/PropertyInfo e Security Bundle. O banco esperado pelo Doctrine é PostgreSQL via `DATABASE_URL`.

Antes de alterar código, leia também:

- [docs/codex/project-context.md](docs/codex/project-context.md)
- [docs/codex/agent-playbook.md](docs/codex/agent-playbook.md)
- [docs/codex/skills.md](docs/codex/skills.md)
- [docs/codex/review-notes.md](docs/codex/review-notes.md)

Quando a tarefa tocar nos diretórios abaixo, leia também a Skill local especializada antes de editar:

- `src/Infrastructure/DTO/EntityAttributes`: [skills/appfinancasnew-backend-fields/SKILL.md](skills/appfinancasnew-backend-fields/SKILL.md)
- `src/Infrastructure/DTO/EntityDto`: [skills/appfinancasnew-backend-entity-dtos/SKILL.md](skills/appfinancasnew-backend-entity-dtos/SKILL.md)
- `src/Infrastructure/Handler/Action`: [skills/appfinancasnew-backend-actions/SKILL.md](skills/appfinancasnew-backend-actions/SKILL.md)
- `src/Infrastructure/Helper`: [skills/appfinancasnew-backend-helpers/SKILL.md](skills/appfinancasnew-backend-helpers/SKILL.md)

## Como O Projeto Está Organizado

- `src/Controller`: controllers HTTP. Hoje existe `UserController`, que delega a lógica para `ActionManager`.
- `src/Entity`: entidades Doctrine do domínio financeiro: `User`, `Wallet`, `Transaction`, `Expense`, `Entry`, `ExpenseType`, `EntryType`, `PaymentMethod`.
- `src/Repository`: repositories Doctrine gerados pelo MakerBundle, ainda sem queries customizadas.
- `src/Infrastructure`: camada própria da aplicação, com DTOs, handlers, helpers, paginação, analytics e builder de resposta.
- `skills`: Skills locais de contexto para agentes Codex; use-as como documentação operacional antes de alterar os módulos cobertos.
- `config`: configuração Symfony, Doctrine, CORS, Security, rotas por atributos e serviços autowired.
- `migrations`: migrations Doctrine.
- `docker`: Nginx para Symfony dentro do container.
- `public`: front controller do Symfony.
- `bin`: console Symfony.
- `var`, `vendor`, `.idea`, `.git`: artefatos locais/ferramentas; não tratar como código de negócio.

## Padrões Obrigatórios

- Mantenha `declare(strict_types=1);` em arquivos novos de PHP quando fizer sentido, especialmente controllers e código novo.
- Use namespace `App\...` conforme PSR-4 de `composer.json`.
- Controllers devem continuar finos: receber `Request`, DTOs via `MapRequestPayload`/`MapQueryString`, `EntityManagerInterface`, e delegar para `ActionManager`.
- A lógica genérica de CRUD fica em `src/Infrastructure/Handler/Action/Action.php`.
- Regras específicas por entidade ficam em classes de `src/Infrastructure/Handler/Action/Specific`.
- A definição de campos, validações, output e vínculo com entidade Doctrine fica em DTOs de `src/Infrastructure/DTO/EntityDto`.
- Não exponha entidade Doctrine diretamente em JSON. Use `EntityBuilder`, `EntityListBuilder`, `AttributeOutputHelper` e `ResponseBuilder`.
- Para campos comuns, use `FieldsAttribute` e os tipos de `FieldTypeEnum`.
- Para senha de usuário, preserve hash em `UserSpecificAction` usando `PasswordHashHelperTrait`.
- Não coloque lógica de banco nos controllers.
- Não edite `vendor/`, `var/`, `.idea/` ou arquivos gerados, salvo quando explicitamente pedido.

## Fluxo Atual De Uma Requisição

1. Controller recebe a rota e mapeia payload/query para DTO.
2. Controller cria o DTO configurável da entidade com `User::build($entityManager)`.
3. `ActionManager` escolhe a ação conforme método HTTP.
4. `Action` valida campos, aplica valores na entidade Doctrine, persiste/atualiza e monta resposta.
5. DTO configurável converte entidade para output via `setFieldsFromEntityData()` e `output()`.
6. `JsonResponseHandler` retorna `JsonResponse` com formato padronizado.

Formato geral de resposta:

```json
{
  "message": "Sucesso!",
  "statusCode": 200,
  "data": {}
}
```

## Comandos Úteis

- Instalar dependências: `composer install`
- Limpar cache: `php bin/console cache:clear`
- Ver rotas: `php bin/console debug:router`
- Validar schema Doctrine: `php bin/console doctrine:schema:validate`
- Gerar migration: `php bin/console make:migration`
- Executar migrations: `php bin/console doctrine:migrations:migrate`

Atualmente não há `phpunit.xml`, suite de testes ou ferramenta de lint configurada no repositório.

## Cuidados Ao Trabalhar

- O worktree pode estar sujo. Nunca reverta alterações existentes sem pedido explícito.
- Prefira mudanças pequenas e alinhadas com a arquitetura atual.
- Ao adicionar uma nova entidade exposta por API, crie também o DTO configurável, Form DTOs, controller e SpecificAction quando houver regra específica.
- Antes de finalizar mudanças de backend, ao menos rode `php -l` nos arquivos alterados. Quando possível, rode comandos Symfony/Doctrine relevantes.
- Documente qualquer limitação encontrada em `docs/codex/review-notes.md` se ela afetar próximas tarefas.
