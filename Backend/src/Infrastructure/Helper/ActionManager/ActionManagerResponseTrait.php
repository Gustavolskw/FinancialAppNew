<?php

declare(strict_types=1);

namespace App\Infrastructure\Helper\ActionManager;

use App\Infrastructure\DTO\Response\ResponseBuilder;
use App\Infrastructure\Handler\Response\JsonResponseHandler;
use App\Infrastructure\Handler\Response\JsonResponseHandlerInterface;

trait ActionManagerResponseTrait
{
    private function response(string $message, int $statusCode): JsonResponseHandlerInterface
    {
        return JsonResponseHandler::create(ResponseBuilder::build($message, $statusCode));
    }
}
