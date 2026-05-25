---
description: Criar interface CRUD no frontend React Router
---

# Criar Interface CRUD No Frontend

Este workflow guia a criação de uma interface CRUD completa no frontend para consumir uma API do backend.

## Antes De Começar

Leia as Skills relevantes:
- `skills/appfinancasnew-frontend-fields-api/SKILL.md`
- `frontEnd/skills/appfinancasnew-frontend-react-router/SKILL.md`
- `frontEnd/skills/appfinancasnew-frontend-api/SKILL.md`

Certifique-se de que o endpoint backend já existe e está funcionando.

## Passos

### 1. Criar cliente de API

Crie em `frontEnd/app/Infrastructure/Api/{entidade}s.ts`:

```typescript
import { apiClient } from './client';

export interface {Entidade} {
  id: number;
  name: string;
  description?: string;
  status: boolean;
  createdAt: string;
  updatedAt: string;
}

export interface {Entidade}FormData {
  id?: number;
  name: string;
  description?: string;
}

export interface {Entidade}ListResponse {
  {entidades}: {Entidade}[];
  pagination: {
    currentPage: number;
    totalPages: number;
    totalItems: number;
    itemsPerPage: number;
  };
}

export async function list{Entidades}(params?: {
  page?: number;
  perPage?: number;
  name?: string;
}): Promise<{Entidade}ListResponse> {
  const searchParams = new URLSearchParams();
  if (params?.page) searchParams.set('page', params.page.toString());
  if (params?.perPage) searchParams.set('perPage', params.perPage.toString());
  if (params?.name) searchParams.set('name', params.name);

  const response = await apiClient(`/{entidade}?${searchParams}`);
  return response.data;
}

export async function get{Entidade}(id: number): Promise<{Entidade}> {
  const response = await apiClient(`/{entidade}/${id}`);
  return response.data.{entidade};
}

export async function create{Entidade}(data: {Entidade}FormData): Promise<{Entidade}> {
  const response = await apiClient('/{entidade}', {
    method: 'POST',
    body: JSON.stringify(data),
  });
  return response.data.{entidade};
}

export async function update{Entidade}(data: {Entidade}FormData): Promise<{Entidade}> {
  const response = await apiClient('/{entidade}', {
    method: 'PATCH',
    body: JSON.stringify(data),
  });
  return response.data.{entidade};
}

export async function toggle{Entidade}Status(id: number, status: boolean): Promise<{Entidade}> {
  const response = await apiClient(`/{entidade}/${id}/status`, {
    method: 'PATCH',
    body: JSON.stringify({ status }),
  });
  return response.data.{entidade};
}
```

### 2. Criar Fields (se usar FieldsForm)

Crie em `frontEnd/app/Infrastructure/DTO/EntityAttributes/{Entidade}Fields.ts`:

```typescript
import { FieldsAttribute } from './FieldsAttribute';

export function get{Entidade}Fields(): FieldsAttribute[] {
  return [
    {
      name: 'name',
      label: 'Nome',
      type: 'text',
      required: true,
      placeholder: 'Digite o nome',
    },
    {
      name: 'description',
      label: 'Descrição',
      type: 'textarea',
      required: false,
      placeholder: 'Digite a descrição',
    },
  ];
}
```

### 3. Criar componente de modal

Crie em `frontEnd/app/components/{entidade}/{Entidade}Modal.tsx`:

```typescript
import { useState } from 'react';
import { FieldsForm } from '../forms/FieldsForm';
import { get{Entidade}Fields } from '~/Infrastructure/DTO/EntityAttributes/{Entidade}Fields';
import { create{Entidade}, update{Entidade}, type {Entidade}FormData } from '~/Infrastructure/Api/{entidade}s';

interface {Entidade}ModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: () => void;
  {entidade}?: {Entidade}FormData;
}

export function {Entidade}Modal({ isOpen, onClose, onSuccess, {entidade} }: {Entidade}ModalProps) {
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async (data: Record<string, any>) => {
    setIsLoading(true);
    setError(null);

    try {
      const formData: {Entidade}FormData = {
        id: {entidade}?.id,
        name: data.name,
        description: data.description,
      };

      if ({entidade}?.id) {
        await update{Entidade}(formData);
      } else {
        await create{Entidade}(formData);
      }

      onSuccess();
      onClose();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erro ao salvar');
    } finally {
      setIsLoading(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h2 className="text-xl font-bold mb-4">
          {{entidade}?.id ? 'Editar' : 'Novo'} {Entidade}
        </h2>

        <FieldsForm
          fields={get{Entidade}Fields()}
          initialValues={{entidade}}
          onSubmit={handleSubmit}
          isLoading={isLoading}
          error={error}
        />

        <button
          type="button"
          onClick={onClose}
          className="mt-4 w-full px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
          disabled={isLoading}
        >
          Cancelar
        </button>
      </div>
    </div>
  );
}
```

### 4. Criar componente de listagem

Crie em `frontEnd/app/components/{entidade}/{Entidade}List.tsx`:

```typescript
import { useState, useEffect } from 'react';
import { list{Entidades}, toggle{Entidade}Status, type {Entidade} } from '~/Infrastructure/Api/{entidade}s';
import { {Entidade}Modal } from './{Entidade}Modal';

export function {Entidade}List() {
  const [items, setItems] = useState<{Entidade}[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [selected{Entidade}, setSelected{Entidade}] = useState<{Entidade} | undefined>();

  const loadItems = async () => {
    setIsLoading(true);
    setError(null);

    try {
      const response = await list{Entidades}();
      setItems(response.{entidades});
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erro ao carregar');
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    loadItems();
  }, []);

  const handleToggleStatus = async (id: number, currentStatus: boolean) => {
    try {
      await toggle{Entidade}Status(id, !currentStatus);
      await loadItems();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erro ao alterar status');
    }
  };

  const handleEdit = ({entidade}: {Entidade}) => {
    setSelected{Entidade}({entidade});
    setIsModalOpen(true);
  };

  const handleNew = () => {
    setSelected{Entidade}(undefined);
    setIsModalOpen(true);
  };

  const handleModalClose = () => {
    setIsModalOpen(false);
    setSelected{Entidade}(undefined);
  };

  const handleModalSuccess = () => {
    loadItems();
  };

  if (isLoading) {
    return <div className="text-center py-8">Carregando...</div>;
  }

  if (error) {
    return <div className="text-red-600 py-8">{error}</div>;
  }

  return (
    <div>
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-2xl font-bold">{Entidades}</h1>
        <button
          onClick={handleNew}
          className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
        >
          Novo {Entidade}
        </button>
      </div>

      <div className="bg-white shadow-md rounded-lg overflow-hidden">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Nome
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Descrição
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Status
              </th>
              <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">
                Ações
              </th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {items.map((item) => (
              <tr key={item.id}>
                <td className="px-6 py-4 whitespace-nowrap">{item.name}</td>
                <td className="px-6 py-4">{item.description}</td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <span
                    className={`px-2 py-1 rounded-full text-xs ${
                      item.status
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800'
                    }`}
                  >
                    {item.status ? 'Ativo' : 'Inativo'}
                  </span>
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button
                    onClick={() => handleEdit(item)}
                    className="text-blue-600 hover:text-blue-900 mr-4"
                  >
                    Editar
                  </button>
                  <button
                    onClick={() => handleToggleStatus(item.id, item.status)}
                    className="text-gray-600 hover:text-gray-900"
                  >
                    {item.status ? 'Desativar' : 'Ativar'}
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      <{Entidade}Modal
        isOpen={isModalOpen}
        onClose={handleModalClose}
        onSuccess={handleModalSuccess}
        {entidade}={selected{Entidade}}
      />
    </div>
  );
}
```

### 5. Criar rota

Adicione em `frontEnd/app/routes.ts`:

```typescript
import { type RouteConfig } from '@react-router/dev/routes';

export default [
  // ... outras rotas
  {
    path: '/{entidades}',
    file: 'routes/{entidades}.tsx',
  },
] satisfies RouteConfig;
```

Crie `frontEnd/app/routes/{entidades}.tsx`:

```typescript
import { {Entidade}List } from '~/components/{entidade}/{Entidade}List';
import { useRequireAuth } from '~/Infrastructure/Auth/session';

export default function {Entidades}Page() {
  const { isLoading } = useRequireAuth();

  if (isLoading) {
    return <div>Carregando...</div>;
  }

  return (
    <div className="container mx-auto px-4 py-8">
      <{Entidade}List />
    </div>
  );
}
```

### 6. Adicionar navegação

Adicione link no menu principal (ex: `frontEnd/app/components/layout/Navigation.tsx`):

```typescript
<Link to="/{entidades}" className="nav-link">
  {Entidades}
</Link>
```

### 7. Testar

// turbo
```bash
cd frontEnd
npm run typecheck
```

```bash
cd frontEnd
npm run dev
```

Acesse http://localhost:3000/{entidades} e teste:
- Listagem
- Criação
- Edição
- Toggle de status

### 8. Executar quality gate

```bash
./scripts/quality-frontend.sh
```

## Próximos Passos

- Adicione paginação se necessário
- Adicione filtros de busca
- Adicione testes
- Documente a interface em `frontEnd/docs/`
