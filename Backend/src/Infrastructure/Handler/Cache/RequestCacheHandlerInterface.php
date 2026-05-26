<?php

declare(strict_types=1);

namespace App\Infrastructure\Handler\Cache;

use App\Infrastructure\DTO\Configuration\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\Params\Interface\QueryParamsInterface;
use App\Infrastructure\Handler\Response\JsonResponseHandlerInterface;
use Symfony\Component\HttpFoundation\Request;

interface RequestCacheHandlerInterface
{
    public function supports(BaseEntityClassInterface $baseEntityClass): bool;

    public function get(
        BaseEntityClassInterface $baseEntityClass,
        Request $request,
        ?QueryParamsInterface $queryParams,
        ?int $id,
        ?int $userId,
        ?int $userRole,
        callable $resolver
    ): JsonResponseHandlerInterface;

    public function invalidateCacheableRequests(): void;
}
