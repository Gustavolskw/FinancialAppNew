<?php

declare(strict_types=1);

namespace App\Infrastructure\Helper\ActionManager;

use App\Infrastructure\DTO\Configuration\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\Forms\FormDtoInterface;
use App\Infrastructure\DTO\Params\Interface\QueryParamsInterface;
use App\Infrastructure\DTO\Params\QueryParams;
use App\Infrastructure\Handler\Action\ActionInterface;
use App\Infrastructure\Handler\Response\JsonResponseHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait ActionManagerDispatchTrait
{
    private function handleGet(
        BaseEntityClassInterface $baseEntityClass,
        ActionInterface $action,
        Request $request,
        ?QueryParamsInterface $queryParams,
        ?int $id
    ): JsonResponseHandlerInterface {
        if ($id !== null) {
            return $this->handleViewGet($baseEntityClass, $action, $request, $queryParams, $id);
        }

        return $this->handleListGet($baseEntityClass, $action, $request, $queryParams);
    }

    private function handleViewGet(
        BaseEntityClassInterface $baseEntityClass,
        ActionInterface $action,
        Request $request,
        ?QueryParamsInterface $queryParams,
        int $id
    ): JsonResponseHandlerInterface {
        if ($id <= 0) {
            return $this->response("ID inválido para consulta", 400);
        }

        return $this->cachedGet($baseEntityClass, $action, $request, $queryParams, $id);
    }

    private function handleListGet(
        BaseEntityClassInterface $baseEntityClass,
        ActionInterface $action,
        Request $request,
        ?QueryParamsInterface $queryParams
    ): JsonResponseHandlerInterface {
        return $this->cachedGet($baseEntityClass, $action, $request, $queryParams, null);
    }

    private function handleSave(
        BaseEntityClassInterface $baseEntityClass,
        ActionInterface $action,
        ?FormDtoInterface $formDto
    ): JsonResponseHandlerInterface {
        if ($formDto === null) {
            return $this->response("Dados obrigatórios para cadastro", 400);
        }

        $baseEntityClass->setFieldValues($formDto);
        $this->applyAuthenticatedCatalogDefaults($baseEntityClass);

        $response = $action->save();
        $this->invalidateCacheAfterSuccessfulMutation($baseEntityClass, $response);

        return $response;
    }

    private function handleUpdate(
        BaseEntityClassInterface $baseEntityClass,
        ActionInterface $action,
        ?FormDtoInterface $formDto
    ): JsonResponseHandlerInterface {
        if ($formDto === null) {
            return $this->response("Dados obrigatórios para atualização", 400);
        }

        $id = $this->getFormId($formDto);
        $baseEntityClass->setFieldValues($formDto);

        if ($id === null) {
            $this->applyAuthenticatedCatalogDefaults($baseEntityClass);

            $response = $action->save();
            $this->invalidateCacheAfterSuccessfulMutation($baseEntityClass, $response);

            return $response;
        }

        if ($id <= 0) {
            return $this->response("ID inválido para atualização", 400);
        }

        if ($baseEntityClass->getRepository()->find($id) === null) {
            return $this->response("Registro não encontrado para atualização", 404);
        }

        $response = $action->edit();
        $this->invalidateCacheAfterSuccessfulMutation($baseEntityClass, $response);

        return $response;
    }

    private function handleDelete(
        BaseEntityClassInterface $baseEntityClass,
        ActionInterface $action,
        ?int $id
    ): JsonResponseHandlerInterface {
        if ($id === null || $id <= 0) {
            return $this->response("ID inválido para exclusão", 400);
        }

        $response = $action->delete($id);
        $this->invalidateCacheAfterSuccessfulMutation($baseEntityClass, $response);

        return $response;
    }

    private function cachedGet(
        BaseEntityClassInterface $baseEntityClass,
        ActionInterface $action,
        Request $request,
        ?QueryParamsInterface $queryParams,
        ?int $id
    ): JsonResponseHandlerInterface {
        $queryParams ??= QueryParams::fromArray([]);

        if ($this->requestCacheHandler === null || !$this->requestCacheHandler->supports($baseEntityClass)) {
            return $this->executeGetAction($action, $queryParams, $id);
        }

        $currentUser = $this->currentAuthenticatedUser($baseEntityClass);

        return $this->requestCacheHandler->get(
            $baseEntityClass,
            $request,
            $queryParams,
            $id,
            $currentUser?->getId(),
            $currentUser?->getRole(),
            fn (): JsonResponseHandlerInterface => $this->executeGetAction($action, $queryParams, $id)
        );
    }

    private function executeGetAction(
        ActionInterface $action,
        QueryParamsInterface $queryParams,
        ?int $id
    ): JsonResponseHandlerInterface {
        if ($id === null) {
            return $action->listView($queryParams);
        }

        return $action->view($id);
    }

    private function invalidateCacheAfterSuccessfulMutation(
        BaseEntityClassInterface $baseEntityClass,
        JsonResponseHandlerInterface $response
    ): void {
        if ($this->requestCacheHandler === null || !$this->requestCacheHandler->supports($baseEntityClass)) {
            return;
        }

        $statusCode = $response->output()->getStatusCode();

        if ($statusCode >= Response::HTTP_OK && $statusCode < Response::HTTP_MULTIPLE_CHOICES) {
            $this->requestCacheHandler->invalidateCacheableRequests();
        }
    }
}
