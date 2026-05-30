---
inclusion: auto
---

# Notas De Avaliação Técnica Do Frontend

## Riscos Ativos

### Resíduos Do Template Inicial
Arquivos: `app/routes/home.tsx`, `app/welcome/*`, `README.md`
O fluxo principal já usa rotas do domínio financeiro, mas resíduos do template React Router ainda existem.

### Catálogos Obrigatórios No Dashboard
A tela `/principal` depende de EntryType, ExpenseType e PaymentMethod retornados pelo backend. Se vazios, botões de cadastro ficam bloqueados.

## Recomendações

- Modele tipos TypeScript a partir das respostas reais do backend
- Rode `npm run typecheck` antes de finalizar mudanças
- Remova resíduos do template quando não houver mais referência
- Trate gracefully catálogos vazios no dashboard
