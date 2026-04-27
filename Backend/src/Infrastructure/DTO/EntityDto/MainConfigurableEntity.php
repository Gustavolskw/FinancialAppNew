<?php

namespace App\Infrastructure\DTO\EntityDto;

use App\Entity\Wallet;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttributeInterface;
use App\Infrastructure\DTO\EntityAttributes\FieldTypeEnum;
use App\Infrastructure\DTO\EntityDto\Interface\BaseMainEntityClassInterface;

abstract class MainConfigurableEntity extends ConfigurableEntity implements BaseMainEntityClassInterface
{
    public function configureFields(FieldsAttributeInterface $fields): FieldsAttributeInterface
    {
        parent::configureFields($fields);

        return $fields
            ->setDateField("createdAt", "getCreatedAt", FieldTypeEnum::DATETIMEFIELD)
            ->setDateField("updatedAt", "getUpdatedAt", FieldTypeEnum::DATETIMEFIELD);
    }
}
