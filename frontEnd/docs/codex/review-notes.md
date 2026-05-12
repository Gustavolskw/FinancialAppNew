# Notas De Avaliação Técnica Do Frontend

## Estado Atual

O frontend já possui telas reais do AppFinancasNew: login, cadastro e tela principal da carteira em `/principal`.

## Riscos Ativos

### Resíduos Do Template Inicial

Arquivos: `app/routes/home.tsx`, `app/welcome/*`, `README.md`

O fluxo principal já usa rotas do domínio financeiro, mas ainda existem arquivos residuais do template React Router que podem ser removidos quando não houver mais referência a eles.

### Catálogos Obrigatórios No Dashboard

A tela `/principal` depende de EntryType, ExpenseType e PaymentMethod retornados pelo backend para habilitar os modais de criação. Se esses catálogos estiverem vazios, os botões de cadastro ficam bloqueados até os dados existirem.

## Recomendações

- Modele os tipos TypeScript a partir das respostas reais do backend.
- Rode `npm run typecheck` antes de finalizar mudanças.
