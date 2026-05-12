<?php

namespace App\Infrastructure\Handler\Action;

use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\Params\Interface\QueryParamsInterface;
use App\Infrastructure\DTO\Response\ResponseBuilder;
use App\Infrastructure\Handler\Analytics\SimpleDataAnalytics;
use App\Infrastructure\Handler\Paginator\SimpleDataPaginator;
use App\Infrastructure\Handler\Response\JsonResponseHandler;
use App\Infrastructure\Handler\Response\JsonResponseHandlerInterface;
use App\Infrastructure\Helper\Action\ActionHelperTrait;
use App\Infrastructure\Helper\BaseHelper;
use App\Infrastructure\Helper\Response\EntityBuilder;
use App\Infrastructure\Helper\Response\EntityListBuilder;
use Closure;

class Action implements ActionInterface
{
    use ActionHelperTrait;

    private BaseEntityClassInterface $baseEntityClass;
    private ?Closure $listQueryRestriction;

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
}
