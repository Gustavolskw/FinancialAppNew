<?php

namespace App\Infrastructure\Helper\EntityHelper;

use App\Infrastructure\DTO\EntityAttributes\FieldsAttributeInterface;
use App\Infrastructure\DTO\EntityAttributes\FieldTypeEnum;
use App\Infrastructure\DTO\EntityAttributes\Fields\FieldsInterface;
use App\Infrastructure\DTO\Params\Interface\QueryParamsInterface;
use App\Infrastructure\Helper\BaseHelper;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class EntityQueryHelper
{

    public static function buildSearchQuery(EntityRepository $repository, QueryParamsInterface $params, FieldsAttributeInterface $fields, string $tableAlias):QueryBuilder
    {
        $paginator = $params->getPaginatorParams();
        $page = (int) (BaseHelper::getParamValueByName($paginator, 'page') ?? 1);
        $perPage = (int) (BaseHelper::getParamValueByName($paginator, 'perPage')
            ?? BaseHelper::getParamValueByName($paginator, 'pageSize')
            ?? 20);

        $qb = $repository->createQueryBuilder($tableAlias)
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        $qb->where('1 = 1');
        $searchParams = $params->getSortParams();
        foreach ($searchParams as $param) {
            $paramName  = $param->getName();
            $paramValue = $param->getValue();
            if ($paramValue === null || $paramValue === '') {
                continue;
            }

            $field = self::resolveFieldByParam($fields, $paramName);
            if (!$field) {
                continue;
            }

            $dbField = self::entityPropertyName($field);
            $placeholder = preg_replace('/[^A-Za-z0-9_]/', '_', $dbField . '_' . $paramName);

            if ($field->getFieldType() === FieldTypeEnum::RELATIONALFIELD) {
                $qb->andWhere(sprintf('IDENTITY(%s.%s) = :%s', $tableAlias, $dbField, $placeholder));
                $qb->setParameter($placeholder, (int) $paramValue);
                continue;
            }

            if (in_array($field->getFieldType(), [
                FieldTypeEnum::TEXTFIELD,
                FieldTypeEnum::NAMEFIELD,
                FieldTypeEnum::EMAILFIELD,
                FieldTypeEnum::LOCATIONFIELD,
            ], true)) {
                $qb->andWhere(sprintf('%s.%s LIKE :%s', $tableAlias, $dbField, $placeholder));
                $qb->setParameter($placeholder, '%' . $paramValue . '%');
                continue;
            }
            if($field->getFieldType() == FieldTypeEnum::STATUSFIELD){
                $qb->andWhere(sprintf('%s.%s = :%s', $tableAlias, $dbField, $placeholder));
                $qb->setParameter($placeholder, (bool) $paramValue);
                continue;
            }

            if ($field->getFieldType() === FieldTypeEnum::ENUMFIELD) {
                $qb->andWhere(sprintf('%s.%s = :%s', $tableAlias, $dbField, $placeholder));
                $qb->setParameter($placeholder, (int) $paramValue);
                continue;
            }


            $qb->andWhere(sprintf('%s.%s = :%s', $tableAlias, $dbField, $placeholder));
            $qb->setParameter($placeholder, $paramValue);

        }
        return $qb;
    }

    private static function resolveFieldByParam(FieldsAttributeInterface $fields, string $paramName): ?FieldsInterface
    {
        $field = $fields->getField($paramName);
        if ($field !== null) {
            return $field;
        }

        if (!str_ends_with($paramName, 'Id')) {
            return null;
        }

        $relationName = substr($paramName, 0, -2);
        $field = $fields->getField($relationName);

        if ($field === null || $field->getFieldType() !== FieldTypeEnum::RELATIONALFIELD) {
            return null;
        }

        return $field;
    }

    private static function entityPropertyName(FieldsInterface $field): string
    {
        if ($field->getFieldType() !== FieldTypeEnum::RELATIONALFIELD) {
            return $field->getName();
        }

        $getter = $field->getEntityGetter();
        if (str_starts_with($getter, 'get')) {
            return lcfirst(substr($getter, 3));
        }

        if (str_starts_with($getter, 'is')) {
            return lcfirst(substr($getter, 2));
        }

        return $field->getName();
    }



}
