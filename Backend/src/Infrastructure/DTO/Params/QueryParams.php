<?php

namespace App\Infrastructure\DTO\Params;

use App\Infrastructure\DTO\Params\DTO\ParamDto;
use App\Infrastructure\DTO\Params\Interface\QueryParamsInterface;
use Doctrine\Common\Collections\ArrayCollection;

class QueryParams implements QueryParamsInterface
{
    /**
     * @var ArrayCollection<ParamDto>
     */
    private ArrayCollection $sortParams;

    /**
     * @var ArrayCollection<ParamDto>
     */
    private ArrayCollection $paginatorParams;


    public function __construct()
    {
        $this->sortParams = new ArrayCollection();
        $this->paginatorParams = new ArrayCollection();
    }

    public function getSortParams(): ArrayCollection
    {
        return $this->sortParams;
    }

    public function getPaginatorParams(): ArrayCollection
    {
        return $this->paginatorParams;
    }

    public function addParam(string $paramName, mixed $paramValue): QueryParamsInterface
    {
        if (empty($paramName) || $paramValue === null) {
            return $this;
        }

        if (in_array($paramName, ['page', 'perPage', 'pageSize'], true)) {
            $this->paginatorParams->add(new ParamDto($paramName, $paramValue));
            return $this;
        }

        $this->sortParams->add(new ParamDto($paramName, $paramValue));
        return $this;
    }


    /** @param array<string,mixed> $data */
    public static function fromArray(array $data): QueryParamsInterface
    {
        $qp = new self();
        foreach ($data as $field => $value) {
            $qp->addParam($field, $value);
        }
        return $qp;
    }
}
