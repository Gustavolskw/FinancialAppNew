<?php

namespace App\Infrastructure\Helper\Response;

use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;

abstract class AbstractEntityBuilder
{
    protected BaseEntityClassInterface|array $baseEntityClass;

    protected function __construct(BaseEntityClassInterface|array $baseEntityClass)
    {
        $this->baseEntityClass = $baseEntityClass;
    }

    /**
     * @param BaseEntityClassInterface|BaseEntityClassInterface[] $data
     */
    public static function factory(BaseEntityClassInterface|array $data): static
    {
        return new static($data);
    }
}
