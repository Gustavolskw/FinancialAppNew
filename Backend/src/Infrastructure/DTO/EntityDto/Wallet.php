<?php

namespace App\Infrastructure\DTO\EntityDto;

use App\Entity\User;
use App\Entity\Wallet as WalletEntity;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttributeInterface;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\EntityDto\User as UserDto;
use App\Infrastructure\DTO\Forms\FormDtoInterface;
use App\Infrastructure\Handler\Action\Specific\Interface\SpecificActionInterface;
use App\Infrastructure\Helper\EntityHelper\AttributeOutputHelper;
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
            ->setTextField("title", "getTitle")
            ->setTextField("description", "getDescription")
            ->setRelationalField("user", User::class, "getWalletUser");
    }


    /**
     * @throws \DateMalformedStringException
     */
    public function output(): array
    {
        return AttributeOutputHelper::outputEntityFields($this->getFields()->getFields());
    }


    public function setFieldValues(FormDtoInterface $dto): void
    {
        foreach ($this->getFields()->getFields() as $field) {
            $name = $field->getName();

            if (!property_exists($dto, $name)) {
                continue;
            }

            if ($dto->$name !== null) {
                $field->setValue($dto->$name);
            }
        }
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
