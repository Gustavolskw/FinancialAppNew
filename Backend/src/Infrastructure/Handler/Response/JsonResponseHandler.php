<?php

namespace App\Infrastructure\Handler\Response;

use JsonSerializable;
use Symfony\Component\HttpFoundation\JsonResponse;

class JsonResponseHandler implements JsonResponseHandlerInterface
{
    private JsonSerializable $data;

    /**
     * @param JsonSerializable $data
     */
    public function __construct(JsonSerializable $data)
    {
        $this->data = $data;
    }

    public function output(): JsonResponse
    {
        return new JsonResponse($this->data->jsonSerialize());
    }

    public static function create(JsonSerializable $data): JsonResponseHandlerInterface
    {
        return new self ($data);
    }
}
