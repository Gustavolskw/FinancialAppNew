<?php

namespace App\Infrastructure\Handler\Action\Manager\interface;

use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\Forms\FormDtoInterface;
use App\Infrastructure\DTO\Forms\StatusFormDto;
use App\Infrastructure\DTO\Params\Interface\QueryParamsInterface;
use App\Infrastructure\Handler\Response\JsonResponseHandlerInterface;
use Symfony\Component\HttpFoundation\Request;

interface ActionManagerInterface
{
    public function handle(
        BaseEntityClassInterface $baseEntityClass,
        Request $request,
        ?QueryParamsInterface $queryParams = null,
        ?FormDtoInterface $formDto = null,
        ?int $id = null
    ): JsonResponseHandlerInterface;

    public function handleStatus(
        BaseEntityClassInterface $baseEntityClass,
        Request $request,
        int $id,
        StatusFormDto $formDto
    ): JsonResponseHandlerInterface;
}
