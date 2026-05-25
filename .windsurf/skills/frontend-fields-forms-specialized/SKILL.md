---
name: frontend-fields-forms
description: Skill especializada em Fields e Forms do frontend - formulários dinâmicos, validação e integração com API
---

# Frontend Fields & Forms Skill

Skill especializada para trabalhar com Fields e Forms no frontend React Router/Vite.

## Quando Usar

Use esta skill quando precisar:
- Criar formulários baseados em metadados
- Implementar validação de campos
- Trabalhar com FieldsForm e FieldRenderer
- Criar Fields customizados
- Integrar formulários com API backend
- Mapear payloads para backend

## Localização

`frontEnd/app/Infrastructure/DTO/EntityAttributes/`

## Conceitos Principais

### FieldsAttribute

Metadados que definem um campo:

```typescript
interface FieldsAttribute {
  name: string;              // Nome do campo
  label: string;             // Label exibido
  type: FieldType;           // Tipo do campo
  required: boolean;         // Se é obrigatório
  placeholder?: string;      // Placeholder
  helpText?: string;         // Texto de ajuda
  options?: string[];        // Opções (select, radio)
  min?: number;              // Valor mínimo
  max?: number;              // Valor máximo
  pattern?: string;          // Regex de validação
  disabled?: boolean;        // Se está desabilitado
}
```

### FieldType

```typescript
type FieldType = 
  | 'text'
  | 'email'
  | 'password'
  | 'number'
  | 'date'
  | 'datetime-local'
  | 'textarea'
  | 'select'
  | 'checkbox'
  | 'radio';
```

## Criar Fields Para Uma Entidade

```typescript
// frontEnd/app/Infrastructure/DTO/EntityAttributes/MinhaEntidadeFields.ts
import { FieldsAttribute } from './FieldsAttribute';

export function getMinhaEntidadeFields(): FieldsAttribute[] {
  return [
    {
      name: 'name',
      label: 'Nome',
      type: 'text',
      required: true,
      placeholder: 'Digite o nome',
      helpText: 'Nome completo da entidade',
    },
    {
      name: 'email',
      label: 'E-mail',
      type: 'email',
      required: true,
      placeholder: 'email@exemplo.com',
    },
    {
      name: 'description',
      label: 'Descrição',
      type: 'textarea',
      required: false,
      placeholder: 'Digite uma descrição',
    },
    {
      name: 'age',
      label: 'Idade',
      type: 'number',
      required: false,
      min: 0,
      max: 150,
    },
    {
      name: 'type',
      label: 'Tipo',
      type: 'select',
      required: true,
      options: ['Tipo 1', 'Tipo 2', 'Tipo 3'],
    },
    {
      name: 'active',
      label: 'Ativo',
      type: 'checkbox',
      required: false,
    },
  ];
}
```

## FieldsForm Component

### Uso Básico

```typescript
import { FieldsForm } from '~/components/forms/FieldsForm';
import { getMinhaEntidadeFields } from '~/Infrastructure/DTO/EntityAttributes/MinhaEntidadeFields';

export function MinhaEntidadeModal({ onSubmit, initialValues, isLoading }) {
  return (
    <FieldsForm
      fields={getMinhaEntidadeFields()}
      initialValues={initialValues}
      onSubmit={onSubmit}
      isLoading={isLoading}
    />
  );
}
```

### Props Do FieldsForm

```typescript
interface FieldsFormProps {
  fields: FieldsAttribute[];           // Metadados dos campos
  initialValues?: Record<string, any>; // Valores iniciais
  onSubmit: (data: Record<string, any>) => void | Promise<void>;
  isLoading?: boolean;                 // Estado de loading
  error?: string | null;               // Erro geral do form
  submitLabel?: string;                // Label do botão submit
  showCancel?: boolean;                // Mostrar botão cancelar
  onCancel?: () => void;               // Callback de cancelar
}
```

## FieldRenderer Component

Para renderizar um campo individual:

```typescript
import { FieldRenderer } from '~/components/forms/FieldRenderer';

export function MeuFormulario() {
  const [value, setValue] = useState('');
  const [error, setError] = useState<string | null>(null);

  const field: FieldsAttribute = {
    name: 'email',
    label: 'E-mail',
    type: 'email',
    required: true,
  };

  const handleChange = (newValue: any) => {
    setValue(newValue);
    const validationError = validateFieldValue(field, newValue);
    setError(validationError);
  };

  return (
    <FieldRenderer
      field={field}
      value={value}
      onChange={handleChange}
      error={error}
    />
  );
}
```

## Validação De Campos

### validateFieldValue

Valida um único campo:

```typescript
import { validateFieldValue } from '~/Infrastructure/DTO/EntityAttributes/validation';

const error = validateFieldValue(field, value);
// Retorna string com erro ou null se válido
```

### validateFieldValues

Valida múltiplos campos:

```typescript
import { validateFieldValues } from '~/Infrastructure/DTO/EntityAttributes/validation';

const errors = validateFieldValues(fields, values);
// Retorna Record<string, string> com erros por campo
```

### Regras De Validação

```typescript
// Required
if (field.required && !value) {
  return `${field.label} é obrigatório`;
}

// Email
if (field.type === 'email' && value) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(value)) {
    return 'E-mail inválido';
  }
}

// Number min/max
if (field.type === 'number' && value) {
  if (field.min !== undefined && value < field.min) {
    return `Valor mínimo: ${field.min}`;
  }
  if (field.max !== undefined && value > field.max) {
    return `Valor máximo: ${field.max}`;
  }
}

// Pattern
if (field.pattern && value) {
  const regex = new RegExp(field.pattern);
  if (!regex.test(value)) {
    return 'Formato inválido';
  }
}
```

## Formulário Completo Com API

```typescript
import { useState } from 'react';
import { FieldsForm } from '~/components/forms/FieldsForm';
import { getMinhaEntidadeFields } from '~/Infrastructure/DTO/EntityAttributes/MinhaEntidadeFields';
import { createMinhaEntidade, updateMinhaEntidade } from '~/Infrastructure/Api/minhasentidades';

interface MinhaEntidadeFormProps {
  entidade?: MinhaEntidade;
  onSuccess: () => void;
  onCancel: () => void;
}

export function MinhaEntidadeForm({ entidade, onSuccess, onCancel }: MinhaEntidadeFormProps) {
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async (data: Record<string, any>) => {
    setIsLoading(true);
    setError(null);

    try {
      const payload = {
        id: entidade?.id,
        name: data.name,
        email: data.email,
        description: data.description,
        age: data.age ? parseInt(data.age) : undefined,
        type: data.type,
        active: data.active ?? true,
      };

      if (entidade?.id) {
        await updateMinhaEntidade(payload);
      } else {
        await createMinhaEntidade(payload);
      }

      onSuccess();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Erro ao salvar');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="space-y-4">
      <h2 className="text-xl font-bold">
        {entidade ? 'Editar' : 'Nova'} Entidade
      </h2>

      <FieldsForm
        fields={getMinhaEntidadeFields()}
        initialValues={entidade}
        onSubmit={handleSubmit}
        isLoading={isLoading}
        error={error}
        submitLabel={entidade ? 'Salvar' : 'Criar'}
        showCancel
        onCancel={onCancel}
      />
    </div>
  );
}
```

## Modal Com Formulário

```typescript
import { useState } from 'react';
import { FieldsForm } from '~/components/forms/FieldsForm';
import { getMinhaEntidadeFields } from '~/Infrastructure/DTO/EntityAttributes/MinhaEntidadeFields';

interface MinhaEntidadeModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: () => void;
  entidade?: MinhaEntidade;
}

export function MinhaEntidadeModal({ 
  isOpen, 
  onClose, 
  onSuccess, 
  entidade 
}: MinhaEntidadeModalProps) {
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async (data: Record<string, any>) => {
    setIsLoading(true);
    setError(null);

    try {
      // Chamar API
      await saveEntidade(data);
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
      <div className="bg-white rounded-lg p-6 max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
        <h2 className="text-xl font-bold mb-4">
          {entidade ? 'Editar' : 'Nova'} Entidade
        </h2>

        <FieldsForm
          fields={getMinhaEntidadeFields()}
          initialValues={entidade}
          onSubmit={handleSubmit}
          isLoading={isLoading}
          error={error}
          submitLabel={entidade ? 'Salvar' : 'Criar'}
          showCancel
          onCancel={onClose}
        />
      </div>
    </div>
  );
}
```

## Campos Relacionais

### Field Relacional

```typescript
{
  name: 'walletId',
  label: 'Carteira',
  type: 'select',
  required: true,
  options: wallets.map(w => ({ value: w.id, label: w.name })),
}
```

### Carregar Opções

```typescript
import { useEffect, useState } from 'react';
import { listWallets } from '~/Infrastructure/Api/wallets';

export function useWalletOptions() {
  const [wallets, setWallets] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    async function load() {
      try {
        const response = await listWallets();
        setWallets(response.wallets);
      } finally {
        setIsLoading(false);
      }
    }
    load();
  }, []);

  return { wallets, isLoading };
}

// Uso
export function EntryForm() {
  const { wallets, isLoading } = useWalletOptions();

  if (isLoading) return <div>Carregando...</div>;

  const fields = [
    {
      name: 'walletId',
      label: 'Carteira',
      type: 'select',
      required: true,
      options: wallets.map(w => ({ value: w.id.toString(), label: w.name })),
    },
    // ... outros campos
  ];

  return <FieldsForm fields={fields} onSubmit={handleSubmit} />;
}
```

## Validação Customizada

### No Field

```typescript
{
  name: 'cpf',
  label: 'CPF',
  type: 'text',
  required: true,
  pattern: '\\d{3}\\.\\d{3}\\.\\d{3}-\\d{2}',
  helpText: 'Formato: 000.000.000-00',
}
```

### Função De Validação

```typescript
function validateCPF(value: string): string | null {
  const cpf = value.replace(/[^\d]/g, '');
  
  if (cpf.length !== 11) {
    return 'CPF deve ter 11 dígitos';
  }
  
  // Validação de CPF aqui
  
  return null;
}

// Uso
const error = validateCPF(value) || validateFieldValue(field, value);
```

## Máscaras De Input

```typescript
function formatCPF(value: string): string {
  const cpf = value.replace(/[^\d]/g, '');
  return cpf
    .replace(/(\d{3})(\d)/, '$1.$2')
    .replace(/(\d{3})(\d)/, '$1.$2')
    .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
}

// Uso no onChange
const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
  const formatted = formatCPF(e.target.value);
  setValue(formatted);
};
```

## Campos Condicionais

```typescript
export function getConditionalFields(showAdvanced: boolean): FieldsAttribute[] {
  const basicFields = [
    { name: 'name', label: 'Nome', type: 'text', required: true },
    { name: 'email', label: 'E-mail', type: 'email', required: true },
  ];

  const advancedFields = [
    { name: 'phone', label: 'Telefone', type: 'text', required: false },
    { name: 'address', label: 'Endereço', type: 'text', required: false },
  ];

  return showAdvanced ? [...basicFields, ...advancedFields] : basicFields;
}
```

## Acessibilidade

### Labels E IDs

```typescript
<label htmlFor={field.name} className="block text-sm font-medium">
  {field.label}
  {field.required && <span className="text-red-500 ml-1">*</span>}
</label>
<input
  id={field.name}
  name={field.name}
  type={field.type}
  required={field.required}
  aria-required={field.required}
  aria-invalid={!!error}
  aria-describedby={error ? `${field.name}-error` : undefined}
/>
{error && (
  <p id={`${field.name}-error`} className="text-red-500 text-sm mt-1">
    {error}
  </p>
)}
```

## Feedback Visual

### Estados Do Campo

```typescript
const inputClasses = cn(
  'w-full px-3 py-2 border rounded-md',
  'focus:outline-none focus:ring-2',
  error
    ? 'border-red-500 focus:ring-red-500'
    : 'border-gray-300 focus:ring-blue-500',
  disabled && 'bg-gray-100 cursor-not-allowed'
);
```

### Loading State

```typescript
<button
  type="submit"
  disabled={isLoading}
  className="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
>
  {isLoading ? (
    <>
      <Loader2 className="inline w-4 h-4 mr-2 animate-spin" />
      Salvando...
    </>
  ) : (
    'Salvar'
  )}
</button>
```

## Integração Com Backend

### Mapear Payload

```typescript
function mapFormDataToPayload(data: Record<string, any>): MinhaEntidadePayload {
  return {
    id: data.id,
    name: data.name,
    email: data.email?.toLowerCase(),
    description: data.description || null,
    age: data.age ? parseInt(data.age) : null,
    walletId: data.walletId ? parseInt(data.walletId) : null,
    active: data.active ?? true,
  };
}
```

### Mapear Response Para Form

```typescript
function mapEntityToFormData(entity: MinhaEntidade): Record<string, any> {
  return {
    id: entity.id,
    name: entity.name,
    email: entity.email,
    description: entity.description,
    age: entity.age?.toString(),
    walletId: entity.walletId?.toString(),
    active: entity.active,
  };
}
```

## Regras Importantes

### ✅ Fazer

- Usar FieldsForm para formulários com metadados
- Validar com validateFieldValue/validateFieldValues
- Manter fields em arquivos separados por entidade
- Usar aria-* attributes para acessibilidade
- Mostrar feedback visual de erros
- Mapear payloads corretamente para backend

### ❌ Não Fazer

- Não usar validação HTML nativa como única validação
- Não duplicar lógica de validação
- Não expor dados sensíveis em console
- Não enviar campos não mapeados para backend

## Verificação

```bash
# TypeCheck
cd frontEnd
npm run typecheck

# Build
npm run build

# Quality gate
./scripts/quality-frontend.sh
```

## Referências

- `frontEnd/app/Infrastructure/DTO/EntityAttributes/`
- `frontEnd/app/components/forms/FieldsForm.tsx`
- `frontEnd/app/components/forms/FieldRenderer.tsx`
- Skill completa: `.windsurf/skills/appfinancasnew-frontend-fields-api/SKILL.md`
