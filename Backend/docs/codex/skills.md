# Skills Locais Do Projeto

Este projeto possui Skills versionadas em `skills/` para dar contexto operacional mais específico aos agentes Codex. Leia a Skill correspondente antes de alterar o módulo coberto por ela.

## Mapa De Skills

| Skill | Quando usar | Diretório principal |
| --- | --- | --- |
| [appfinancasnew-backend-fields](../../skills/appfinancasnew-backend-fields/SKILL.md) | Alterar ou criar fields, validações, enums, tipos de campo, relações ou output de atributos. | `src/Infrastructure/DTO/EntityAttributes` |
| [appfinancasnew-backend-entity-dtos](../../skills/appfinancasnew-backend-entity-dtos/SKILL.md) | Criar ou alterar EntityDTOs configuráveis, `configureFields()`, `setFieldValues()`, `output()` e hidratação por entidade. | `src/Infrastructure/DTO/EntityDto` |
| [appfinancasnew-backend-actions](../../skills/appfinancasnew-backend-actions/SKILL.md) | Alterar fluxo CRUD, `ActionManager`, `Action`, hooks `SpecificAction` ou ações primárias como login/logoff. | `src/Infrastructure/Handler/Action` |
| [appfinancasnew-backend-helpers](../../skills/appfinancasnew-backend-helpers/SKILL.md) | Usar, alterar ou criar helpers de query, output, hidratação, response builders, senha ou utilitários. | `src/Infrastructure/Helper` |

## Ordem Recomendada Para Agentes

1. Leia `AGENTS.md`.
2. Leia `.codex`.
3. Leia `docs/codex/project-context.md`, `docs/codex/agent-playbook.md`, este arquivo e `docs/codex/review-notes.md`.
4. Identifique os diretórios que a tarefa toca.
5. Leia as Skills correspondentes em `skills/`.
6. Só então edite código ou documentação.

## Como Manter As Skills

- Atualize a Skill quando uma regra do módulo mudar de forma durável.
- Mantenha exemplos iguais ao código real do projeto.
- Não use a Skill para registrar bugs temporários; use `docs/codex/review-notes.md` quando for um risco técnico ainda aberto.
- Se uma mudança tocar mais de um módulo, leia e atualize todas as Skills envolvidas.
