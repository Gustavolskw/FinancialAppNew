<?php

namespace App\Infrastructure\Handler\Action;

interface ActionHandlerInterface
{
    public static function build(ActionInterface $action): ActionHandlerInterface;
    public function execute(): ActionInterface;
}
