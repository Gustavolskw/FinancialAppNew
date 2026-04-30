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
        $data = $this->data->jsonSerialize();
        $statusCode = isset($data['statusCode']) && is_int($data['statusCode']) ? $data['statusCode'] : 200;

        return new JsonResponse($data, $statusCode);
    }

    public static function create(JsonSerializable $data): JsonResponseHandlerInterface
    {
        return new self ($data);
    }
}
