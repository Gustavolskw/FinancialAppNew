<?php

namespace App\Infrastructure\Handler\Action;

class ActionHandler implements ActionHandlerInterface
{
    private Action $action;
    public function __construct(Action $action)
    {
        $this->action = $action;
    }
    public static function Build(ActionInterface $action): ActionHandlerInterface
    {
        return new self($action);
    }

    function execute(): ActionInterface
    {
       return $this->action;
    }
}
