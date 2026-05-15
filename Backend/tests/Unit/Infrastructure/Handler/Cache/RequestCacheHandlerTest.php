<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Handler\Cache;

use App\Entity\Entry;
use App\Entity\Wallet;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\Params\QueryParams;
use App\Infrastructure\DTO\Response\CachedResponseData;
use App\Infrastructure\Handler\Cache\RequestCacheHandler;
use App\Infrastructure\Handler\Response\JsonResponseHandler;
use App\Infrastructure\Handler\Response\JsonResponseHandlerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\HttpFoundation\Request;

final class RequestCacheHandlerTest extends TestCase
{
    public function testCachesSuccessfulGetAndInvalidatesByTag(): void
    {
        $handler = new RequestCacheHandler(new TagAwareAdapter(new ArrayAdapter()));
        $baseEntityClass = $this->baseEntityClass(Wallet::class);
        $request = Request::create('/wallet', Request::METHOD_GET, ['page' => '1']);
        $request->attributes->set('_route', 'walletList');
        $calls = 0;

        $resolver = function () use (&$calls): JsonResponseHandlerInterface {
            $calls++;

            return JsonResponseHandler::create(new CachedResponseData([
                'message' => 'Sucesso!',
                'statusCode' => 200,
                'data' => ['calls' => $calls],
            ]));
        };

        $first = $handler->get(
            $baseEntityClass,
            $request,
            QueryParams::fromArray(['page' => 1]),
            null,
            10,
            1,
            $resolver
        )->output();

        self::assertSame(1, $calls, 'Cache miss deve executar a busca real uma única vez.');

        $second = $handler->get(
            $baseEntityClass,
            $request,
            QueryParams::fromArray(['page' => 1]),
            null,
            10,
            1,
            $resolver
        )->output();

        self::assertSame(1, $calls, 'Cache hit não deve executar a busca real novamente.');
        self::assertSame($first->getContent(), $second->getContent());

        $handler->invalidateCacheableRequests();

        $third = $handler->get(
            $baseEntityClass,
            $request,
            QueryParams::fromArray(['page' => 1]),
            null,
            10,
            1,
            $resolver
        )->output();

        self::assertSame(2, $calls, 'Depois da invalidação, a busca real deve executar apenas uma nova vez.');
        self::assertStringContainsString('"calls":2', (string) $third->getContent());
    }

    public function testDoesNotCacheUnsuccessfulGet(): void
    {
        $handler = new RequestCacheHandler(new TagAwareAdapter(new ArrayAdapter()));
        $baseEntityClass = $this->baseEntityClass(Wallet::class);
        $request = Request::create('/wallet/999', Request::METHOD_GET);
        $calls = 0;

        $resolver = function () use (&$calls): JsonResponseHandlerInterface {
            $calls++;

            return JsonResponseHandler::create(new CachedResponseData([
                'message' => 'Registro não encontrado',
                'statusCode' => 404,
                'data' => ['calls' => $calls],
            ]));
        };

        $handler->get($baseEntityClass, $request, QueryParams::fromArray([]), 999, 10, 1, $resolver)->output();
        $handler->get($baseEntityClass, $request, QueryParams::fromArray([]), 999, 10, 1, $resolver)->output();

        self::assertSame(2, $calls);
    }

    public function testDoesNotCacheEntryRequests(): void
    {
        $handler = new RequestCacheHandler(new TagAwareAdapter(new ArrayAdapter()));
        $baseEntityClass = $this->baseEntityClass(Entry::class);
        $request = Request::create('/entry', Request::METHOD_GET);
        $calls = 0;

        $resolver = function () use (&$calls): JsonResponseHandlerInterface {
            $calls++;

            return JsonResponseHandler::create(new CachedResponseData([
                'message' => 'Sucesso!',
                'statusCode' => 200,
                'data' => ['calls' => $calls],
            ]));
        };

        $handler->get($baseEntityClass, $request, QueryParams::fromArray([]), null, 10, 1, $resolver)->output();
        $handler->get($baseEntityClass, $request, QueryParams::fromArray([]), null, 10, 1, $resolver)->output();

        self::assertSame(2, $calls);
    }

    /**
     * @param class-string $entityClass
     */
    private function baseEntityClass(string $entityClass): BaseEntityClassInterface
    {
        $baseEntityClass = $this->createStub(BaseEntityClassInterface::class);
        $baseEntityClass->method('getEntityClass')->willReturn($entityClass);

        return $baseEntityClass;
    }
}
