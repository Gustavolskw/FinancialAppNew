<?php

declare(strict_types=1);

namespace App\Infrastructure\Helper\Auth;

use App\Entity\Entry as EntryEntity;
use App\Entity\EntryType as EntryTypeEntity;
use App\Entity\Expense as ExpenseEntity;
use App\Entity\ExpenseType as ExpenseTypeEntity;
use App\Entity\PaymentMethod as PaymentMethodEntity;
use App\Entity\Transaction as TransactionEntity;
use App\Entity\User as UserEntity;
use App\Entity\Wallet as WalletEntity;
use App\Infrastructure\DTO\EntityAttributes\Enum\RolesEnum;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\Forms\FormDtoInterface;
use App\Infrastructure\DTO\Response\ResponseBuilder;
use App\Infrastructure\Handler\Response\JsonResponseHandler;
use App\Infrastructure\Handler\Response\JsonResponseHandlerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait RecordAuthorizationHelperTrait
{
    private ?UserEntity $recordAuthorizationUser = null;

    /**
     * @var array<string, object|null>
     */
    private array $recordAuthorizationEntityCache = [];

    protected function resetRecordAuthorizationState(): void
    {
        $this->recordAuthorizationUser = null;
        $this->recordAuthorizationEntityCache = [];
    }

    protected function authorizeRecordAccess(
        BaseEntityClassInterface $baseEntityClass,
        Request $request,
        ?FormDtoInterface $formDto = null,
        ?int $id = null
    ): ?JsonResponseHandlerInterface {
        $currentUser = $this->currentAuthenticatedUser($baseEntityClass);

        if (!$currentUser instanceof UserEntity) {
            return $this->authorizationResponse('Usuário autenticado não encontrado', Response::HTTP_UNAUTHORIZED);
        }

        if ($currentUser->isStatus() === false) {
            return $this->authorizationResponse('Usuário autenticado está inativo', Response::HTTP_FORBIDDEN);
        }

        $method = $request->getMethod();
        $entityClass = $baseEntityClass->getEntityClass();
        $targetId = $id ?? $this->formId($formDto);
        $isUserAdminCreateRoute = $request->attributes->get('_route') === 'userAdminPost';

        if (
            $entityClass === UserEntity::class
            && $targetId === null
            && !$isUserAdminCreateRoute
            && (
                $this->formInt($formDto, 'role') !== null
                || $this->requestPayloadHas($request, 'role')
            )
        ) {
            return $this->authorizationResponse(
                'Perfil de acesso não pode ser enviado na criação normal de usuário',
                Response::HTTP_FORBIDDEN
            );
        }

        if ($this->isAdmin($currentUser)) {
            return null;
        }

        if ($method === Request::METHOD_GET) {
            if ($id === null) {
                return null;
            }

            return $this->canAccessExistingRecord($baseEntityClass, $currentUser, $id)
                ? null
                : $this->authorizationResponse('Usuário sem permissão para acessar este registro', Response::HTTP_FORBIDDEN);
        }

        if ($this->isGlobalCatalogEntity($entityClass)) {
            if ($targetId === null) {
                return null;
            }

            if ($this->canModifyCatalogRecord($baseEntityClass, $currentUser, $targetId)) {
                return null;
            }

            $message = $this->catalogRecordIsDefault($baseEntityClass, $targetId)
                ? 'Cadastros auxiliares padrão só podem ser alterados por administradores'
                : 'Usuário sem permissão para alterar este cadastro auxiliar';

            return $this->authorizationResponse($message, Response::HTTP_FORBIDDEN);
        }

        if ($targetId === null) {
            return $this->canCreateRecord($baseEntityClass, $currentUser, $formDto)
                ? null
                : $this->authorizationResponse('Usuário sem permissão para criar este registro', Response::HTTP_FORBIDDEN);
        }

        if (!$this->canAccessExistingRecord($baseEntityClass, $currentUser, $targetId)) {
            return $this->authorizationResponse('Usuário sem permissão para alterar este registro', Response::HTTP_FORBIDDEN);
        }

        if (!$this->canApplyOwnershipChange($baseEntityClass, $currentUser, $formDto)) {
            return $this->authorizationResponse('Usuário sem permissão para vincular este registro', Response::HTTP_FORBIDDEN);
        }

        if ($entityClass === UserEntity::class && $this->formInt($formDto, 'role') !== null) {
            return $this->authorizationResponse('Somente administradores podem alterar perfil de acesso', Response::HTTP_FORBIDDEN);
        }

        return null;
    }

    protected function recordListQueryRestriction(BaseEntityClassInterface $baseEntityClass): ?\Closure
    {
        $currentUser = $this->currentAuthenticatedUser($baseEntityClass);

        if (!$currentUser instanceof UserEntity || $this->isAdmin($currentUser)) {
            return null;
        }

        $entityClass = $baseEntityClass->getEntityClass();
        $currentWallet = $currentUser->getUserWallet();

        return match ($entityClass) {
            UserEntity::class => static function (QueryBuilder $qb) use ($currentUser): void {
                $alias = $qb->getRootAliases()[0];
                $qb->andWhere(sprintf('%s.id = :securityCurrentUserId', $alias))
                    ->setParameter('securityCurrentUserId', $currentUser->getId());
            },
            WalletEntity::class => static function (QueryBuilder $qb) use ($currentUser): void {
                $alias = $qb->getRootAliases()[0];
                $qb->andWhere(sprintf('%s.walletUser = :securityCurrentUser', $alias))
                    ->setParameter('securityCurrentUser', $currentUser);
            },
            TransactionEntity::class => static function (QueryBuilder $qb) use ($currentWallet): void {
                $alias = $qb->getRootAliases()[0];
                self::restrictByCurrentWallet($qb, sprintf('%s.transactionWallet', $alias), $currentWallet);
            },
            EntryEntity::class => static function (QueryBuilder $qb) use ($currentWallet): void {
                $alias = $qb->getRootAliases()[0];
                $qb->leftJoin(sprintf('%s.transaction', $alias), 'securityEntryTransaction');
                self::restrictByCurrentWallet($qb, 'securityEntryTransaction.transactionWallet', $currentWallet);
            },
            ExpenseEntity::class => static function (QueryBuilder $qb) use ($currentWallet): void {
                $alias = $qb->getRootAliases()[0];
                $qb->leftJoin(sprintf('%s.expenseTransaction', $alias), 'securityExpenseTransaction');
                self::restrictByCurrentWallet($qb, 'securityExpenseTransaction.transactionWallet', $currentWallet);
            },
            EntryTypeEntity::class,
            ExpenseTypeEntity::class,
            PaymentMethodEntity::class => static function (QueryBuilder $qb) use ($currentUser): void {
                $alias = $qb->getRootAliases()[0];
                $qb->andWhere(sprintf('(%s.isDefault = :securityDefaultCatalog OR %s.user = :securityCatalogUser)', $alias, $alias))
                    ->setParameter('securityDefaultCatalog', true)
                    ->setParameter('securityCatalogUser', $currentUser);
            },
            default => null,
        };
    }

    private function applyAuthenticatedCatalogDefaults(BaseEntityClassInterface $baseEntityClass): void
    {
        if (!$this->isGlobalCatalogEntity($baseEntityClass->getEntityClass())) {
            return;
        }

        $currentUser = $this->currentAuthenticatedUser($baseEntityClass);

        if (!$currentUser instanceof UserEntity) {
            return;
        }

        $isDefaultField = $baseEntityClass->getFields()->getField('isDefault');
        if ($isDefaultField !== null) {
            $isDefaultField->setValue(false);
        }

        $userField = $baseEntityClass->getFields()->getField('user');
        if ($userField !== null) {
            $userField->setValue($currentUser->getId());
        }
    }

    private static function restrictByCurrentWallet(QueryBuilder $qb, string $walletPath, ?WalletEntity $currentWallet): void
    {
        if (!$currentWallet instanceof WalletEntity) {
            $qb->andWhere('1 = 0');
            return;
        }

        $qb->andWhere(sprintf('%s = :securityCurrentWallet', $walletPath))
            ->setParameter('securityCurrentWallet', $currentWallet);
    }

    private function currentAuthenticatedUser(BaseEntityClassInterface $baseEntityClass): ?UserEntity
    {
        $userId = $this->authenticatedTokenUserId();

        if ($userId === null || $userId <= 0) {
            $this->recordAuthorizationUser = null;

            return null;
        }

        if (
            $this->recordAuthorizationUser instanceof UserEntity
            && $this->recordAuthorizationUser->getId() === $userId
        ) {
            return $this->recordAuthorizationUser;
        }

        $user = $baseEntityClass->getEntityManager()
            ->getRepository(UserEntity::class)
            ->find($userId);

        if (!$user instanceof UserEntity) {
            return null;
        }

        $this->recordAuthorizationUser = $user;

        return $this->recordAuthorizationUser;
    }

    private function authenticatedTokenUserId(): ?int
    {
        $payload = $this->authenticatedJwtPayload();

        return isset($payload['sub']) && is_numeric($payload['sub']) ? (int) $payload['sub'] : null;
    }

    private function isAdmin(UserEntity $user): bool
    {
        return $user->getRole() === RolesEnum::ADM->value();
    }

    private function canAccessExistingRecord(
        BaseEntityClassInterface $baseEntityClass,
        UserEntity $currentUser,
        int $id
    ): bool {
        if ($id <= 0) {
            return false;
        }

        $entity = $this->recordAuthorizationEntity($baseEntityClass, $baseEntityClass->getEntityClass(), $id);

        if ($entity === null) {
            return true;
        }

        return $this->entityBelongsToUser($entity, $currentUser);
    }

    private function entityBelongsToUser(object $entity, UserEntity $currentUser): bool
    {
        return match (true) {
            $entity instanceof UserEntity => $entity->getId() === $currentUser->getId(),
            $entity instanceof WalletEntity => $this->walletBelongsToUser($entity, $currentUser),
            $entity instanceof TransactionEntity => $this->transactionBelongsToUser($entity, $currentUser),
            $entity instanceof EntryEntity => $this->transactionBelongsToUser($entity->getTransaction(), $currentUser),
            $entity instanceof ExpenseEntity => $this->transactionBelongsToUser($entity->getExpenseTransaction(), $currentUser),
            $entity instanceof EntryTypeEntity,
            $entity instanceof ExpenseTypeEntity,
            $entity instanceof PaymentMethodEntity => $this->catalogVisibleToUser($entity, $currentUser),
            default => false,
        };
    }

    private function canCreateRecord(
        BaseEntityClassInterface $baseEntityClass,
        UserEntity $currentUser,
        ?FormDtoInterface $formDto
    ): bool {
        $entityClass = $baseEntityClass->getEntityClass();

        return match ($entityClass) {
            WalletEntity::class => $this->formInt($formDto, 'userId') === $currentUser->getId(),
            TransactionEntity::class => $this->walletIdBelongsToUser($this->formInt($formDto, 'walletId'), $currentUser),
            EntryEntity::class => $this->walletIdBelongsToUser($this->formInt($formDto, 'walletId'), $currentUser)
                && $this->catalogIdVisibleToUser($baseEntityClass, EntryTypeEntity::class, $this->formInt($formDto, 'entryTypeId'), $currentUser),
            ExpenseEntity::class => $this->walletIdBelongsToUser($this->formInt($formDto, 'walletId'), $currentUser)
                && $this->catalogIdVisibleToUser($baseEntityClass, ExpenseTypeEntity::class, $this->formInt($formDto, 'expenseTypeId'), $currentUser)
                && $this->catalogIdVisibleToUser($baseEntityClass, PaymentMethodEntity::class, $this->formInt($formDto, 'paymentMethodId'), $currentUser),
            EntryTypeEntity::class,
            ExpenseTypeEntity::class,
            PaymentMethodEntity::class => true,
            default => false,
        };
    }

    private function canApplyOwnershipChange(
        BaseEntityClassInterface $baseEntityClass,
        UserEntity $currentUser,
        ?FormDtoInterface $formDto
    ): bool {
        $entityClass = $baseEntityClass->getEntityClass();

        return match ($entityClass) {
            WalletEntity::class => $this->formInt($formDto, 'userId') === null
                || $this->formInt($formDto, 'userId') === $currentUser->getId(),
            TransactionEntity::class => $this->formInt($formDto, 'walletId') === null
                || $this->walletIdBelongsToUser($this->formInt($formDto, 'walletId'), $currentUser),
            EntryEntity::class => (
                $this->formInt($formDto, 'walletId') === null
                || $this->walletIdBelongsToUser($this->formInt($formDto, 'walletId'), $currentUser)
            )
                && $this->nullableCatalogIdVisibleToUser($baseEntityClass, EntryTypeEntity::class, $this->formInt($formDto, 'entryTypeId'), $currentUser),
            ExpenseEntity::class => (
                $this->formInt($formDto, 'walletId') === null
                || $this->walletIdBelongsToUser($this->formInt($formDto, 'walletId'), $currentUser)
            )
                && $this->nullableCatalogIdVisibleToUser($baseEntityClass, ExpenseTypeEntity::class, $this->formInt($formDto, 'expenseTypeId'), $currentUser)
                && $this->nullableCatalogIdVisibleToUser($baseEntityClass, PaymentMethodEntity::class, $this->formInt($formDto, 'paymentMethodId'), $currentUser),
            UserEntity::class => true,
            EntryTypeEntity::class,
            ExpenseTypeEntity::class,
            PaymentMethodEntity::class => true,
            default => false,
        };
    }

    private function canModifyCatalogRecord(
        BaseEntityClassInterface $baseEntityClass,
        UserEntity $currentUser,
        int $id
    ): bool {
        if ($id <= 0) {
            return false;
        }

        $entity = $this->recordAuthorizationEntity($baseEntityClass, $baseEntityClass->getEntityClass(), $id);

        if ($entity === null) {
            return true;
        }

        if (!$this->isCatalogEntity($entity) || $this->catalogIsDefault($entity)) {
            return false;
        }

        return $this->catalogBelongsToUser($entity, $currentUser);
    }

    private function catalogRecordIsDefault(BaseEntityClassInterface $baseEntityClass, int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $entity = $this->recordAuthorizationEntity($baseEntityClass, $baseEntityClass->getEntityClass(), $id);

        return is_object($entity) && $this->catalogIsDefault($entity);
    }

    private function nullableCatalogIdVisibleToUser(
        BaseEntityClassInterface $baseEntityClass,
        string $catalogClass,
        ?int $id,
        UserEntity $currentUser
    ): bool {
        if ($id === null) {
            return true;
        }

        return $this->catalogIdVisibleToUser($baseEntityClass, $catalogClass, $id, $currentUser);
    }

    private function catalogIdVisibleToUser(
        BaseEntityClassInterface $baseEntityClass,
        string $catalogClass,
        ?int $id,
        UserEntity $currentUser
    ): bool {
        if ($id === null || $id <= 0 || !$this->isGlobalCatalogEntity($catalogClass)) {
            return false;
        }

        $entity = $this->recordAuthorizationCatalogEntity($baseEntityClass, $catalogClass, $id);

        return $entity !== null && $this->catalogVisibleToUser($entity, $currentUser);
    }

    private function recordAuthorizationCatalogEntity(
        BaseEntityClassInterface $baseEntityClass,
        string $catalogClass,
        int $id
    ): ?object {
        if (!$this->isGlobalCatalogEntity($catalogClass)) {
            return null;
        }

        return $this->recordAuthorizationEntity($baseEntityClass, $catalogClass, $id);
    }

    private function recordAuthorizationEntity(
        BaseEntityClassInterface $baseEntityClass,
        string $entityClass,
        int $id
    ): ?object {
        if ($id <= 0) {
            return null;
        }

        $cacheKey = $entityClass . '#' . $id;
        if (array_key_exists($cacheKey, $this->recordAuthorizationEntityCache)) {
            return $this->recordAuthorizationEntityCache[$cacheKey];
        }

        $repository = $entityClass === $baseEntityClass->getEntityClass()
            ? $baseEntityClass->getRepository()
            : $baseEntityClass->getEntityManager()->getRepository($entityClass);

        $entity = $repository->find($id);
        $this->recordAuthorizationEntityCache[$cacheKey] = is_object($entity) ? $entity : null;

        return $this->recordAuthorizationEntityCache[$cacheKey];
    }

    private function catalogVisibleToUser(object $entity, UserEntity $currentUser): bool
    {
        return $this->catalogIsDefault($entity)
            || $this->catalogBelongsToUser($entity, $currentUser);
    }

    private function catalogBelongsToUser(object $entity, UserEntity $currentUser): bool
    {
        $owner = match (true) {
            $entity instanceof EntryTypeEntity => $entity->getUser(),
            $entity instanceof ExpenseTypeEntity => $entity->getUser(),
            $entity instanceof PaymentMethodEntity => $entity->getUser(),
            default => null,
        };

        return $owner instanceof UserEntity && $owner->getId() === $currentUser->getId();
    }

    private function catalogIsDefault(object $entity): bool
    {
        return match (true) {
            $entity instanceof EntryTypeEntity => $entity->isDefault() === true,
            $entity instanceof ExpenseTypeEntity => $entity->isDefault() === true,
            $entity instanceof PaymentMethodEntity => $entity->isDefault() === true,
            default => false,
        };
    }

    private function isCatalogEntity(object $entity): bool
    {
        return $entity instanceof EntryTypeEntity
            || $entity instanceof ExpenseTypeEntity
            || $entity instanceof PaymentMethodEntity;
    }

    private function walletIdBelongsToUser(?int $walletId, UserEntity $currentUser): bool
    {
        if ($walletId === null || $walletId <= 0) {
            return false;
        }

        $wallet = $currentUser->getUserWallet();

        return $wallet instanceof WalletEntity && $wallet->getId() === $walletId;
    }

    private function walletBelongsToUser(?WalletEntity $wallet, UserEntity $currentUser): bool
    {
        return $wallet instanceof WalletEntity
            && $wallet->getWalletUser() instanceof UserEntity
            && $wallet->getWalletUser()->getId() === $currentUser->getId();
    }

    private function transactionBelongsToUser(?TransactionEntity $transaction, UserEntity $currentUser): bool
    {
        return $transaction instanceof TransactionEntity
            && $this->walletBelongsToUser($transaction->getTransactionWallet(), $currentUser);
    }

    private function formId(?FormDtoInterface $formDto): ?int
    {
        return $this->formInt($formDto, 'id');
    }

    private function formInt(?FormDtoInterface $formDto, string $property): ?int
    {
        if ($formDto === null || !property_exists($formDto, $property)) {
            return null;
        }

        $value = $formDto->$property;

        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : 0;
    }

    private function requestPayloadHas(Request $request, string $property): bool
    {
        if ($request->getContent() === '') {
            return false;
        }

        $payload = json_decode($request->getContent(), true);

        return is_array($payload) && array_key_exists($property, $payload);
    }

    private function isGlobalCatalogEntity(string $entityClass): bool
    {
        return in_array($entityClass, [
            EntryTypeEntity::class,
            ExpenseTypeEntity::class,
            PaymentMethodEntity::class,
        ], true);
    }

    private function authorizationResponse(string $message, int $statusCode): JsonResponseHandlerInterface
    {
        return JsonResponseHandler::create(ResponseBuilder::build($message, $statusCode));
    }
}
