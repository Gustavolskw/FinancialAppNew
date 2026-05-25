---
name: backend-actions
description: Skill especializada em Actions e ActionManager do backend - fluxo CRUD, hooks e orquestração
---

# Backend Actions Skill

Skill especializada para trabalhar com Actions e ActionManager no backend Symfony/PHP.

## Quando Usar

Use esta skill quando precisar:
- Entender o fluxo CRUD genérico
- Criar SpecificAction para comportamento customizado
- Implementar hooks de ciclo de vida
- Trabalhar com ActionManager
- Criar primary actions (login, logoff)
- Implementar autorização de registros

## Localização

`Backend/src/Infrastructure/Handler/Action/`

## Conceitos Principais

### ActionManager

Orquestrador central que:
- Recebe requests HTTP
- Valida autenticação JWT
- Valida autorização por dono/ADMIN
- Despacha para Action apropriada por método HTTP
- Gerencia cache de requests

### Action

Implementação genérica de CRUD que:
- Valida fields configurados
- Executa hooks de ciclo de vida
- Persiste/atualiza/deleta entidades
- Constrói respostas padronizadas

### SpecificAction

Hooks específicos por entidade para:
- Validações customizadas
- Transformações de dados
- Lógica de negócio específica
- Side effects (ex: criar wallet após criar user)

## Fluxo HTTP → Action

```
Request HTTP
    ↓
Controller (fino)
    ↓
ActionManager::handle()
    ↓
Autenticação JWT
    ↓
Autorização (dono/ADMIN)
    ↓
Cache lookup (GET)
    ↓
Dispatch por método HTTP
    ↓
Action (save/edit/view/listView/delete/status)
    ↓
Hooks SpecificAction
    ↓
Persistência Doctrine
    ↓
ResponseBuilder
    ↓
JSON Response
```

## Dispatch Por Método HTTP

| Método | Action | Comportamento |
|--------|--------|---------------|
| GET sem id | `listView()` | Lista com paginação e filtros |
| GET com id | `view($id)` | Visualiza um registro |
| POST | `save()` | Cria novo registro |
| PUT sem id | `save()` | Cria novo registro |
| PUT com id | `edit($id)` | Atualiza registro existente |
| PATCH | `edit($id)` | Atualiza parcialmente |
| DELETE | `delete($id)` | Remove registro |
| PATCH /status | `status($id, $status)` | Altera status |

## Fluxo De Criação (save)

```php
1. ActionManager::handle()
   - setFieldValues($formDto)
   
2. Action::save()
   - Valida todos os fields configurados
   
3. preActionValidation()
   - Validações customizadas
   
4. specificAction()
   - Lógica específica de criação
   
5. Action cria entidade Doctrine
   - Aplica fields à entidade
   
6. preSave()
   - Transformações antes de persistir
   
7. Action reaplica fields
   - Hooks podem ter mutado valores
   
8. persist() + flush()
   
9. setFieldsFromEntityData()
   - Atualiza DTO da entidade salva
   
10. afterAction()
    - Side effects pós-persistência
    
11. ResponseBuilder
    - Constrói resposta JSON
```

## Fluxo De Atualização (edit)

```php
1. ActionManager::handle()
   - setFieldValues($formDto)
   
2. Action::edit($id)
   - Valida apenas fields com valores
   - Carrega entidade existente
   
3. preActionValidation()
   
4. beforeUpdate()
   - Lógica antes de atualizar
   
5. Action aplica fields
   
6. preUpdate()
   - Transformações antes do flush
   
7. Action reaplica fields
   
8. flush()
   
9. afterUpdate()
   - Side effects pós-atualização
   
10. ResponseBuilder
```

## Criar SpecificAction

### Template Básico

```php
<?php

namespace App\Infrastructure\Handler\Action\Specific;

use App\Infrastructure\Handler\Action\BaseSpecificAction;
use App\Infrastructure\DTO\BaseEntityClassInterface;

class MinhaEntidadeSpecificAction extends BaseSpecificAction
{
    // Override apenas os hooks necessários
}
```

### Hooks Disponíveis

```php
// Validação antes de qualquer ação
public function preActionValidation(BaseEntityClassInterface $dto): bool|string
{
    // Retorne true para continuar
    // Retorne string com mensagem de erro para parar
    return true;
}

// Apenas na criação (POST)
public function specificAction(BaseEntityClassInterface $dto): bool|string
{
    // Lógica específica de criação
    return true;
}

// Antes de persistir (criação)
public function preSave(BaseEntityClassInterface $dto): bool|string
{
    // Transformações antes de persist()
    return true;
}

// Depois de persistir (criação)
public function afterAction(BaseEntityClassInterface $dto): bool|string
{
    // Side effects após flush
    return true;
}

// Antes de atualizar
public function beforeUpdate(BaseEntityClassInterface $dto): bool|string
{
    return true;
}

// Antes do flush (atualização)
public function preUpdate(BaseEntityClassInterface $dto): bool|string
{
    return true;
}

// Depois de atualizar
public function afterUpdate(BaseEntityClassInterface $dto): bool|string
{
    return true;
}

// Antes de deletar
public function beforeDelete(BaseEntityClassInterface $dto): bool|string
{
    return true;
}

// Depois de deletar
public function afterDelete(BaseEntityClassInterface $dto): bool|string
{
    return true;
}

// Antes de mudar status
public function beforeChangeStatus(BaseEntityClassInterface $dto): bool|string
{
    return true;
}

// Depois de mudar status
public function afterChangeStatus(BaseEntityClassInterface $dto): bool|string
{
    return true;
}
```

## Exemplos De SpecificAction

### Hash De Senha (UserSpecificAction)

```php
class UserSpecificAction extends BaseSpecificAction
{
    public function __construct(
        private readonly PasswordHasherInterface $passwordHasher
    ) {}

    public function preSave(BaseEntityClassInterface $dto): bool|string
    {
        $this->hashPasswordIfPresent($dto);
        return true;
    }

    public function preUpdate(BaseEntityClassInterface $dto): bool|string
    {
        $this->hashPasswordIfPresent($dto);
        return true;
    }

    private function hashPasswordIfPresent(BaseEntityClassInterface $dto): void
    {
        $passwordField = $dto->getFields()->getField('password');
        
        if ($passwordField && $passwordField->getValue()) {
            $plainPassword = $passwordField->getValue();
            $entity = $dto->getEntity();
            
            $hashedPassword = $this->passwordHasher->hashPassword(
                $entity,
                $plainPassword
            );
            
            $passwordField->fillValue($hashedPassword);
        }
    }
}
```

### Criar Wallet Após Criar User

```php
class UserSpecificAction extends BaseSpecificAction
{
    public function afterAction(BaseEntityClassInterface $dto): bool|string
    {
        $user = $dto->getEntity();
        
        // Criar wallet padrão
        $wallet = new Wallet();
        $wallet->setName('Carteira Principal');
        $wallet->setUser($user);
        $wallet->setStatus(true);
        
        $this->entityManager->persist($wallet);
        $this->entityManager->flush();
        
        return true;
    }
}
```

### Validação Customizada

```php
class EntrySpecificAction extends BaseSpecificAction
{
    public function preActionValidation(BaseEntityClassInterface $dto): bool|string
    {
        $walletId = $dto->getFields()->getField('wallet')?->getValue();
        $amount = $dto->getFields()->getField('amount')?->getValue();
        
        if ($amount && $amount <= 0) {
            return 'Valor deve ser maior que zero';
        }
        
        // Validar que wallet pertence ao usuário
        if ($walletId) {
            $wallet = $this->entityManager->find(Wallet::class, $walletId);
            if (!$wallet || $wallet->getUser()->getId() !== $this->currentUserId) {
                return 'Carteira não encontrada ou não pertence ao usuário';
            }
        }
        
        return true;
    }
}
```

### Gerenciar Transaction (Entry/Expense)

```php
class EntrySpecificAction extends BaseSpecificAction
{
    public function preSave(BaseEntityClassInterface $dto): bool|string
    {
        // Criar Transaction associada
        $transaction = new Transaction();
        $transaction->setAmount($dto->getFields()->getField('amount')->getValue());
        $transaction->setLocation($dto->getFields()->getField('location')->getValue());
        $transaction->setDate($dto->getFields()->getField('date')->getValue());
        // ... outros campos
        
        $this->entityManager->persist($transaction);
        
        // Associar à Entry
        $dto->getFields()->fillValue('transaction', $transaction);
        
        return true;
    }
    
    public function beforeDelete(BaseEntityClassInterface $dto): bool|string
    {
        // Deletar Transaction associada
        $entry = $dto->getEntity();
        $transaction = $entry->getTransaction();
        
        if ($transaction) {
            $this->entityManager->remove($transaction);
        }
        
        return true;
    }
}
```

## Autorização De Registros

### Regras Padrão

- **ADMIN**: Pode operar todos os registros
- **USER**: Pode operar apenas seus próprios registros

### Entidades Com Dono

```php
// User: próprio usuário
// Wallet: wallets do usuário
// Entry/Expense: transações de wallets do usuário
```

### Catálogos Auxiliares

```php
// EntryType, ExpenseType, PaymentMethod:
// - Usuários leem: defaults + próprios
// - Usuários criam: próprios
// - Usuários editam/excluem: apenas próprios não-default
// - ADMIN: acesso amplo
```

## Request Cache

### Entidades Cacheáveis

- `Wallet`
- `User`
- `EntryType`
- `ExpenseType`
- `PaymentMethod`

### Não Cacheáveis

- `Entry`
- `Expense`

### Cache Key

Inclui:
- Entity class
- Route
- Path
- Query params
- ID
- User ID
- User role

### Invalidação

Cache é invalidado após:
- POST 2xx
- PUT 2xx
- PATCH 2xx
- DELETE 2xx
- Status change 2xx

## Primary Actions

Para workflows fora do CRUD (ex: login, logoff):

```php
// Backend/src/Infrastructure/Handler/Action/PrimaryAction/AccessControlAction.php
class AccessControlAction
{
    public static function build(string $baseEntityClass): self
    {
        return new self($baseEntityClass);
    }
    
    public function login(LoginFormDto $formDto): JsonResponse
    {
        // Lógica de login
        // Gerar JWT
        // Retornar resposta
    }
    
    public function logoff(Request $request): JsonResponse
    {
        // Lógica de logoff
        // Invalidar token
        // Retornar resposta
    }
}
```

## Controller Fino

```php
#[Route('/api/minhaentidade')]
class MinhaEntidadeController extends AbstractController
{
    public function __construct(
        private readonly ActionManagerInterface $actionManager,
    ) {}

    #[Route('', name: 'minhaentidade_list', methods: ['GET'])]
    public function list(
        #[MapQueryString] MinhaEntidadeQueryDto $queryParams,
        EntityManagerInterface $entityManager,
        Request $request,
    ) {
        return $this->actionManager
            ->handle(MinhaEntidadeDto::build($entityManager), $request, $queryParams)
            ->output();
    }

    #[Route('/{id}', name: 'minhaentidade_view', methods: ['GET'])]
    public function view(
        int $id,
        EntityManagerInterface $entityManager,
        Request $request,
    ) {
        return $this->actionManager
            ->handle(MinhaEntidadeDto::build($entityManager), $request, id: $id)
            ->output();
    }

    #[Route('', name: 'minhaentidade_post', methods: ['POST'])]
    public function post(
        #[MapRequestPayload] MinhaEntidadeFormDto $formDto,
        EntityManagerInterface $entityManager,
        Request $request,
    ) {
        return $this->actionManager
            ->handle(MinhaEntidadeDto::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('', name: 'minhaentidade_patch', methods: ['PATCH'])]
    public function patch(
        #[MapRequestPayload] MinhaEntidadeFormDto $formDto,
        EntityManagerInterface $entityManager,
        Request $request,
    ) {
        return $this->actionManager
            ->handle(MinhaEntidadeDto::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/{id}/status', name: 'minhaentidade_status', methods: ['PATCH'])]
    public function status(
        int $id,
        #[MapRequestPayload] StatusFormDto $statusDto,
        EntityManagerInterface $entityManager,
        Request $request,
    ) {
        return $this->actionManager
            ->handleStatus(MinhaEntidadeDto::build($entityManager), $request, $statusDto, $id)
            ->output();
    }
}
```

## Regras Importantes

### ✅ Fazer

- Manter controllers finos
- Usar `ActionManager` injetado pelo container
- Criar `SpecificAction` apenas quando necessário
- Retornar `true` ou mensagem de erro dos hooks
- Usar hooks apropriados para cada fase do ciclo de vida
- Validar relações em `preActionValidation()`

### ❌ Não Fazer

- Não colocar lógica de negócio em controllers
- Não instanciar `new ActionManager()` manualmente
- Não duplicar lógica genérica em `SpecificAction`
- Não passar `Request` ou `ResponseBuilder` para hooks
- Não chamar `specificAction()` durante update

## Verificação

```bash
# Sintaxe PHP
php -l Backend/src/Infrastructure/Handler/Action/Specific/MinhaEntidadeSpecificAction.php

# Ver rotas
docker compose exec backend php bin/console debug:router

# Quality gate completo
./scripts/quality-backend.sh
```

## Referências

- `Backend/src/Infrastructure/Handler/Action/ActionManager.php`
- `Backend/src/Infrastructure/Handler/Action/Action.php`
- `Backend/src/Infrastructure/Handler/Action/BaseSpecificAction.php`
- `Backend/src/Infrastructure/Handler/Action/Specific/`
- Skill completa: `.windsurf/skills/appfinancasnew-backend-actions/SKILL.md`
