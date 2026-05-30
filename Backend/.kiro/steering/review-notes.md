---
inclusion: auto
---

# Notas De Avaliação Técnica Do Backend

## Riscos Ativos

### PUT Sem id Vira Criação
`handleUpdate()` chama `save()` quando o Form DTO não tem `id`. Documentar em API pública.

### User Não Implementa Security Interfaces
Security Bundle existe, mas `User` não implementa `UserInterface`/`PasswordAuthenticatedUserInterface`. Proteção atual valida JWT stateless no `ActionManager`.

### JWT Stateless Sem Revogação Server-Side
`/login` gera JWT stateless. `/logoff` apenas confirma encerramento. Revogação exigirá blacklist ou tokens opacos.

### Autorização De Edição Corrigida (2026-05-25)
- Corrigido bug em `canAccessExistingRecord()` e `canModifyCatalogRecord()` que retornavam `true` para entidade `null`
- Habilitado Symfony Serializer em `framework.yaml` para `MapRequestPayload` funcionar

## Recomendações

- Criar field/output para coleções inversas (`OneToMany`)
- Ampliar suíte com testes funcionais para controllers
- Padronizar `declare(strict_types=1);` em arquivos novos
- Se autenticação avançar, implementar `UserInterface` e firewall real
