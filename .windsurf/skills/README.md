# Skills Centralizadas

Esta pasta contém todas as skills do projeto AppFinancasNew centralizadas para acesso pelo Windsurf.

## Estrutura

```
.windsurf/skills/
├── README.md                                    # Este arquivo
│
├── caveman-specialized/                         # Modo ultra-comprimido
├── backend-complete-specialized/                # Guia completo backend
├── backend-fields-specialized/                  # Fields especializados
├── backend-entity-dto-specialized/              # Configurations especializados
├── backend-actions-specialized/                 # Actions especializados
├── frontend-fields-forms-specialized/           # Fields/Forms especializados
├── frontend-menus-specialized/                  # Menus especializados
├── frontend-tailwind-specialized/               # Tailwind especializado
│
├── appfinancasnew-project/                      # Skill geral do projeto
│
├── appfinancasnew-backend-actions/              # Backend: Actions (raiz)
├── appfinancasnew-backend-entity-dtos/          # Backend: Configurations (raiz)
├── appfinancasnew-backend-fields/               # Backend: Fields (raiz)
├── appfinancasnew-backend-helpers/              # Backend: Helpers (raiz)
│
├── backend-actions-specific/                    # Backend: Actions (específico)
├── backend-entity-dtos-specific/                # Backend: Configurations (específico)
├── backend-fields-specific/                     # Backend: Fields (específico)
├── backend-helpers-specific/                    # Backend: Helpers (específico)
│
├── appfinancasnew-frontend-api/                 # Frontend: API client
├── appfinancasnew-frontend-react-router/        # Frontend: React Router
├── appfinancasnew-frontend-fields-api/          # Frontend: Fields e API
└── appfinancasnew-react-mobile-first/           # Frontend: UI mobile-first
```

## Mapa De Skills

### Geral

| Skill | Descrição | Quando Usar |
|-------|-----------|-------------|
| **appfinancasnew-project** | Contexto geral do monorepo | Trabalhar em qualquer parte do projeto, setup, Docker, banco, quality gates, documentação |
| **caveman** 🦴 | Modo de comunicação ultra-comprimido | Reduzir tokens ~75%, respostas rápidas, economia de contexto |

### Skills Agregadoras (Especialistas)

| Skill | Descrição | Quando Usar |
|-------|-----------|-------------|
| **frontend-specialist** ⭐ | Agrega todas as skills de frontend | Desenvolvimento completo de features frontend, arquitetura de componentes, UI/UX |
| **frontend-integrator** 🔗 | Agrega skills de frontend + backend para integração | Criar features full-stack, integrar endpoints, debugar integração API |
| **backend-specialist** ⭐ | Agrega todas as skills de backend | Desenvolvimento completo de features backend, arquitetura de API, domínio |
| **backend-entity-generator** 🏗️ | Agrega skills para geração de entidades | Criar entidades Doctrine completas com CRUD, Fields, Configurations, migrations |
| **backend-review** 🔍 | Agrega skills de backend + review | Review de código backend, sugestões de melhoria, refatoração |
| **frontend-review** 🔍 | Agrega skills de frontend + review | Review de código frontend, sugestões de melhoria, refatoração |

### Backend - Skills Especializadas (Invocáveis)

| Skill | Descrição | Quando Usar |
|-------|-----------|-------------|
| **backend-complete** ⭐ | Guia completo e compactado | Quick start CRUD, referência rápida, checklist completo |
| **backend-fields** | Fields especializados | Criar campos, validações, enums, campos relacionais |
| **backend-entity-dto** | Configurations especializados | Configurar Configurations, output, hidratação, Form DTOs |
| **backend-actions** | Actions especializados | Fluxo CRUD, hooks, SpecificAction, ActionManager |

### Backend - Skills Completas (Referência)

| Skill | Descrição | Quando Usar |
|-------|-----------|-------------|
| **appfinancasnew-backend-actions** | ActionManager, Actions, CRUD | Alterar fluxo CRUD, ActionManager, Action, hooks SpecificAction, login/logoff |
| **appfinancasnew-backend-entity-dtos** | Configurations configuráveis | Criar ou alterar Configurations, configureFields(), output(), hidratação |
| **appfinancasnew-backend-fields** | Fields, validações, enums | Alterar ou criar fields, validações, enums, relation fields, output de atributos |
| **appfinancasnew-backend-helpers** | Helpers diversos | Usar, alterar ou criar helpers de query, output, response builders, auth, senha |
| **backend-actions-specific** | Actions (versão específica Backend) | Versão específica do Backend/ para referência |
| **backend-entity-dtos-specific** | Configurations (versão específica Backend) | Versão específica do Backend/ para referência |
| **backend-fields-specific** | Fields (versão específica Backend) | Versão específica do Backend/ para referência |
| **backend-helpers-specific** | Helpers (versão específica Backend) | Versão específica do Backend/ para referência |

### Frontend - Skills Especializadas (Invocáveis)

| Skill | Descrição | Quando Usar |
|-------|-----------|-------------|
| **frontend-fields-forms** | Fields e Forms | Formulários dinâmicos, validação, FieldsForm, integração com API |
| **frontend-menus** | Menus e navegação | Criar menus, regras de permissão, rotas protegidas, navegação mobile |
| **frontend-tailwind** | Tailwind CSS | Utility classes, componentes, responsividade, design system |

### Frontend - Skills Completas (Referência)

| Skill | Descrição | Quando Usar |
|-------|-----------|-------------|
| **appfinancasnew-frontend-api** | Cliente HTTP e JWT | Criar ou alterar cliente HTTP, integração JWT, chamadas API, contratos de resposta |
| **appfinancasnew-frontend-react-router** | Rotas e componentes | Alterar rotas, layout raiz, componentes, estilos globais, estrutura React Router |
| **appfinancasnew-frontend-fields-api** | Formulários e API | Criar formulários com Fields, modais CRUD, integrações API, sessão/JWT, payloads |
| **appfinancasnew-react-mobile-first** | UI mobile-first | Criar ou refatorar UI React Router/Tailwind mobile first, dashboards, navegação, modais, grids, tabelas, gráficos |

## Como Usar

### No Windsurf

As skills são automaticamente detectadas pelo Windsurf quando estão na pasta `.windsurf/skills/`.

#### Skills Invocáveis (Especializadas)

**Método 1: Comando /skill**

```
# Skills Agregadoras (Especialistas)
/skill frontend-specialist       # Todo conhecimento de frontend
/skill frontend-integrator       # Integração frontend-backend
/skill backend-specialist        # Todo conhecimento de backend
/skill backend-entity-generator  # Geração completa de entidades
/skill backend-review            # Review de código backend
/skill frontend-review           # Review de código frontend

# Skills Especializadas
/skill caveman                # Modo ultra-comprimido (~75% menos tokens)
/skill backend-complete       # Guia completo (recomendado para começar)
/skill backend-fields         # Trabalhar com Fields
/skill backend-entity-dto     # Trabalhar com Configurations
/skill backend-actions        # Trabalhar com Actions
/skill frontend-fields-forms  # Formulários e validação
/skill frontend-menus         # Menus e navegação
/skill frontend-tailwind      # Estilização com Tailwind
```

**Método 2: Menção @skills: no chat**

Use `@skills:` para mencionar diretamente:

```
# Skills Agregadoras
@skills:frontend-specialist
@skills:frontend-integrator
@skills:backend-specialist
@skills:backend-entity-generator
@skills:backend-review
@skills:frontend-review

# Skills Especializadas
@skills:caveman-specialized
@skills:backend-complete-specialized
@skills:backend-fields-specialized
@skills:backend-entity-dto-specialized
@skills:backend-actions-specialized
@skills:frontend-fields-forms-specialized
@skills:frontend-menus-specialized
@skills:frontend-tailwind-specialized
```

#### Skills Completas (Referência)

Para referência detalhada:
```
/skill appfinancasnew-project
/skill appfinancasnew-backend-actions
/skill appfinancasnew-frontend-api
```

### Com Agentes

Os agentes em `.windsurf/agents/` já referenciam estas skills:

- **appfinancas-project**: Usa todas as skills relevantes
- **appfinancas-backend**: Usa skills de backend
- **appfinancas-frontend**: Usa skills de frontend

## Origem Das Skills

### Skills Da Raiz (`skills/`)
Copiadas de `/home/gustavo-luis/Documents/AppFinancasNew/skills/`:
- appfinancasnew-project
- appfinancasnew-backend-actions
- appfinancasnew-backend-entity-dtos
- appfinancasnew-backend-fields
- appfinancasnew-backend-helpers
- appfinancasnew-frontend-fields-api
- appfinancasnew-react-mobile-first

### Skills Do Backend (`Backend/skills/`)
Copiadas de `/home/gustavo-luis/Documents/AppFinancasNew/Backend/skills/`:
- backend-actions-specific
- backend-entity-dtos-specific
- backend-fields-specific
- backend-helpers-specific

### Skills Do Frontend (`frontEnd/skills/`)
Copiadas de `/home/gustavo-luis/Documents/AppFinancasNew/frontEnd/skills/`:
- appfinancasnew-frontend-api
- appfinancasnew-frontend-react-router

## Diferença Entre Skills Gerais E Específicas

### Skills Gerais (da raiz)
- Visão ampla do módulo
- Contexto do monorepo
- Integração entre backend e frontend
- Uso recomendado para trabalhos que cruzam módulos

### Skills Específicas (Backend/ e frontEnd/)
- Foco interno do módulo
- Detalhes de implementação
- Caminhos relativos ao módulo
- Uso recomendado para trabalhos focados em um módulo

## Manutenção

### Atualizar Skills

Quando uma skill for atualizada na origem:

```bash
# Atualizar skill da raiz
cp -r skills/[nome-skill] .windsurf/skills/

# Atualizar skill do backend
cp -r Backend/skills/[nome-skill] .windsurf/skills/backend-[nome]-specific

# Atualizar skill do frontend
cp -r frontEnd/skills/[nome-skill] .windsurf/skills/
```

### Adicionar Nova Skill

1. Crie a skill na localização original (`skills/`, `Backend/skills/` ou `frontEnd/skills/`)
2. Copie para `.windsurf/skills/`
3. Atualize este README
4. Atualize os agentes em `.windsurf/agents/` se necessário

## Documentação Relacionada

- `.windsurf/agents/`: Agentes especializados que usam estas skills
- `.windsurf/workflows/`: Workflows para tarefas comuns
- `docs/codex/skills.md`: Documentação original de skills na raiz
- `Backend/docs/codex/skills.md`: Documentação de skills do backend
- `frontEnd/docs/codex/skills.md`: Documentação de skills do frontend

## Convenções

### Formato De Skill

Cada skill deve ter:
- Frontmatter YAML com `name` e `description`
- Seção "Scope" descrevendo quando usar
- Seções claras e bem estruturadas
- Exemplos práticos quando aplicável
- Referências a documentação relacionada

### Nomenclatura

- Skills gerais: `appfinancasnew-[modulo]-[funcionalidade]`
- Skills específicas de backend: `backend-[funcionalidade]-specific`
- Skills específicas de frontend: mantém nome original

## Suporte

Para dúvidas sobre skills:
- Consulte a skill específica em sua pasta
- Leia a documentação em `docs/codex/`
- Use os agentes especializados em `.windsurf/agents/`
- Consulte workflows em `.windsurf/workflows/`
