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
            return $this->authorizationResponse('Somente administradores podem alterar cadastros globais', Response::HTTP_FORBIDDEN);
        }

        if ($targetId === null) {
            return $this->canCreateRecord($entityClass, $currentUser, $formDto)
                ? null
                : $this->authorizationResponse('Usuário sem permissão para criar este registro', Response::HTTP_FORBIDDEN);
        }

        if (!$this->canAccessExistingRecord($baseEntityClass, $currentUser, $targetId)) {
            return $this->authorizationResponse('Usuário sem permissão para alterar este registro', Response::HTTP_FORBIDDEN);
        }

        if (!$this->canApplyOwnershipChange($entityClass, $currentUser, $formDto)) {
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
            default => null,
        };
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
        if ($this->recordAuthorizationUser instanceof UserEntity) {
            return $this->recordAuthorizationUser;
        }

        $payload = $this->authenticatedJwtPayload();
        $userId = isset($payload['sub']) && is_numeric($payload['sub']) ? (int) $payload['sub'] : null;

        if ($userId === null || $userId <= 0) {
            return null;
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

        $entity = $baseEntityClass->getRepository()->find($id);

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
            $entity instanceof PaymentMethodEntity => true,
            default => false,
        };
    }

    private function canCreateRecord(string $entityClass, UserEntity $currentUser, ?FormDtoInterface $formDto): bool
    {
        return match ($entityClass) {
            WalletEntity::class => $this->formInt($formDto, 'userId') === $currentUser->getId(),
            TransactionEntity::class => $this->walletIdBelongsToUser($this->formInt($formDto, 'walletId'), $currentUser),
            EntryEntity::class => $this->transactionIdBelongsToUser($this->formInt($formDto, 'transactionId'), $currentUser),
            ExpenseEntity::class => $this->transactionIdBelongsToUser($this->formInt($formDto, 'transactionId'), $currentUser),
            default => false,
        };
    }

    private function canApplyOwnershipChange(string $entityClass, UserEntity $currentUser, ?FormDtoInterface $formDto): bool
    {
        return match ($entityClass) {
            WalletEntity::class => $this->formInt($formDto, 'userId') === null
                || $this->formInt($formDto, 'userId') === $currentUser->getId(),
            TransactionEntity::class => $this->formInt($formDto, 'walletId') === null
                || $this->walletIdBelongsToUser($this->formInt($formDto, 'walletId'), $currentUser),
            EntryEntity::class => $this->formInt($formDto, 'transactionId') === null
                || $this->transactionIdBelongsToUser($this->formInt($formDto, 'transactionId'), $currentUser),
            ExpenseEntity::class => $this->formInt($formDto, 'transactionId') === null
                || $this->transactionIdBelongsToUser($this->formInt($formDto, 'transactionId'), $currentUser),
            UserEntity::class => true,
            default => false,
        };
    }

    private function walletIdBelongsToUser(?int $walletId, UserEntity $currentUser): bool
    {
        if ($walletId === null || $walletId <= 0) {
            return false;
        }

        $wallet = $currentUser->getUserWallet();

        return $wallet instanceof WalletEntity && $wallet->getId() === $walletId;
    }

    private function transactionIdBelongsToUser(?int $transactionId, UserEntity $currentUser): bool
    {
        if ($transactionId === null || $transactionId <= 0) {
            return false;
        }

        $transaction = $currentUser->getUserWallet()?->getWalletTransactions()
            ->filter(static fn (TransactionEntity $item): bool => $item->getId() === $transactionId)
            ->first();

        return $transaction instanceof TransactionEntity;
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
