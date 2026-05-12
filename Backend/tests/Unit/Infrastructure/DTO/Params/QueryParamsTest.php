<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\DTO\Params;

use App\Infrastructure\DTO\Params\DTO\ParamDto;
use App\Infrastructure\DTO\Params\QueryParams;
use PHPUnit\Framework\TestCase;

final class QueryParamsTest extends TestCase
{
    public function testFromArraySeparatesPaginatorParamsFromSearchParams(): void
    {
        $params = QueryParams::fromArray([
            'page' => '2',
            'perPage' => '15',
            'name' => 'Ana',
            'status' => '1',
            'empty' => null,
        ]);

        $paginatorParams = $params->getPaginatorParams()->toArray();
        $sortParams = $params->getSortParams()->toArray();

        self::assertCount(2, $paginatorParams);
        self::assertContainsOnlyInstancesOf(ParamDto::class, $paginatorParams);
        self::assertSame('page', $paginatorParams[0]->getName());
        self::assertSame('2', $paginatorParams[0]->getValue());
        self::assertSame('perPage', $paginatorParams[1]->getName());

        self::assertCount(2, $sortParams);
        self::assertSame('name', $sortParams[0]->getName());
        self::assertSame('Ana', $sortParams[0]->getValue());
        self::assertSame('status', $sortParams[1]->getName());
    }

    public function testPageSizeIsTreatedAsPaginatorParam(): void
    {
        $params = QueryParams::fromArray([
            'pageSize' => '50',
            'location' => 'Mercado',
        ]);

        self::assertCount(1, $params->getPaginatorParams());
        self::assertCount(1, $params->getSortParams());
        self::assertSame('pageSize', $params->getPaginatorParams()->first()->getName());
        self::assertSame('location', $params->getSortParams()->first()->getName());
    }
}
