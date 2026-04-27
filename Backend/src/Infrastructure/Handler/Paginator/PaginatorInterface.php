<?php

namespace App\Infrastructure\Handler\Paginator;

use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
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
    public static function build(EntityRepository $repository, array $mappedItems, int $page, int $perPage): PaginatorInterface;
}
