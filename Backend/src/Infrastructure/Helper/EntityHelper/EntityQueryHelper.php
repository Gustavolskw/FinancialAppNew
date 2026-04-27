<?php

namespace App\Infrastructure\Helper\EntityHelper;

use App\Infrastructure\DTO\EntityAttributes\FieldsAttributeInterface;
use App\Infrastructure\DTO\EntityAttributes\FieldTypeEnum;
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

            $field = $fields->getField($paramName);
            if (!$field) {
                continue;
            }

            $dbField = $field->getName();
            $placeholder = $dbField . '_' . $paramName;

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




}
