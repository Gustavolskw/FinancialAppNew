<?php

namespace App\Infrastructure\Handler\Action;

use App\Infrastructure\Handler\Response\JsonResponseHandlerInterface;

interface ActionHandlerInterface
{
    public static function Build(ActionInterface $action) :ActionHandlerInterface ;
    function execute(): ActionInterface;
}
