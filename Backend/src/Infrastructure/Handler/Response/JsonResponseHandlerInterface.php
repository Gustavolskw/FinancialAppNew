<?php

namespace App\Infrastructure\Handler\Response;

use JsonSerializable;
use Symfony\Component\HttpFoundation\JsonResponse;

interface JsonResponseHandlerInterface
{
    public static function create(JsonSerializable $data): JsonResponseHandlerInterface;
    public function output(): JsonResponse;

}
