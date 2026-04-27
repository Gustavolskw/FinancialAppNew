<?php

namespace App\Infrastructure\DTO\Params\Interface;

use Doctrine\Common\Collections\ArrayCollection;

interface QueryParamsInterface
{
    public static function fromArray(array $data): QueryParamsInterface;
    public function addParam(string $paramName, mixed $paramValue): QueryParamsInterface;
    public function getSortParams(): ArrayCollection;
    public function getPaginatorParams(): ArrayCollection;

}
