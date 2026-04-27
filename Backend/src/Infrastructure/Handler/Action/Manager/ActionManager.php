<?php

namespace App\Infrastructure\Handler\Action\Manager;

use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\Forms\FormDtoInterface;
use App\Infrastructure\DTO\Forms\StatusFormDto;
use App\Infrastructure\DTO\Params\Interface\QueryParamsInterface;
use App\Infrastructure\DTO\Params\QueryParams;
use App\Infrastructure\DTO\Response\ResponseBuilder;
use App\Infrastructure\Handler\Action\Action;
use App\Infrastructure\Handler\Action\ActionInterface;
use App\Infrastructure\Handler\Action\Manager\interface\ActionManagerInterface;
use App\Infrastructure\Handler\Response\JsonResponseHandler;
use App\Infrastructure\Handler\Response\JsonResponseHandlerInterface;
use Symfony\Component\HttpFoundation\Request;

final class ActionManager implements ActionManagerInterface
{
    public function handle(
        BaseEntityClassInterface $baseEntityClass,
        Request $request,
        ?QueryParamsInterface $queryParams = null,
        ?FormDtoInterface $formDto = null,
        ?int $id = null
    ): JsonResponseHandlerInterface {
        $action = Action::build($baseEntityClass);

        return match ($request->getMethod()) {
            Request::METHOD_GET => $this->handleGet($action, $queryParams, $id),
            Request::METHOD_POST => $this->handleSave($baseEntityClass, $action, $formDto),
            Request::METHOD_PUT,
            Request::METHOD_PATCH => $this->handleUpdate($baseEntityClass, $action, $formDto),
            Request::METHOD_DELETE => $this->handleDelete($action, $id),
            default => $this->response("Método não permitido", 405),
        };
    }

    public function handleStatus(
        BaseEntityClassInterface $baseEntityClass,
        int $id,
        StatusFormDto $formDto
    ): JsonResponseHandlerInterface {
        if ($id <= 0) {
            return $this->response("ID inválido para atualização de status", 400);
        }

        if ($formDto->status === null) {
            return $this->response("Status é obrigatório", 400);
        }

        return Action::build($baseEntityClass)->status($id, $formDto->status);
    }

    private function handleGet(
        ActionInterface $action,
        ?QueryParamsInterface $queryParams,
        ?int $id
    ): JsonResponseHandlerInterface
    {
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
    ): JsonResponseHandlerInterface
    {
        if ($formDto === null) {
            return $this->response("Dados obrigatórios para cadastro", 400);
        }

        $baseEntityClass->setFieldValues($formDto);

        return $action->save();
    }

    private function handleUpdate(
        BaseEntityClassInterface $baseEntityClass,
        ActionInterface $action,
        ?FormDtoInterface $formDto
    ): JsonResponseHandlerInterface
    {
        if ($formDto === null) {
            return $this->response("Dados obrigatórios para atualização", 400);
        }

        $id = $this->getFormId($formDto);
        $baseEntityClass->setFieldValues($formDto);

        if ($id === null) {
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

    private function getFormId(?FormDtoInterface $formDto): ?int
    {
        if ($formDto === null || !property_exists($formDto, 'id')) {
            return null;
        }

        $id = $formDto->id;

        if ($id === null || $id === '') {
            return null;
        }

        return is_numeric($id) ? (int) $id : 0;
    }

    private function response(string $message, int $statusCode): JsonResponseHandlerInterface
    {
        return JsonResponseHandler::create(ResponseBuilder::build($message, $statusCode));
    }
}
