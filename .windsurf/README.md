# Windsurf Configuration

Esta pasta contém a configuração do Windsurf para o projeto AppFinancasNew, incluindo agentes especializados e workflows para tarefas comuns.

## Estrutura

```
.windsurf/
├── agents/           # Agentes especializados
│   ├── appfinancas-project.md   # Agente geral do projeto
│   ├── appfinancas-backend.md   # Agente especializado em backend
│   └── appfinancas-frontend.md  # Agente especializado em frontend
├── workflows/        # Workflows para tarefas comuns
│   ├── setup-project.md         # Setup inicial do projeto
│   ├── create-crud-backend.md   # Criar CRUD no backend
│   ├── create-frontend-crud.md  # Criar interface CRUD no frontend
│   ├── run-migrations.md        # Executar migrations
│   ├── quality-gates.md         # Executar quality gates
│   └── docker-management.md     # Gerenciar containers Docker
├── skills/           # Skills centralizadas do projeto
│   ├── README.md                # Documentação das skills
│   ├── appfinancasnew-project/  # Skill geral
│   ├── appfinancasnew-backend-*/ # Skills de backend
│   ├── appfinancasnew-frontend-*/ # Skills de frontend
│   └── backend-*-specific/      # Skills específicas do Backend/
└── README.md         # Este arquivo
```

## Agentes

### AppFinancas Project (`/agent appfinancas-project`)

Agente geral para trabalhar em qualquer parte do monorepo, incluindo:
- Setup do projeto
- Docker e docker-compose
- Banco de dados PostgreSQL
- Scripts de automação
- Documentação geral
- Tarefas que envolvem backend e frontend

### AppFinancas Backend (`/agent appfinancas-backend`)

Agente especializado para trabalhar na API Symfony/PHP, incluindo:
- Controllers e rotas
- EntityDTOs configuráveis
- Actions e SpecificActions
- Fields, validações e enums
- Helpers de query, output e auth
- Entidades Doctrine
- Migrations
- Autenticação JWT
- Autorização por dono/ADMIN

### AppFinancas Frontend (`/agent appfinancas-frontend`)

Agente especializado para trabalhar na aplicação React Router/Vite, incluindo:
- Rotas e componentes
- UI com Tailwind CSS
- Formulários com Fields
- Modais e dashboards
- Tabelas e gráficos
- Integração com API backend
- Autenticação JWT no cliente
- Sessão de usuário

## Workflows

### Setup Project (`/workflow setup-project`)

Setup inicial completo do projeto:
1. Configurar variáveis de ambiente
2. Provisionar usuário do banco
3. Subir stack Docker
4. Executar migrations
5. Verificar funcionamento

### Create CRUD Backend (`/workflow create-crud-backend`)

Criar um novo endpoint CRUD no backend:
1. Criar/verificar entidade Doctrine
2. Criar EntityDTO
3. Criar Form DTOs
4. Criar Controller
5. Criar SpecificAction (se necessário)
6. Criar migration
7. Testar CRUD
8. Executar quality gate

### Create Frontend CRUD (`/workflow create-frontend-crud`)

Criar interface CRUD no frontend:
1. Criar cliente de API
2. Criar Fields (se usar FieldsForm)
3. Criar componente de modal
4. Criar componente de listagem
5. Criar rota
6. Adicionar navegação
7. Testar
8. Executar quality gate

### Run Migrations (`/workflow run-migrations`)

Executar migrations do Doctrine:
- Menu interativo (recomendado)
- Comandos manuais
- Workflow completo para nova entidade
- Troubleshooting

### Quality Gates (`/workflow quality-gates`)

Executar quality gates:
- Backend: Composer validate, PHP lint, PHPCS, PHPStan, PHPUnit
- Frontend: TypeScript typecheck, build, code smells
- CI/CD automático
- Comandos individuais

### Docker Management (`/workflow docker-management`)

Gerenciar containers Docker:
- Subir/parar stack
- Gerenciar containers individuais
- Ver logs
- Executar comandos nos containers
- Reconstruir containers
- Limpar Docker
- Troubleshooting

## Como Usar

### Invocar Agentes

Use o comando `/agent` seguido do nome do agente:

```
/agent appfinancas-project
/agent appfinancas-backend
/agent appfinancas-frontend
```

### Executar Workflows

Use o comando `/workflow` seguido do nome do workflow:

```
/workflow setup-project
/workflow create-crud-backend
/workflow create-frontend-crud
/workflow run-migrations
/workflow quality-gates
/workflow docker-management
```

## Documentação Relacionada

### Raiz do Projeto
- `AGENTS.md`: Instruções para agentes na raiz
- `README.md`: Documentação geral do projeto
- `docs/codex/`: Documentação de steering
- `skills/`: Skills gerais do projeto

### Backend
- `Backend/AGENTS.md`: Instruções para agentes do backend
- `Backend/docs/codex/`: Documentação de steering do backend
- `Backend/skills/`: Skills específicas do backend

### Frontend
- `frontEnd/AGENTS.md`: Instruções para agentes do frontend
- `frontEnd/docs/codex/`: Documentação de steering do frontend
- `frontEnd/skills/`: Skills específicas do frontend

## Skills Disponíveis

Todas as skills do projeto estão centralizadas em `.windsurf/skills/` para fácil acesso pelo Windsurf.

### Geral
- `appfinancasnew-project`: Contexto geral do monorepo

### Backend
- `appfinancasnew-backend-fields`: Fields, validações, enums
- `appfinancasnew-backend-entity-dtos`: EntityDTOs configuráveis
- `appfinancasnew-backend-actions`: ActionManager, Actions, CRUD
- `appfinancasnew-backend-helpers`: Helpers de query, output, auth
- `backend-*-specific`: Versões específicas do Backend/ (para referência)

### Frontend
- `appfinancasnew-react-mobile-first`: UI React Router/Tailwind
- `appfinancasnew-frontend-fields-api`: Formulários e API
- `appfinancasnew-frontend-react-router`: Rotas e componentes
- `appfinancasnew-frontend-api`: Cliente HTTP e JWT

**Documentação completa**: Consulte `.windsurf/skills/README.md` para detalhes sobre todas as skills disponíveis.

## Manutenção

### Atualizar Agentes

Quando houver mudanças significativas no projeto:
1. Atualize os agentes em `.windsurf/agents/`
2. Mantenha consistência com `AGENTS.md` e `docs/codex/`
3. Atualize Skills relacionadas se necessário

### Atualizar Workflows

Quando processos mudarem:
1. Atualize os workflows em `.windsurf/workflows/`
2. Teste os workflows atualizados
3. Documente mudanças em `docs/codex/` se relevante

### Criar Novos Workflows

Para adicionar novos workflows:
1. Crie arquivo `.md` em `.windsurf/workflows/`
2. Use frontmatter YAML com `description`
3. Siga o padrão dos workflows existentes
4. Documente no README

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

## Convenções

### Frontmatter

Todos os arquivos devem ter frontmatter YAML:

```yaml
---
description: Descrição curta do agente/workflow
---
```

### Estrutura De Agentes

- **Quando Usar**: Descreva quando invocar o agente
- **Ordem De Leitura**: Liste documentação obrigatória
- **Skills**: Liste skills relevantes
- **Arquitetura**: Descreva padrões que devem ser preservados
- **Comandos**: Liste comandos úteis
- **Verificação**: Descreva como verificar mudanças

### Estrutura De Workflows

- **Descrição**: Objetivo do workflow
- **Pré-requisitos**: O que é necessário antes de começar
- **Passos**: Passos numerados e claros
- **Troubleshooting**: Problemas comuns e soluções
- **Próximos Passos**: O que fazer após completar o workflow

### Anotação Turbo

Use `// turbo` acima de comandos seguros para auto-run:

```markdown
// turbo
```bash
./scripts/setup-env.sh
```
```

## Contribuindo

Ao adicionar ou modificar agentes e workflows:

1. Mantenha consistência com a documentação existente
2. Teste antes de commitar
3. Atualize este README se necessário
4. Siga as convenções estabelecidas
5. Documente mudanças significativas

## Suporte

Para dúvidas ou problemas:
- Consulte `AGENTS.md` na raiz
- Leia `docs/codex/` para steering detalhado
- Use `/agent appfinancas-project` para ajuda geral
