<?php

namespace App\Infrastructure\DTO\EntityDto;

use App\Entity\User;
use App\Entity\Wallet as WalletEntity;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttributeInterface;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\EntityDto\User as UserDto;
use App\Infrastructure\Helper\EntityHelper\EntityFieldsHelper;
use Doctrine\ORM\EntityManagerInterface;

final class Wallet extends MainConfigurableEntity
{
    private const string ENTITYCLASS = WalletEntity::class;
    public const string LISTDATATERM = "wallets";
    public const string SINGLEDATATERM = "wallet";


    public function configureFields(FieldsAttributeInterface $fields): FieldsAttributeInterface
    {
        parent::configureFields($fields);

        return $fields
            ->setIdField("id")
            ->setTextField("title", "getTitle", required: true)
            ->setTextField("description", "getDescription", required: true)
            ->setStatusField("status")
            ->setRelationalField("user", User::class, "getWalletUser", required: true);
    }


    public function setFieldsFromEntityData(object $entity, bool $deepFetch = false): self
    {
        EntityFieldsHelper::setFieldsFromEntityData(
            $entity,
            self::ENTITYCLASS,
            $this->getFields(),
            $this->getEntityManager(),
            UserDto::class,
            $deepFetch
        );

        return $this;
    }

    public function getEntityClass(): string
    {
        return self::ENTITYCLASS;
    }

    public static function build(EntityManagerInterface $entityManager): BaseEntityClassInterface
    {
        return new self(new FieldsAttribute(), self::ENTITYCLASS, $entityManager);
    }

}
