<?php

namespace App\Infrastructure\Handler\Paginator;

use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\Handler\Paginator\Dto\PaginatorDataDto;
use App\Infrastructure\Helper\Interface\EntityClassCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

class SimpleDataPaginator implements PaginatorInterface, EntityClassCollection
{
    private EntityRepository $repository;

    /**
     * @var ArrayCollection<PaginatorDataDto>
     */
    private ArrayCollection $paginators;

    private int $page;
    private int $perPage;

    /**
     * @var BaseEntityClassInterface[] $mappedItems
     */
    private array $mappedItems;

    private int $totalCount;
    private ?int $filteredTotalCount;

    private int $lastPage;

    /**
     * @param EntityRepository $repository
     * @param ArrayCollection $analyses
     */
    public function __construct(
        EntityRepository $repository,
        ArrayCollection $analyses,
        array $mappedItems,
        int $page,
        int $perPage,
        ?int $filteredTotalCount = null
    )
    {
        $this->repository = $repository;
        $this->paginators = $analyses;
        $this->page = $page;
        $this->perPage = $perPage;
        $this->mappedItems = $mappedItems;
        $this->filteredTotalCount = $filteredTotalCount;

        $this
            ->totalItems()
            ->totalQueriedItems()
            ->pageItems()
            ->totalPages()
            ->previousPage()
            ->currentPage()
            ->nextPage()
            ->lastPage();
    }

    /**
     * @param EntityRepository $repository
     * @return PaginatorInterface
     */
    public static function build(
        EntityRepository $repository,
        array $mappedItems,
        int $page,
        int $perPage,
        ?int $filteredTotalCount = null
    ): PaginatorInterface
    {
        return new self($repository, new ArrayCollection(), $mappedItems,  $page, $perPage, $filteredTotalCount);
    }

    private function totalItems(): self
    {
        $this->totalCount = $this->filteredTotalCount ?? $this->repository->count();
        $this->paginators->add(new PaginatorDataDto("totalItems", $this->totalCount));
        return $this;
    }

    private function totalQueriedItems(): self
    {
        $this->paginators->add(new PaginatorDataDto("mappedItems", count($this->mappedItems)));
        return $this;
    }
    private function pageItems():self
    {

        $this->paginators->add(new PaginatorDataDto("perPage", min($this->perPage, count($this->mappedItems))));
        return $this;
    }
    private function currentPage():self
    {
        $this->paginators->add(new PaginatorDataDto("currentPage", $this->page));
        return $this;
    }
    private function previousPage():self
    {
        $prevPage = $this->page == 1 ? null : ($this->page - 1);
        $this->paginators->add(new PaginatorDataDto("previousPage", $prevPage));
        return $this;
    }
    private function nextPage():self
    {
        $nextPage = $this->page == $this->lastPage ? null : ($this->page + 1);
        $this->paginators->add(new PaginatorDataDto("nextPage", $nextPage));
        return $this;
    }
    private function lastPage():void
    {
        $this->paginators->add(new PaginatorDataDto("lastPage", $this->lastPage));
    }
    private function totalPages(): self
    {
        $this->lastPage = ceil($this->totalCount / $this->perPage);
        $this->paginators->add(new PaginatorDataDto("totalPages", $this->lastPage));
        return $this;
    }

    public function output(): array
    {
        $out = [];

        foreach ($this->paginators as $dto) {
            /** @var PaginatorDataDto $dto */
            $out = array_merge($out, $dto->output());
        }

        return $out;
    }
}
