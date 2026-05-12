<?php

namespace App\Infrastructure\Handler\Action;

class ActionHandler implements ActionHandlerInterface
{
    private ActionInterface $action;

    public function __construct(ActionInterface $action)
    {
        $this->action = $action;
    }

    public static function build(ActionInterface $action): ActionHandlerInterface
    {
        return new self($action);
    }

    public function execute(): ActionInterface
    {
        return $this->action;
    }
}
