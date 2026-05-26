---
name: frontend-integrator
description: >
  Integration specialist combining frontend and backend knowledge for API contracts,
  endpoints, authentication flows, and full-stack feature development. Use when building
  features that require coordination between React frontend and Symfony backend.
---

# Frontend Integrator

Skill especializada em integração frontend-backend, reunindo conhecimento de ambos os lados da stack.

## Scope

Use quando precisar:
- Criar features full-stack completas
- Integrar novos endpoints do backend no frontend
- Debugar problemas de integração
- Validar contratos de API
- Implementar fluxos de autenticação
- Sincronizar mudanças entre frontend e backend

## Skills Incluídas

### Frontend
- **appfinancasnew-frontend-api**: Cliente HTTP, JWT, chamadas protegidas
- **appfinancasnew-frontend-fields-api**: Formulários com Fields, modais CRUD, integrações API
- **frontend-fields-forms**: Formulários dinâmicos, validação, integração com API

### Backend
- **appfinancasnew-backend-actions**: ActionManager, Actions, CRUD, endpoints
- **appfinancasnew-backend-entity-dtos**: Configurations configuráveis, output, contratos
- **appfinancasnew-backend-helpers**: Response builders, output helpers, auth

### General
- **appfinancasnew-project**: Contexto geral do monorepo

## Arquitetura de Integração

### Fluxo Completo

```
Frontend (React Router)
    ↓ HTTP Request (Bearer JWT)
Backend Controller (Symfony)
    ↓ ActionManager
Action + Configuration
    ↓ Response Builder
JSON Response
    ↓ API Client
Frontend State Update
    ↓ UI Re-render
```

### Contratos de API

**Request:**
```typescript
// Frontend
const response = await apiClient.post('/api/entries', {
  amount: 100.50,
  description: "Salary",
  date: "2024-01-15",
  month: 1,
  year: 2024,
  walletId: 1,
  entryTypeId: 2
});
```

**Backend Controller:**
```php
#[Route('/api/entries', methods: ['POST'])]
public function create(
    Request $request,
    ActionManager $actionManager,
    #[MapRequestPayload] EntryFormDTO $dto
): JsonResponse {
    return $actionManager->save(Entry::class, $dto);
}
```

**Response:**
```json
{
  "message": "Entry created successfully",
  "statusCode": 201,
  "data": {
    "entry": {
      "id": 123,
      "amount": 100.50,
      "description": "Salary",
      "date": "2024-01-15",
      "wallet": { "id": 1, "name": "Main" },
      "entryType": { "id": 2, "name": "Salary" }
    }
  }
}
```

## Autenticação e Autorização

### Login Flow

**Frontend:**
```typescript
// app/Infrastructure/Api/auth.ts
export async function login(email: string, password: string) {
  const response = await fetch(`${API_BASE_URL}/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  
  const data = await response.json();
  
  if (data.statusCode === 200) {
    sessionStorage.setItem('token', data.data.token);
    sessionStorage.setItem('user', JSON.stringify(data.data.user));
  }
  
  return data;
}
```

**Backend:**
```php
// LoginAction.php
public function execute(LoginFormDTO $dto): array
{
    $user = $this->userRepository->findOneBy(['email' => $dto->email]);
    
    if (!$user || !$this->passwordHasher->isPasswordValid($user, $dto->password)) {
        throw new AuthenticationException('Invalid credentials');
    }
    
    $token = $this->jwtManager->create($user);
    
    return [
        'token' => $token,
        'user' => $this->userDTO->output($user)
    ];
}
```

### Protected Requests

**Frontend:**
```typescript
// app/Infrastructure/Api/client.ts
export const apiClient = {
  async get(endpoint: string) {
    const token = sessionStorage.getItem('token');
    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    });
    return response.json();
  }
};
```

**Backend:**
```php
// JWT validation happens automatically via security.yaml
// RecordAuthorizationHelperTrait applies ownership check
protected function applyRecordAuthorization(object $entity): void
{
    $user = $this->security->getUser();
    
    if (!$this->security->isGranted(RolesEnum::ADM->value)) {
        if ($entity->getUser()->getId() !== $user->getId()) {
            throw new AccessDeniedException();
        }
    }
}
```

## Mapeamento de Endpoints

### User Management

| Method | Endpoint | Frontend | Backend |
|--------|----------|----------|---------|
| POST | `/register` | `auth.register()` | `UserController::create()` |
| POST | `/login` | `auth.login()` | `LoginAction::execute()` |
| POST | `/logoff` | `auth.logout()` | `LogoffAction::execute()` |
| GET | `/user/{id}` | `users.getById()` | `UserController::show()` |
| PUT | `/user/{id}` | `users.update()` | `UserController::edit()` |
| PATCH | `/user/{id}/status` | `users.changeStatus()` | `UserController::status()` |

### Wallets

| Method | Endpoint | Frontend | Backend |
|--------|----------|----------|---------|
| GET | `/wallets` | `apiClient.get('/wallets')` | `WalletController::index()` |
| POST | `/wallet` | `apiClient.post('/wallet', dto)` | `WalletController::create()` |
| GET | `/wallet/{id}` | `apiClient.get('/wallet/{id}')` | `WalletController::show()` |
| PUT | `/wallet/{id}` | `apiClient.put('/wallet/{id}', dto)` | `WalletController::edit()` |

### Entries & Expenses

| Method | Endpoint | Frontend | Backend |
|--------|----------|----------|---------|
| GET | `/entries?month=1&year=2024` | `movements.getEntries()` | `EntryController::index()` |
| POST | `/entry` | `movements.createEntry()` | `EntryController::create()` |
| GET | `/expenses?month=1&year=2024` | `movements.getExpenses()` | `ExpenseController::index()` |
| POST | `/expense` | `movements.createExpense()` | `ExpenseController::create()` |

### Catalogs

| Method | Endpoint | Frontend | Backend |
|--------|----------|----------|---------|
| GET | `/entry-types` | `catalogs.getEntryTypes()` | `EntryTypeController::index()` |
| GET | `/expense-types` | `catalogs.getExpenseTypes()` | `ExpenseTypeController::index()` |
| GET | `/payment-methods` | `catalogs.getPaymentMethods()` | `PaymentMethodController::index()` |

## Payloads e DTOs

### Entry/Expense Payload

**Frontend Form:**
```typescript
interface MovementFormData {
  amount: number;
  description: string;
  location?: string;
  date: string;
  month: number;
  year: number;
  walletId: number;
  entryTypeId?: number;  // Entry only
  expenseTypeId?: number; // Expense only
  paymentMethodId?: number;
}
```

**Backend DTO:**
```php
class EntryFormDTO
{
    public float $amount;
    public string $description;
    public ?string $location = null;
    public string $date;
    public int $month;
    public int $year;
    public int $walletId;
    public int $entryTypeId;
    public ?int $paymentMethodId = null;
}
```

### Field Mapping

**Frontend Fields:**
```typescript
const entryFields = [
  { name: 'amount', type: 'number', required: true },
  { name: 'description', type: 'text', required: true },
  { name: 'date', type: 'date', required: true },
  { name: 'walletId', type: 'select', required: true, options: wallets },
  { name: 'entryTypeId', type: 'select', required: true, options: entryTypes }
];
```

**Backend Fields:**
```php
protected function configureFields(): array
{
    return [
        new NumberField('amount', required: true),
        new TextField('description', required: true),
        new DateField('date', required: true),
        new RelationField('wallet', Wallet::class, required: true),
        new RelationField('entryType', EntryType::class, required: true)
    ];
}
```

## Validação em Camadas

### Frontend (UX)
```typescript
function validateAmount(value: number): string | null {
  if (value <= 0) return 'Amount must be positive';
  if (value > 999999.99) return 'Amount too large';
  return null;
}
```

### Backend (Domain)
```php
class NumberField extends FieldsAttribute
{
    public function validate(mixed $value): void
    {
        if (!is_numeric($value)) {
            throw new ValidationException('Must be a number');
        }
        
        if ($this->min !== null && $value < $this->min) {
            throw new ValidationException("Minimum value is {$this->min}");
        }
    }
}
```

## Error Handling

### Frontend
```typescript
try {
  const response = await apiClient.post('/entry', formData);
  
  if (response.statusCode >= 400) {
    showError(response.message);
    return;
  }
  
  showSuccess(response.message);
  navigate('/entries');
  
} catch (error) {
  showError('Network error. Please try again.');
}
```

### Backend
```php
try {
    $this->actionManager->save(Entry::class, $dto);
} catch (ValidationException $e) {
    return new JsonResponse([
        'message' => $e->getMessage(),
        'statusCode' => 400
    ], 400);
} catch (\Exception $e) {
    return new JsonResponse([
        'message' => 'Internal server error',
        'statusCode' => 500
    ], 500);
}
```

## Dashboard Integration

### Frontend Request
```typescript
const dashboardData = await apiClient.get(
  `/dashboard?month=${month}&year=${year}`
);

// Response
{
  statusCode: 200,
  data: {
    summary: {
      totalEntries: 5000.00,
      totalExpenses: 3000.00,
      balance: 2000.00
    },
    entriesByType: [...],
    expensesByType: [...]
  }
}
```

### Backend Response Builder
```php
public function buildDashboardResponse(int $month, int $year, User $user): array
{
    return [
        'summary' => $this->getSummary($month, $year, $user),
        'entriesByType' => $this->getEntriesByType($month, $year, $user),
        'expensesByType' => $this->getExpensesByType($month, $year, $user)
    ];
}
```

## Cache Strategy

### Backend (Request Cache)
```php
// Only cache GET requests for:
// - Wallet, User, EntryType, ExpenseType, PaymentMethod
// Never cache Entry and Expense

// Cache key includes:
// - entity, route, path, query params, id, user id, user role

// Invalidate after:
// - 2xx POST, PUT, PATCH, DELETE
// - Status changes
```

### Frontend (No Cache)
```typescript
// Don't cache API responses in frontend
// Always fetch fresh data
// Backend handles caching strategy
```

## Testing Integration

### Frontend Test
```typescript
test('creates entry successfully', async () => {
  const formData = {
    amount: 100,
    description: 'Test',
    date: '2024-01-15',
    walletId: 1,
    entryTypeId: 2
  };
  
  const response = await movements.createEntry(formData);
  
  expect(response.statusCode).toBe(201);
  expect(response.data.entry.amount).toBe(100);
});
```

### Backend Test
```php
public function testCreateEntry(): void
{
    $client = static::createClient();
    $client->request('POST', '/api/entry', [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
    ], json_encode([
        'amount' => 100,
        'description' => 'Test',
        'date' => '2024-01-15',
        'walletId' => 1,
        'entryTypeId' => 2
    ]));
    
    $this->assertResponseIsSuccessful();
    $data = json_decode($client->getResponse()->getContent(), true);
    $this->assertEquals(201, $data['statusCode']);
}
```

## Troubleshooting

### CORS Issues
```php
// Backend: config/packages/nelmio_cors.yaml
nelmio_cors:
    defaults:
        origin_regex: true
        allow_origin: ['%env(CORS_ALLOW_ORIGIN)%']
        allow_methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']
        allow_headers: ['Content-Type', 'Authorization']
```

### JWT Expiration
```typescript
// Frontend: Refresh token or redirect to login
if (response.statusCode === 401) {
  sessionStorage.clear();
  navigate('/login');
}
```

### Payload Mismatch
- Verifique `MapRequestPayload` no backend
- Confirme nomes de campos (camelCase vs snake_case)
- Valide tipos de dados (string vs number)

## Comandos Úteis

```bash
# Ver rotas backend
docker compose exec backend php bin/console debug:router

# Testar endpoint com curl
curl -X POST http://localhost:8000/api/entry \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"amount":100,"description":"Test",...}'

# Logs backend
docker compose logs -f backend

# Logs frontend
docker compose logs -f frontend
```

## Próximos Passos

- Para desenvolvimento focado em frontend: `/skill frontend-specialist`
- Para desenvolvimento focado em backend: `/skill backend-specialist`
- Para geração de entidades: `/skill backend-entity-generator`
