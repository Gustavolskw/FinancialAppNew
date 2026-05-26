<?php

namespace App\Infrastructure\Helper\Action;

use App\Infrastructure\DTO\EntityAttributes\FieldTypeEnum;
use App\Infrastructure\DTO\EntityAttributes\Fields\FieldsInterface;
use App\Infrastructure\DTO\EntityAttributes\Fields\RelationalAttributeDto;
use App\Infrastructure\DTO\Configuration\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\Response\ResponseBuilder;
use App\Infrastructure\Handler\Response\JsonResponseHandler;
use App\Infrastructure\Handler\Response\JsonResponseHandlerInterface;
use App\Infrastructure\Helper\Response\EntityBuilder;
use Doctrine\ORM\QueryBuilder;
use Closure;

trait ActionHelperTrait
{
    public function __construct(BaseEntityClassInterface $baseEntityClass, ?Closure $listQueryRestriction = null)
    {
        $this->baseEntityClass = $baseEntityClass;
        $this->listQueryRestriction = $listQueryRestriction;
    }

    private function validateFields(bool $updateOnly = false): void
    {
        /** @var FieldsInterface $field */
        foreach ($this->baseEntityClass->getFields()->getFields() as $field) {
            if ($updateOnly && !$field->hasValue()) {
                continue;
            }

            $field->validate();
        }
    }

    private function countRestrictedResults(QueryBuilder $qb): int
    {
        $countQb = clone $qb;
        $rootAlias = $countQb->getRootAliases()[0];

        $countQb
            ->select(sprintf('COUNT(DISTINCT %s.id)', $rootAlias))
            ->setFirstResult(0)
            ->setMaxResults(null);

        return (int) $countQb->getQuery()->getSingleScalarResult();
    }

    private function applyFieldsToEntity(object $entity, bool $updateOnly = false): void
    {
        $now = new \DateTimeImmutable();

        /** @var FieldsInterface $field */
        foreach ($this->baseEntityClass->getFields()->getFields() as $field) {
            if ($this->shouldSkipField($field)) {
                continue;
            }

            if (!$field->hasValue()) {
                continue;
            }

            $this->setEntityValue($entity, $field, $this->fieldEntityValue($field));
        }

        if (!$updateOnly && method_exists($entity, 'setCreatedAt')) {
            $entity->setCreatedAt($now);
        }

        if (method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt($now);
        }

        $statusField = $this->baseEntityClass->getFields()->getStatusField();
        if (!$updateOnly && $statusField !== null && !$statusField->hasValue() && method_exists($entity, 'setStatus')) {
            $entity->setStatus(true);
        }
    }

    private function shouldSkipField(FieldsInterface $field): bool
    {
        return in_array($field->getFieldType(), [
            FieldTypeEnum::IDFIELD,
        ], true);
    }

    private function fieldEntityValue(FieldsInterface $field): mixed
    {
        if ($field->getFieldType() === FieldTypeEnum::ENUMFIELD) {
            return $field->getRawValue();
        }

        if ($field->getFieldType() === FieldTypeEnum::RELATIONALFIELD) {
            return $this->relationalFieldEntityValue($field);
        }

        return $field->getValue();
    }

    private function relationalFieldEntityValue(FieldsInterface $field): mixed
    {
        if (!$field instanceof RelationalAttributeDto) {
            throw new \InvalidArgumentException("Campo relacional {$field->getName()} inválido");
        }

        $value = $field->getRawValue();
        if ($value === null) {
            return null;
        }

        $relationalEntityClass = $field->getRelationalEntityClass();
        if ($relationalEntityClass === null) {
            throw new \InvalidArgumentException("Classe relacional não configurada para campo {$field->getName()}");
        }

        if (is_object($value) && $value instanceof $relationalEntityClass) {
            return $value;
        }

        if (!is_int($value)) {
            throw new \InvalidArgumentException("Valor inválido para campo relacional {$field->getName()}");
        }

        $relatedEntity = $this->baseEntityClass->getEntityManager()
            ->getRepository($relationalEntityClass)
            ->find($value);

        if ($relatedEntity === null) {
            throw new \InvalidArgumentException("Registro relacionado não encontrado para campo {$field->getName()}");
        }

        return $relatedEntity;
    }

    private function setEntityValue(object $entity, FieldsInterface $field, mixed $value): void
    {
        $setter = $this->fieldEntitySetter($field);

        if (!method_exists($entity, $setter)) {
            return;
        }

        $entity->$setter($value);
    }

    private function fieldEntitySetter(FieldsInterface $field): string
    {
        if ($field->getFieldType() !== FieldTypeEnum::RELATIONALFIELD) {
            return 'set' . ucfirst($field->getName());
        }

        $getter = $field->getEntityGetter();
        if (str_starts_with($getter, 'get')) {
            return 'set' . substr($getter, 3);
        }

        if (str_starts_with($getter, 'is')) {
            return 'set' . substr($getter, 2);
        }

        return 'set' . ucfirst($field->getName());
    }

    private function getFieldId(): ?int
    {
        $idField = $this->baseEntityClass->getFields()->getIdField();

        if ($idField === null || !$idField->hasValue()) {
            return null;
        }

        return (int) $idField->getValue();
    }

    private function setIdFieldValue(int $id): void
    {
        $idField = $this->baseEntityClass->getFields()->getIdField();

        if ($idField === null) {
            throw new \InvalidArgumentException("Campo id não configurado");
        }

        $idField->setValue($id)->validate();
    }

    private function setStatusFieldValue(bool $status): void
    {
        $statusField = $this->baseEntityClass->getFields()->getStatusField();

        if ($statusField === null) {
            throw new \InvalidArgumentException("Campo status não configurado");
        }

        $statusField->setValue($status)->validate();
    }

    private function singleResourceResponse(object $entity): JsonResponseHandlerInterface
    {
        $dto = $this->baseEntityClass::build($this->baseEntityClass->getEntityManager());
        $dto->setFieldsFromEntityData($entity, true);

        $response = ResponseBuilder::build("Sucesso!", 200)
            ->addData($this->baseEntityClass->getSingleDataTerm(), EntityBuilder::factory($dto));

        return JsonResponseHandler::create($response);
    }

    private function response(string $message, int $statusCode): JsonResponseHandlerInterface
    {
        return JsonResponseHandler::create(ResponseBuilder::build($message, $statusCode));
    }
}
