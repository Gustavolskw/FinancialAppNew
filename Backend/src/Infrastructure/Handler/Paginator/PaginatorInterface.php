<?php

namespace App\Infrastructure\Handler\Paginator;

use App\Infrastructure\DTO\Configuration\Interface\BaseEntityClassInterface;
use Doctrine\ORM\EntityRepository;

interface PaginatorInterface
{
    /**
     * @param EntityRepository $repository
     * @param BaseEntityClassInterface[] $mappedItems
     * @param int $page
     * @param int $perPage
     * @return PaginatorInterface
     */
    public static function build(
        EntityRepository $repository,
        array $mappedItems,
        int $page,
        int $perPage,
        ?int $filteredTotalCount = null
    ): PaginatorInterface;

    public function output(): array;
}
