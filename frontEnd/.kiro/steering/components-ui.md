---
inclusion: fileMatch
fileMatchPattern: "**/app/components/**,**/app/routes/**"
---

# Componentes E UI

## Princípios

- Mobile-first com breakpoints `sm:`, `md:`, `lg:`, `xl:`
- Touch-friendly targets (min 44px)
- Paleta: Blue primary, Green success, Yellow warning, Red error, Gray neutral
- UI utilitária, escaneável, objetiva

## Padrões De Componentes

### Botões
```tsx
<button className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
```

### Cards
```tsx
<div className="bg-white rounded-lg shadow-md p-4">
```

### Inputs
```tsx
<input className="border border-gray-300 rounded px-3 py-2 w-full focus:ring-2 focus:ring-blue-500">
```

### Grid Responsivo
```tsx
<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
```

## Reutilização Obrigatória

- Botões com mesmo comportamento visual compartilham componente
- Ícones centralizados como componente
- Modais usam modal base + composição
- Mensagens de sucesso/erro/loading são componentes reutilizáveis
- Dashboards separam cálculo de renderização
- Tailwind repetível vira componente/constante

## Acessibilidade

- `aria-*` attributes apropriados
- `htmlFor` em labels
- Foco visível em elementos interativos
- `type="button"` em botões não-submit
- Labels descritivos
- Contraste WCAG AA

## Navegação

- `AppSidebar.tsx`: navegação autenticada
- Modo colapsado: apenas ícones + tooltips
- Modo expandido: ícone + texto
- Link para dashboard e logout

## Permissões Na UI

- Quando o usuário não tiver permissão, oculte a ação completamente
- Não renderize botão desabilitado ou "Restrito"
- Se nenhum registro tiver ação disponível, remova a coluna de ações
- Backend é a barreira final de autorização
