<?php

namespace App\Infrastructure\Helper\Response;

use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\Helper\Interface\EntityClassCollection;

final class EntityListBuilder extends AbstractEntityBuilder implements EntityClassCollection
{
    public function output(): array
    {
        return array_map(
            static fn (BaseEntityClassInterface $entity) => $entity->output(),
            $this->baseEntityClass
        );
    }
}
