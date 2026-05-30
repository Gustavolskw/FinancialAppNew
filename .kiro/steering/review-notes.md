---
inclusion: auto
---

# Notas De Avaliação Técnica

Achados importantes para evitar que padrões problemáticos continuem sem perceber.

## Pontos Fortes

- Controllers delegam lógica e ficam fáceis de ler
- Separação entre entidade Doctrine, DTO de API, DTO de formulário e response builder
- Fluxo de campos configuráveis reduz duplicação em CRUDs parecidos
- Validação de senha próxima da definição do campo
- Hash de senha em hook específico de usuário
- CORS, Doctrine, migrations e Docker encaminhados

## Riscos Ativos

### PUT Sem id Vira Criação
`handleUpdate()` chama `save()` quando o Form DTO não tem `id`. Documentar em API pública.

### User Não Implementa Security Interfaces
Security Bundle existe, mas `User` não implementa `UserInterface`/`PasswordAuthenticatedUserInterface`. Proteção atual valida JWT stateless no `ActionManager`.

### JWT Stateless Sem Revogação Server-Side
`/login` gera JWT stateless. `/logoff` apenas confirma encerramento. Revogação exigirá blacklist ou tokens opacos.

### Frontend: Resíduos Do Template Inicial
Arquivos `app/routes/home.tsx`, `app/welcome/*`, `README.md` do template React Router ainda existem.

### Frontend: Catálogos Obrigatórios No Dashboard
A tela `/principal` depende de EntryType, ExpenseType e PaymentMethod. Se vazios, botões de cadastro ficam bloqueados.

## Recomendações

- Criar field/output para coleções inversas (`OneToMany`)
- Ampliar suíte com testes funcionais para controllers
- Padronizar `declare(strict_types=1);` em arquivos novos
- Se autenticação avançar, implementar `UserInterface` e firewall real
- Modelar tipos TypeScript a partir das respostas reais do backend
- Rodar `npm run typecheck` antes de finalizar mudanças no frontend
