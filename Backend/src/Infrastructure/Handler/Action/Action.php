<?php

namespace App\Infrastructure\Handler\Action;

use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\EntityAttributes\FieldTypeEnum;
use App\Infrastructure\DTO\EntityAttributes\Fields\FieldsInterface;
use App\Infrastructure\DTO\EntityAttributes\Fields\RelationalAttributeDto;
use App\Infrastructure\DTO\Params\Interface\QueryParamsInterface;
use App\Infrastructure\DTO\Response\ResponseBuilder;
use App\Infrastructure\Handler\Analytics\SimpleDataAnalytics;
use App\Infrastructure\Handler\Paginator\SimpleDataPaginator;
use App\Infrastructure\Handler\Response\JsonResponseHandler;
use App\Infrastructure\Handler\Response\JsonResponseHandlerInterface;
use App\Infrastructure\Helper\BaseHelper;
use App\Infrastructure\Helper\Response\EntityBuilder;
use App\Infrastructure\Helper\Response\EntityListBuilder;
use Closure;

class Action implements ActionInterface
{
    private BaseEntityClassInterface $baseEntityClass;
    private ?Closure $listQueryRestriction;

    /**
     * @param BaseEntityClassInterface $baseEntityClass
     */
    public function __construct(BaseEntityClassInterface $baseEntityClass, ?Closure $listQueryRestriction = null)
    {
        $this->baseEntityClass = $baseEntityClass;
        $this->listQueryRestriction = $listQueryRestriction;
    }

    public function listView(QueryParamsInterface $queryParams): JsonResponseHandlerInterface
    {
        $repo = $this->baseEntityClass->getRepository();
        $qb = $this->baseEntityClass->resolveQueryBuilder($queryParams);
        if ($this->listQueryRestriction instanceof Closure) {
            ($this->listQueryRestriction)($qb);
        }

        $totalCount = $this->countRestrictedResults($qb);
        $resourceList = $qb->getQuery()->getResult();
        $paginator = $queryParams->getPaginatorParams();
        $page = (int) (BaseHelper::getParamValueByName($paginator, 'page') ?? 1);
        $perPage = (int) (BaseHelper::getParamValueByName($paginator, 'perPage')
            ?? BaseHelper::getParamValueByName($paginator, 'pageSize')
            ?? 20);
        $paginatorData = SimpleDataPaginator::build($repo, $resourceList, $page, $perPage, $totalCount);

        $mapped = array_map(function ($entity) {
            $dto = $this->baseEntityClass::build($this->baseEntityClass->getEntityManager());
            $dto->setFieldsFromEntityData($entity, true);
            return $dto;
        }, $resourceList);

        $analytics = SimpleDataAnalytics::build($mapped)
            ->countAnalyses();

        $response = ResponseBuilder::build("Sucesso!", 200)
            ->addData($this->baseEntityClass::LISTDATATERM, EntityListBuilder::factory($mapped))
            ->addData("pagination", $paginatorData)
            ->addData('analytics', $analytics);


        return JsonResponseHandler::create($response);
    }

    public function view(int $id): JsonResponseHandlerInterface
    {
        $entity = $this->baseEntityClass->getRepository()->find($id);

        if ($entity === null) {
            return JsonResponseHandler::create(ResponseBuilder::build("Registro não encontrado", 404));
        }

        $dto = $this->baseEntityClass::build($this->baseEntityClass->getEntityManager());
        $dto->setFieldsFromEntityData($entity, true);

        $response = ResponseBuilder::build("Sucesso!", 200)
            ->addData($this->baseEntityClass::SINGLEDATATERM, EntityBuilder::factory($dto));

        return JsonResponseHandler::create($response);
    }

    public function save(): JsonResponseHandlerInterface
    {
        try {
            $this->validateFields();

            $specificAction = $this->baseEntityClass->setSpecificAction();
            if (!$specificAction->preActionValidation($this->baseEntityClass)) {
                return $this->response("Regra de negócio impediu o cadastro", 400);
            }

            $specificAction->specificAction($this->baseEntityClass);

            $entityClass = $this->baseEntityClass->getEntityClass();
            $entity = new $entityClass();
            $this->applyFieldsToEntity($entity);

            if (!$specificAction->preSave($this->baseEntityClass)) {
                return $this->response("Regra de negócio impediu o cadastro", 400);
            }

            // Hooks como preSave/preUpdate podem alterar fields antes do flush.
            $this->applyFieldsToEntity($entity);

            $entityManager = $this->baseEntityClass->getEntityManager();
            $connection = $entityManager->getConnection();

            $connection->beginTransaction();

            try {
                $entityManager->persist($entity);
                $entityManager->flush();
                $this->baseEntityClass->setFieldsFromEntityData($entity, true);

                if (!$specificAction->afterAction($this->baseEntityClass)) {
                    $connection->rollBack();
                    return $this->response("Regra de negócio impediu o cadastro", 400);
                }

                $connection->commit();
            } catch (\Throwable $exception) {
                $connection->rollBack();

                throw $exception;
            }

            return $this->singleResourceResponse($entity);
        } catch (\InvalidArgumentException $exception) {
            return $this->response($exception->getMessage(), 400);
        }
    }

    public function delete(int $id): JsonResponseHandlerInterface
    {
        try {
            if ($id <= 0) {
                return $this->response("ID inválido para exclusão", 400);
            }

            $entity = $this->baseEntityClass->getRepository()->find($id);

            if ($entity === null) {
                return $this->response("Registro não encontrado para exclusão", 404);
            }

            $this->baseEntityClass->setFieldsFromEntityData($entity, true);
            $this->setIdFieldValue($id);

            $specificAction = $this->baseEntityClass->setSpecificAction();
            if (!$specificAction->beforeDelete($this->baseEntityClass)) {
                return $this->response("Regra de negócio impediu a exclusão", 400);
            }

            $entityManager = $this->baseEntityClass->getEntityManager();
            $entityManager->remove($entity);

            if (!$specificAction->afterDelete($this->baseEntityClass)) {
                return $this->response("Regra de negócio impediu a exclusão", 400);
            }

            $entityManager->flush();

            return $this->response("Registro excluído com sucesso", 200);
        } catch (\InvalidArgumentException $exception) {
            return $this->response($exception->getMessage(), 400);
        }
    }

    public function edit(): JsonResponseHandlerInterface
    {
        try {
            $id = $this->getFieldId();
            if ($id === null || $id <= 0) {
                return $this->response("ID inválido para atualização", 400);
            }

            $entity = $this->baseEntityClass->getRepository()->find($id);
            if ($entity === null) {
                return $this->response("Registro não encontrado para atualização", 404);
            }

            $this->validateFields(updateOnly: true);

            $specificAction = $this->baseEntityClass->setSpecificAction();
            if (!$specificAction->preActionValidation($this->baseEntityClass)) {
                return $this->response("Regra de negócio impediu a atualização", 400);
            }

            if (!$specificAction->beforeUpdate($this->baseEntityClass)) {
                return $this->response("Regra de negócio impediu a atualização", 400);
            }

            $this->applyFieldsToEntity($entity, updateOnly: true);

            if (!$specificAction->preUpdate($this->baseEntityClass)) {
                return $this->response("Regra de negócio impediu a atualização", 400);
            }

            // Hooks como preSave/preUpdate podem alterar fields antes do flush.
            $this->applyFieldsToEntity($entity, updateOnly: true);

            $entityManager = $this->baseEntityClass->getEntityManager();
            $connection = $entityManager->getConnection();

            $connection->beginTransaction();

            try {
                $entityManager->flush();

                if (!$specificAction->afterUpdate($this->baseEntityClass)) {
                    $connection->rollBack();
                    return $this->response("Regra de negócio impediu a atualização", 400);
                }

                $connection->commit();
            } catch (\Throwable $exception) {
                $connection->rollBack();

                throw $exception;
            }

            return $this->singleResourceResponse($entity);
        } catch (\InvalidArgumentException $exception) {
            return $this->response($exception->getMessage(), 400);
        }
    }

    public function status(int $id, bool $status): JsonResponseHandlerInterface
    {
        try {
            if ($id <= 0) {
                return $this->response("ID inválido para atualização de status", 400);
            }

            $entity = $this->baseEntityClass->getRepository()->find($id);
            if ($entity === null) {
                return $this->response("Registro não encontrado para atualização de status", 404);
            }

            if (!method_exists($entity, 'setStatus')) {
                return $this->response("Entidade não permite atualização de status", 400);
            }

            $this->baseEntityClass->setFieldsFromEntityData($entity, true);
            $this->setIdFieldValue($id);
            $this->setStatusFieldValue($status);

            $specificAction = $this->baseEntityClass->setSpecificAction();
            if (!$specificAction->beforeChangeStatus($this->baseEntityClass)) {
                return $this->response("Regra de negócio impediu a alteração de status", 400);
            }

            $entity->setStatus($status);

            if (method_exists($entity, 'setUpdatedAt')) {
                $entity->setUpdatedAt(new \DateTimeImmutable());
            }

            if (!$specificAction->afterChangeStatus($this->baseEntityClass)) {
                return $this->response("Regra de negócio impediu a alteração de status", 400);
            }

            $this->baseEntityClass->getEntityManager()->flush();

            return $this->singleResourceResponse($entity);
        } catch (\InvalidArgumentException $exception) {
            return $this->response($exception->getMessage(), 400);
        }
    }

    public static function build(BaseEntityClassInterface $baseEntityClass, ?Closure $listQueryRestriction = null): ActionInterface
    {
        return new self($baseEntityClass, $listQueryRestriction);
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

    private function countRestrictedResults(\Doctrine\ORM\QueryBuilder $qb): int
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
            ->addData($this->baseEntityClass::SINGLEDATATERM, EntityBuilder::factory($dto));

        return JsonResponseHandler::create($response);
    }

    private function response(string $message, int $statusCode): JsonResponseHandlerInterface
    {
        return JsonResponseHandler::create(ResponseBuilder::build($message, $statusCode));
    }
}
