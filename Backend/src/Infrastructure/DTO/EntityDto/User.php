<?php

namespace App\Infrastructure\DTO\EntityDto;

use App\Entity\User as UserEntity;
use App\Entity\Wallet;
use App\Infrastructure\DTO\EntityAttributes\Enum\RolesEnum;
use App\Infrastructure\DTO\EntityAttributes\Fields\FieldsInterface;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttributeInterface;
use App\Infrastructure\DTO\EntityAttributes\FieldTypeEnum;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\EntityDto\Wallet as WalletDto;
use App\Infrastructure\Handler\Action\Specific\Interface\SpecificActionInterface;
use App\Infrastructure\Handler\Action\Specific\UserSpecificAction;
use App\Infrastructure\Helper\EntityHelper\EntityFieldsHelper;
use Doctrine\ORM\EntityManagerInterface;

final class User extends MainConfigurableEntity
{
    private const string ENTITYCLASS = UserEntity::class;
    public const string LISTDATATERM = "users";
    public const string SINGLEDATATERM = "user";


    public function configureFields(FieldsAttributeInterface $fields): FieldsAttributeInterface
    {
        parent::configureFields($fields);
        $fields
            ->setIdField("id")
            ->setNameField("name", required: true)
            ->setTextField("email", "getEmail", FieldTypeEnum::EMAILFIELD, required: true)
            ->setPassword("password", required: true, additionalFieldValidation: function (FieldsInterface $field): void {
                $password = $field->getValue();
                $passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d\s])\S{6,}$/';

                if (!preg_match($passwordPattern, $password)) {
                    throw new \InvalidArgumentException(
                        "A senha deve ter mais de 5 caracteres, com letra maiúscula, letra minúscula, número e caractere especial"
                    );
                }
            })
            ->setEnumField("role", "getRole", RolesEnum::class)
            ->setStatusField("status")
            ->setRelationalField("wallet", Wallet::class, "getUserWallet");
        return $fields;
    }


    public function setFieldsFromEntityData(object $entity, bool $deepFetch = false): self
    {
        EntityFieldsHelper::setFieldsFromEntityData(
            $entity,
            self::ENTITYCLASS,
            $this->getFields(),
            $this->getEntityManager(),
            WalletDto::class,
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

    public function setSpecificAction(): SpecificActionInterface
    {
        return new UserSpecificAction($this);
    }
}
