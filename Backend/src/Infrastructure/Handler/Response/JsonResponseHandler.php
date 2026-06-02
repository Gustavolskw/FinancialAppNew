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
        return new self($data);
    }

    public static function createFromArray(string $message, int $statusCode, array $data): JsonResponseHandlerInterface
    {
        $serializable = new class($message, $statusCode, $data) implements JsonSerializable {
            public function __construct(
                private readonly string $message,
                private readonly int $statusCode,
                private readonly array $data
            ) {
            }

            public function jsonSerialize(): array
            {
                return [
                    'message' => $this->message,
                    'statusCode' => $this->statusCode,
                    'data' => $this->data,
                ];
            }
        };

        return new self($serializable);
    }
}
