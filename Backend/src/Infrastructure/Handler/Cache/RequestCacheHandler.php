<?php

declare(strict_types=1);

namespace App\Infrastructure\Handler\Cache;

use App\Entity\EntryType;
use App\Entity\ExpenseType;
use App\Entity\PaymentMethod;
use App\Entity\User;
use App\Entity\Wallet;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\Params\Interface\QueryParamsInterface;
use App\Infrastructure\DTO\Response\CachedResponseData;
use App\Infrastructure\Handler\Response\JsonResponseHandler;
use App\Infrastructure\Handler\Response\JsonResponseHandlerInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\HttpFoundation\Request;

final class RequestCacheHandler implements RequestCacheHandlerInterface
{
    private const int LIFETIME_SECONDS = 600;
    private const string TAG_ALL = 'appfinancas_cacheable_requests';

    /**
     * @var array<class-string, true>
     */
    private const array CACHEABLE_ENTITIES = [
        Wallet::class => true,
        User::class => true,
        EntryType::class => true,
        PaymentMethod::class => true,
        ExpenseType::class => true,
    ];

    public function __construct(private readonly TagAwareAdapterInterface $appRequestCache)
    {
    }

    public function supports(BaseEntityClassInterface $baseEntityClass): bool
    {
        return isset(self::CACHEABLE_ENTITIES[$baseEntityClass->getEntityClass()]);
    }

    public function get(
        BaseEntityClassInterface $baseEntityClass,
        Request $request,
        ?QueryParamsInterface $queryParams,
        ?int $id,
        ?int $userId,
        ?int $userRole,
        callable $resolver
    ): JsonResponseHandlerInterface {
        if (!$this->supports($baseEntityClass)) {
            return $resolver();
        }

        $entityClass = $baseEntityClass->getEntityClass();
        $key = $this->cacheKey($entityClass, $request, $queryParams, $id, $userId, $userRole);
        $tags = [self::TAG_ALL, $this->entityTag($entityClass)];
        $item = $this->appRequestCache->getItem($key);

        $cachedResponse = $this->cachedResponse($item);
        if ($cachedResponse !== null) {
            return $cachedResponse;
        }

        $payload = $this->resolveFreshPayload($resolver);
        $this->saveSuccessfulPayload($item, $tags, $payload);

        return $this->responseFromPayload($payload);
    }

    public function invalidateCacheableRequests(): void
    {
        $this->appRequestCache->invalidateTags([self::TAG_ALL]);
    }

    private function cacheKey(
        string $entityClass,
        Request $request,
        ?QueryParamsInterface $queryParams,
        ?int $id,
        ?int $userId,
        ?int $userRole
    ): string {
        $query = $request->query->all();
        $this->sortRecursive($query);

        $payload = [
            'entity' => $entityClass,
            'route' => (string) $request->attributes->get('_route', ''),
            'path' => $request->getPathInfo(),
            'id' => $id,
            'query' => $query,
            'queryParams' => $this->queryParamsPayload($queryParams),
            'userId' => $userId,
            'userRole' => $userRole,
        ];

        return 'request_' . hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
    }

    private function entityTag(string $entityClass): string
    {
        return 'appfinancas_entity_' . hash('sha256', $entityClass);
    }

    private function cachedResponse(CacheItem $item): ?JsonResponseHandlerInterface
    {
        if (!$item->isHit()) {
            return null;
        }

        $payload = $item->get();

        return $this->responseFromPayload(is_array($payload) ? $payload : []);
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveFreshPayload(callable $resolver): array
    {
        $response = $resolver()->output();
        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return is_array($payload) ? $payload : [];
    }

    /**
     * @param string[] $tags
     * @param array<string, mixed> $payload
     */
    private function saveSuccessfulPayload(CacheItem $item, array $tags, array $payload): void
    {
        if (!$this->isSuccessfulPayload($payload)) {
            return;
        }

        $item->expiresAfter(self::LIFETIME_SECONDS);
        $item->tag($tags);
        $item->set($payload);
        $this->appRequestCache->save($item);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function isSuccessfulPayload(array $payload): bool
    {
        $statusCode = $payload['statusCode'] ?? 200;

        return is_int($statusCode) && $statusCode >= 200 && $statusCode < 300;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function responseFromPayload(array $payload): JsonResponseHandlerInterface
    {
        return JsonResponseHandler::create(new CachedResponseData($payload));
    }

    /**
     * @return array<string, mixed>
     */
    private function queryParamsPayload(?QueryParamsInterface $queryParams): array
    {
        if ($queryParams === null) {
            return [];
        }

        $payload = [
            'sort' => [],
            'paginator' => [],
        ];

        foreach ($queryParams->getSortParams() as $param) {
            $payload['sort'][$param->getName()] = $param->getValue();
        }

        foreach ($queryParams->getPaginatorParams() as $param) {
            $payload['paginator'][$param->getName()] = $param->getValue();
        }

        $this->sortRecursive($payload);

        return $payload;
    }

    /**
     * @param array<mixed> $data
     */
    private function sortRecursive(array &$data): void
    {
        ksort($data);

        foreach ($data as &$value) {
            if (is_array($value)) {
                $this->sortRecursive($value);
            }
        }
    }
}
