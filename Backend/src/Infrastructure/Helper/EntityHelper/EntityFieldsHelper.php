<?php

namespace App\Infrastructure\Helper\EntityHelper;

use App\Infrastructure\DTO\EntityAttributes\Fields\FieldsInterface;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttributeInterface;
use App\Infrastructure\DTO\EntityAttributes\FieldTypeEnum;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use Doctrine\ORM\EntityManagerInterface;

class EntityFieldsHelper
{

    /**
     * @param object $entity Entidade Doctrine
     * @param class-string $entityClass FQCN esperado da entidade (ex: App\Entity\User::class)
     * @param FieldsAttributeInterface $fields Campos configurados do DTO
     * @param class-string<BaseEntityClassInterface>|array<string, class-string<BaseEntityClassInterface>>|null $relationalDtoClass DTO relacional unico (ex: WalletDto::class) ou mapa por campo
     * @param bool $deepFetch Se true, popula o DTO relacional; se false retorna somente id
     * @return FieldsAttributeInterface
     */
    public static function setFieldsFromEntityData(
        object $entity,
        string $entityClass,
        FieldsAttributeInterface $fields,
        EntityManagerInterface $entityManager,
        string|array|null $relationalDtoClass = null,
        bool $deepFetch = false
    ): FieldsAttributeInterface {
        if (!$entity instanceof $entityClass) {
            throw new \InvalidArgumentException("Entity must be instance of {$entityClass}");
        }

        /** @var FieldsInterface $field */
        foreach ($fields->getFields() as $field) {
            $getter = $field->getEntityGetter();

            if ($getter === null || $getter === '') {
                continue;
            }

            if (!method_exists($entity, $getter)) {
                throw new \RuntimeException("Getter {$getter} não existe em {$entityClass}");
            }

            $value = $entity->$getter();

            // Relacional
            $fieldRelationalDtoClass = self::resolveRelationalDtoClass($field, $relationalDtoClass);
            if ($field->getFieldType() === FieldTypeEnum::RELATIONALFIELD && $fieldRelationalDtoClass !== null) {
                $value = AttributeOutputHelper::setRelationalAttribute(
                    $deepFetch,
                    $value,
                    $fieldRelationalDtoClass::build($entityManager)
                );
            }

            // Cast para ID
            if ($field->getFieldType() === FieldTypeEnum::IDFIELD && $value !== null) {
                $value = (int) $value;
            }

            $fields->getField($field->getName())?->setValue($value);
        }

        return $fields;
    }

    /**
     * @param class-string<BaseEntityClassInterface>|array<string, class-string<BaseEntityClassInterface>>|null $relationalDtoClass
     * @return class-string<BaseEntityClassInterface>|null
     */
    private static function resolveRelationalDtoClass(
        FieldsInterface $field,
        string|array|null $relationalDtoClass
    ): ?string {
        if ($field->getFieldType() !== FieldTypeEnum::RELATIONALFIELD) {
            return null;
        }

        if (is_string($relationalDtoClass)) {
            return $relationalDtoClass;
        }

        if (is_array($relationalDtoClass)) {
            return $relationalDtoClass[$field->getName()] ?? null;
        }

        return null;
    }
}
