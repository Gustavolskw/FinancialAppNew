# Notas De Avaliação Técnica

Este arquivo registra achados importantes para próximos agentes Codex. Ele não é uma lista de tarefas obrigatória, mas ajuda a evitar que o agente continue padrões problemáticos sem perceber.

Antes de mexer em um módulo coberto por Skill local, leia [docs/codex/skills.md](skills.md). Se um risco técnico deste arquivo virar regra durável após correção, atualize também a Skill correspondente em `skills/`.

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

### `User` Não Implementa Security Interfaces

Arquivo: `src/Entity/User.php`

Security Bundle existe, mas `User` ainda não implementa `UserInterface`/`PasswordAuthenticatedUserInterface`. A proteção atual valida JWT stateless e autorização por dono/ADMIN no `ActionManager`, sem autenticar o usuário pelo firewall nativo do Symfony.

### JWT Stateless Sem Revogação Server-Side

Arquivos: `src/Controller/AccessControlController.php`, `src/Infrastructure/Handler/Action/PrimaryAction/AccessControlAction.php`

O endpoint `/login` gera JWT stateless assinado com `APP_SECRET` e o `ActionManager` valida bearer token e autorização por registro nas rotas CRUD/status. O `/logoff` apenas confirma o encerramento para o cliente descartar o token; revogação server-side exigirá blacklist/persistência de sessões ou tokens opacos armazenados.

### Falta De Testes Automatizados

Não há `phpunit.xml`, pasta `tests` ou scripts de teste no `composer.json`. Mudanças devem ser verificadas com `php -l` e comandos Symfony/Doctrine quando possível.

## Recomendações Para Próximas Iterações

- Criar um field/output próprio para coleções inversas (`OneToMany`) quando a API precisar expor listas como `Wallet.walletTransactions`.
- Adicionar testes de unidade para Fields/Query/Response e testes funcionais para `UserController`.
- Padronizar `declare(strict_types=1);` em arquivos novos.
- Se autenticação avançar, implementar `UserInterface`, password hasher do Symfony e firewall/autenticador real.
