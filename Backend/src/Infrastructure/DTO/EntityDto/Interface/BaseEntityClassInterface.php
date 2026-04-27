<?php

namespace App\Infrastructure\DTO\EntityDto\Interface;

use App\Infrastructure\DTO\EntityAttributes\FieldsAttributeInterface;
use App\Infrastructure\DTO\Forms\FormDtoInterface;
use App\Infrastructure\DTO\Params\Interface\QueryParamsInterface;
use App\Infrastructure\Handler\Action\Specific\Interface\SpecificActionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

interface BaseEntityClassInterface
{
    public function output(): array;
    public function configureFields(FieldsAttributeInterface $fields): FieldsAttributeInterface;
    public function getFields(): FieldsAttributeInterface;
    public function getEntityClass(): string;
    public function setFieldValues(FormDtoInterface $dto):void;
    public static function build(EntityManagerInterface $entityManager): BaseEntityClassInterface;
    public function setFieldsFromEntityData(object $entity, bool $deepFetch): BaseEntityClassInterface;
    public function resolveQueryBuilder(QueryParamsInterface $params): QueryBuilder;
    public function getRepository(): EntityRepository;
    public function getEntityManager(): EntityManagerInterface;
    public function setSpecificAction(): SpecificActionInterface;
}
