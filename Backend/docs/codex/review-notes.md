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

### Autorização De Edição De Usuário Corrigida (2026-05-25)

Arquivos: 
- `src/Infrastructure/Helper/Auth/RecordAuthorizationHelperTrait.php`
- `config/packages/framework.yaml`

**Correções aplicadas:**

1. Corrigido bug nos métodos `canAccessExistingRecord()` e `canModifyCatalogRecord()` que retornavam `true` quando a entidade não era encontrada (`$entity === null`). Agora retornam `false` corretamente, impedindo acesso a registros inexistentes.

2. **Habilitado Symfony Serializer** em `framework.yaml` com `serializer: enabled: true`. Sem essa configuração, o `MapRequestPayload` não conseguia deserializar o JSON do request, resultando em todos os campos do DTO como `null`.

**Comportamento atual correto:**
- ADMIN pode editar qualquer usuário
- Usuário comum pode editar apenas seu próprio registro
- Usuário comum não pode alterar o campo `role` (nem o próprio)
- Tentativa de editar usuário inexistente retorna erro de autorização
- `MapRequestPayload` deserializa corretamente os DTOs de formulário

## Recomendações Para Próximas Iterações

- Criar um field/output próprio para coleções inversas (`OneToMany`) quando a API precisar expor listas como `Wallet.walletTransactions`.
- Ampliar a suíte existente com testes funcionais para controllers e fluxos HTTP reais.
- Padronizar `declare(strict_types=1);` em arquivos novos.
- Se autenticação avançar, implementar `UserInterface`, password hasher do Symfony e firewall/autenticador real.
