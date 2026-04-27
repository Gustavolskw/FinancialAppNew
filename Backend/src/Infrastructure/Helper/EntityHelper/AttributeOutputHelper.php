<?php

namespace App\Infrastructure\Helper\EntityHelper;

use App\Infrastructure\DTO\EntityAttributes\Fields\FieldsInterface;
use App\Infrastructure\DTO\EntityAttributes\FieldTypeEnum;
use App\Infrastructure\DTO\EntityAttributes\Enum\Interface\EntityFieldEnumInterface;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;

class AttributeOutputHelper
{
    public static function outputAttribute(BaseEntityClassInterface|int|null $attribute): array|int|null
    {
        if ($attribute instanceof BaseEntityClassInterface) {
            return $attribute->output();
        }
        return $attribute;
    }

    public static function setRelationalAttribute(bool $deepFetch, ?object $entityClass, object $dtoClass): BaseEntityClassInterface|int|null
    {
        if ($entityClass === null) {
            return null;
        }

        if ($deepFetch) {
            return $dtoClass->setFieldsFromEntityData($entityClass, false);
        }

        return method_exists($entityClass, 'getId') ? $entityClass->getId() : null;
    }

    /**
     * @param ArrayCollection<FieldsInterface> $fields
     * @throws DateMalformedStringException
     */
    public static function outputEntityFields(ArrayCollection $fields): array
    {
        $result = [];

        /** @var array<string, \DateTimeInterface|null> $deferredDateTimes */
        $deferredDateTimes = [];

        foreach ($fields as $field) {
            $name = $field->getName();
            $value = $field->getValue();
            $type = $field->getFieldType();

            if ($type === FieldTypeEnum::RELATIONALFIELD) {
                $relation = self::outputAttribute($value);

                if (is_array($relation)) {
                    $result[$name] = $relation;
                } else {
                    $result[$name . 'Id'] = $relation;
                }
                continue;
            }

            if ($type === FieldTypeEnum::ENUMFIELD) {
                $result[$name] = $value instanceof EntityFieldEnumInterface ? $value->name() : $value;
                continue;
            }

            if (
                $type === FieldTypeEnum::DATETIMEFIELD
                && ($name === 'createdAt' || $name === 'updatedAt')
            ) {
                $deferredDateTimes[$name] = $value instanceof \DateTimeInterface ? $value : null;
                continue;
            }

            if ($type === FieldTypeEnum::DATEFIELD) {
                $result[$name] = $value instanceof \DateTimeInterface ? self::format($value, false) : $value;
                continue;
            }

            if ($type === FieldTypeEnum::DATETIMEFIELD) {
                $result[$name] = $value instanceof \DateTimeInterface ? self::format($value, true) : $value;
                continue;
            }

            $result[$name] = $value;
        }

        if (array_key_exists('createdAt', $deferredDateTimes)) {
            $result['createdAt'] = self::format($deferredDateTimes['createdAt'], true);
        }
        if (array_key_exists('updatedAt', $deferredDateTimes)) {
            $result['updatedAt'] = self::format($deferredDateTimes['updatedAt'], true);
        }

        return $result;
    }

    /**
     * @throws DateMalformedStringException
     */
    public static function format(?\DateTimeInterface $dt, bool $withTime = true): ?string
    {
        if ($dt === null) {
            return null;
        }

        $tz = new DateTimeZone('America/Sao_Paulo');
        $dtLocal = new DateTimeImmutable($dt->format('c'))->setTimezone($tz);

        return $withTime
            ? $dtLocal->format('d/m/Y H:i:s')
            : $dtLocal->format('d/m/Y');
    }
}
