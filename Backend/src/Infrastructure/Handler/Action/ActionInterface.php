<?php

namespace App\Infrastructure\Handler\Action;

use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\Params\Interface\QueryParamsInterface;
use App\Infrastructure\Handler\Response\JsonResponseHandlerInterface;

interface ActionInterface
{
    public function listView(QueryParamsInterface $queryParams): JsonResponseHandlerInterface;
    public function view(int $id) : JsonResponseHandlerInterface;
    public function save() : JsonResponseHandlerInterface;
    public function delete(int $id) : JsonResponseHandlerInterface;
    public function edit() : JsonResponseHandlerInterface;
    public function status(int $id, bool $status) : JsonResponseHandlerInterface;
    public static function build(BaseEntityClassInterface $baseEntityClass): ActionInterface;
}
