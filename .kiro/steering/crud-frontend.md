---
inclusion: manual
---

# Criar Interface CRUD No Frontend

Guia passo a passo para criar uma interface CRUD no frontend React Router.

## Pré-requisitos

- Endpoint backend funcional
- Stack Docker rodando ou frontend local (`cd frontEnd && npm run dev`)

## Passos

### 1. Criar Cliente de API

Em `frontEnd/app/Infrastructure/Api/{entidade}.ts`:

```typescript
import { apiClient } from './client';

export async function list{Entidade}s(params?: Record<string, string>) {
  return apiClient.get('/{rota}', params);
}

export async function get{Entidade}(id: number) {
  return apiClient.get(`/{rota}/${id}`);
}

export async function create{Entidade}(data: Record<string, unknown>) {
  return apiClient.post('/{rota}', data);
}

export async function update{Entidade}(data: Record<string, unknown>) {
  return apiClient.patch('/{rota}', data);
}

export async function delete{Entidade}(id: number) {
  return apiClient.delete(`/{rota}/${id}`);
}
```

### 2. Criar Fields (se usar FieldsForm)

Em `frontEnd/app/Infrastructure/DTO/EntityAttributes/`:
- Configurar `FieldsAttribute` com os campos do formulário
- Usar tipos compatíveis com o backend

### 3. Criar Componente de Modal

Usar `FieldsForm` para formulários baseados em metadados:

```tsx
<FieldsForm
  fields={fieldsAttribute}
  onSubmit={handleSubmit}
  isLoading={isSubmitting}
/>
```

### 4. Criar Componente de Listagem

- Grid/tabela com dados paginados
- Filtros quando aplicável
- Ações por registro (editar, excluir)
- Empty state quando sem dados

### 5. Criar Rota

Em `frontEnd/app/routes.ts` e `frontEnd/app/routes/{rota}.tsx`:
- Usar `useRequireAuth()` para proteção
- Carregar dados no componente
- Orquestrar modais e estado

### 6. Adicionar Navegação

Em `AppSidebar.tsx`:
- Adicionar link para a nova rota
- Ícone e texto descritivo

### 7. Verificar

```bash
cd frontEnd && npm run typecheck
cd frontEnd && npm run build
```

## Regras

- Rotas orquestram dados; UI reutilizável fica em componentes
- Formulários usam `FieldsForm` e `FieldRenderer`
- Payloads relacionais enviam `{relation}Id`
- Não duplique regra de negócio do backend
- Não registre dados sensíveis em console
- Quando o usuário não tiver permissão, oculte a ação
- Mobile-first com breakpoints `sm:`, `md:`, `lg:`, `xl:`
