<?php

namespace App\Infrastructure\Helper\Response;

use App\Infrastructure\Helper\Interface\EntityClassCollection;

class EntityBuilder extends AbstractEntityBuilder implements EntityClassCollection
{
    public function output(): array
    {
        return $this->baseEntityClass->output();
    }

}
