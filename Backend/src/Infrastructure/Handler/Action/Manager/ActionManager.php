<?php

namespace App\Infrastructure\Handler\Action\Manager;

use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\Forms\FormDtoInterface;
use App\Infrastructure\DTO\Forms\StatusFormDto;
use App\Infrastructure\DTO\Params\Interface\QueryParamsInterface;
use App\Infrastructure\Handler\Action\Action;
use App\Infrastructure\Handler\Action\Manager\interface\ActionManagerInterface;
use App\Infrastructure\Handler\Response\JsonResponseHandlerInterface;
use App\Infrastructure\Helper\ActionManager\ActionManagerDispatchTrait;
use App\Infrastructure\Helper\ActionManager\ActionManagerRequestTrait;
use App\Infrastructure\Helper\ActionManager\ActionManagerResponseTrait;
use App\Infrastructure\Helper\Auth\JwtAuthenticationHelperTrait;
use App\Infrastructure\Helper\Auth\RecordAuthorizationHelperTrait;
use Symfony\Component\HttpFoundation\Request;

final class ActionManager implements ActionManagerInterface
{
    use JwtAuthenticationHelperTrait;
    use RecordAuthorizationHelperTrait;
    use ActionManagerDispatchTrait;
    use ActionManagerRequestTrait;
    use ActionManagerResponseTrait;

    public function handle(
        BaseEntityClassInterface $baseEntityClass,
        Request $request,
        ?QueryParamsInterface $queryParams = null,
        ?FormDtoInterface $formDto = null,
        ?int $id = null
    ): JsonResponseHandlerInterface {
        if ($this->isPublicUserCreate($baseEntityClass, $request, $id)) {
            if ($this->actionManagerRequestPayloadHas($request, 'role')) {
                return $this->response('Perfil de acesso não pode ser enviado na criação normal de usuário', 403);
            }

            return $this->handleSave($baseEntityClass, Action::build($baseEntityClass), $formDto);
        }

        $authenticationResponse = $this->authenticateRequest($request);

        if ($authenticationResponse !== null) {
            return $authenticationResponse;
        }

        $authorizationResponse = $this->authorizeRecordAccess($baseEntityClass, $request, $formDto, $id);

        if ($authorizationResponse !== null) {
            return $authorizationResponse;
        }

        $action = Action::build($baseEntityClass, $this->recordListQueryRestriction($baseEntityClass));

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
        Request $request,
        int $id,
        StatusFormDto $formDto
    ): JsonResponseHandlerInterface {
        $authenticationResponse = $this->authenticateRequest($request);

        if ($authenticationResponse !== null) {
            return $authenticationResponse;
        }

        $authorizationResponse = $this->authorizeRecordAccess($baseEntityClass, $request, $formDto, $id);

        if ($authorizationResponse !== null) {
            return $authorizationResponse;
        }

        if ($id <= 0) {
            return $this->response("ID inválido para atualização de status", 400);
        }

        if ($formDto->status === null) {
            return $this->response("Status é obrigatório", 400);
        }

        return Action::build($baseEntityClass)->status($id, $formDto->status);
    }
}
