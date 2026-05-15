<?php

declare(strict_types=1);

namespace App\Infrastructure\Helper\ActionManager;

use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\Forms\FormDtoInterface;
use App\Infrastructure\DTO\Params\Interface\QueryParamsInterface;
use App\Infrastructure\DTO\Params\QueryParams;
use App\Infrastructure\Handler\Action\ActionInterface;
use App\Infrastructure\Handler\Response\JsonResponseHandlerInterface;

trait ActionManagerDispatchTrait
{
    private function handleGet(
        ActionInterface $action,
        ?QueryParamsInterface $queryParams,
        ?int $id
    ): JsonResponseHandlerInterface {
        if ($id !== null) {
            if ($id <= 0) {
                return $this->response("ID inválido para consulta", 400);
            }

            return $action->view($id);
        }

        return $action->listView($queryParams ?? QueryParams::fromArray([]));
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

        return $action->save();
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

            return $action->save();
        }

        if ($id <= 0) {
            return $this->response("ID inválido para atualização", 400);
        }

        if ($baseEntityClass->getRepository()->find($id) === null) {
            return $this->response("Registro não encontrado para atualização", 404);
        }

        return $action->edit();
    }

    private function handleDelete(ActionInterface $action, ?int $id): JsonResponseHandlerInterface
    {
        if ($id === null || $id <= 0) {
            return $this->response("ID inválido para exclusão", 400);
        }

        return $action->delete($id);
    }
}
