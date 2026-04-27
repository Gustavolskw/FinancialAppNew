# Notas De Avaliação Técnica

Este arquivo registra achados importantes para próximos agentes Codex. Ele não é uma lista de tarefas obrigatória, mas ajuda a evitar que o agente continue padrões problemáticos sem perceber.

## Arquitetura Atual

O projeto está construindo um CRUD genérico próprio sobre Symfony e Doctrine. A intenção é boa: controllers finos, configuração de campos por DTO, respostas padronizadas e hooks específicos por entidade.

Esse padrão deve ser preservado por enquanto, porque já existe bastante código apontando nessa direção. Mudanças grandes de arquitetura só devem ser feitas se o usuário pedir ou se uma implementação ficar bloqueada.

## Pontos Fortes

- Controllers delegam a lógica e ficam fáceis de ler.
- Existe separação entre entidade Doctrine, DTO de API, DTO de formulário e response builder.
- O fluxo de campos configuráveis reduz duplicação em CRUDs parecidos.
- Validação de senha está próxima da definição do campo.
- Hash de senha fica em hook específico de usuário.
- CORS, Doctrine, migrations e Docker já estão encaminhados.

## Riscos E Bugs Encontrados

### `PUT` Sem `id` Vira Criação

Arquivo: `src/Infrastructure/Handler/Action/Manager/ActionManager.php`

`handleUpdate()` chama `save()` quando o Form DTO não tem `id`. Isso combina com o nome `insertEdit`, mas deve ser documentado em API pública.

### Campos Relacionais Não São Aplicados Na Escrita

Arquivo: `src/Infrastructure/Handler/Action/Action.php`

`applyFieldsToEntity()` pula `RELATIONALFIELD`. Entidades como `Wallet`, `Expense` e `Entry` têm relações obrigatórias, então criação/edição delas precisa de lógica adicional.

### `User` Não Implementa Security Interfaces

Arquivo: `src/Entity/User.php`

Security Bundle existe, mas `User` ainda não implementa `UserInterface`/`PasswordAuthenticatedUserInterface`. Não há login real.

### Login JWT Ainda Não Protege Rotas

Arquivos: `src/Controller/AccessControlController.php`, `src/Infrastructure/Handler/Action/PrimaryAction/AccessControlAction.php`

O endpoint `/login` gera JWT stateless assinado com `APP_SECRET`, mas ainda não existe firewall/autenticador que valide o bearer token nas demais rotas. O `/logoff` apenas confirma o encerramento para o cliente descartar o token; revogação server-side exigirá blacklist/persistência de sessões ou tokens opacos armazenados.

### Falta De Testes Automatizados

Não há `phpunit.xml`, pasta `tests` ou scripts de teste no `composer.json`. Mudanças devem ser verificadas com `php -l` e comandos Symfony/Doctrine quando possível.

## Recomendações Para Próximas Iterações

- Criar um padrão para escrita de relações por id.
- Adicionar testes de unidade para Fields/Query/Response e testes funcionais para `UserController`.
- Padronizar `declare(strict_types=1);` em arquivos novos.
- Se autenticação for prioridade, implementar `UserInterface`, password hasher do Symfony e firewall real.
