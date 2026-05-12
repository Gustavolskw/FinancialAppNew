<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Handler\Paginator;

use App\Infrastructure\Handler\Paginator\SimpleDataPaginator;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

final class SimpleDataPaginatorTest extends TestCase
{
    public function testBuildUsesRepositoryCountWhenFilteredTotalIsNotProvided(): void
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::once())
            ->method('count')
            ->willReturn(15);

        $paginator = SimpleDataPaginator::build($repository, ['a', 'b'], 2, 10);

        self::assertSame([
            'totalItems' => 15,
            'mappedItems' => 2,
            'perPage' => 2,
            'totalPages' => 2,
            'previousPage' => 1,
            'currentPage' => 2,
            'nextPage' => null,
            'lastPage' => 2,
        ], $paginator->output());
    }

    public function testBuildUsesFilteredTotalWithoutCallingRepositoryCount(): void
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::never())
            ->method('count');

        $paginator = SimpleDataPaginator::build($repository, ['a'], 1, 10, 1);

        self::assertSame(1, $paginator->output()['totalItems']);
        self::assertNull($paginator->output()['nextPage']);
    }
}
