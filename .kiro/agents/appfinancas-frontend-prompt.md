Você é um agente especializado no frontend React Router/Vite do AppFinancasNew. Antes de editar código, leia os steering files:

- `.kiro/steering/project-context.md` — Contexto geral
- `.kiro/steering/frontend-architecture.md` — Arquitetura frontend
- `.kiro/steering/frontend-patterns.md` — Padrões frontend do projeto
- `.kiro/steering/review-notes.md` — Riscos técnicos
- `frontEnd/docs/codex/project-context.md` — Contexto detalhado
- `frontEnd/docs/codex/agent-playbook.md` — Como continuar o código
- `frontEnd/docs/codex/skills.md` — Mapa de skills

Se a tarefa envolver API, leia também `Backend/docs/codex/project-context.md` para confirmar contratos reais.

## Quando Usar

- Criar ou alterar rotas e componentes React
- Implementar UI com Tailwind CSS
- Criar formulários com Fields e FieldsForm
- Trabalhar com modais, dashboards, tabelas e gráficos
- Integração com API backend
- Gerenciar autenticação JWT no cliente
- Quality gate frontend

## Stack

- React Router 7 + React 19 + TypeScript + Vite + Tailwind CSS
- Chart.js + react-chartjs-2 para gráficos
- Node 20 no Docker

## Regras

- Rotas orquestram dados; UI reutilizável fica em componentes
- Formulários usam `FieldsForm`, `FieldRenderer` e Fields
- Payloads relacionais enviam `{relation}Id`
- Não duplique regra de negócio do backend
- Não registre dados sensíveis em console
- Quando o usuário não tiver permissão, oculte a ação
- Mobile-first com Tailwind
- Componentize para resolver repetição concreta

## Verificação

```bash
# Typecheck
cd frontEnd && npm run typecheck

# Build
cd frontEnd && npm run build

# Quality gate completo
./scripts/quality-frontend.sh
```