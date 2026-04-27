<?php

namespace App\Infrastructure\DTO\Response;

use App\Infrastructure\Helper\Interface\EntityClassCollection;

final class ResponseBuilder implements ResponseBuilderInterface
{
    private string $responseMessage;
    private int $statusCode {
        get {
            return $this->statusCode;
        }
    }

    /** @var ResponseDataDto[] */
    private array $data = [];

    private function __construct(string $responseMessage, int $statusCode)
    {
        $this->responseMessage = $responseMessage;
        $this->statusCode = $statusCode;
    }

    public static function build(string $responseMessage, int $statusCode): ResponseBuilderInterface
    {
        return new self($responseMessage, $statusCode);
    }

    public function addData(string $dataTitle, EntityClassCollection $data): ResponseBuilderInterface
    {
        $this->data[] = ResponseDataDto::make($dataTitle, $data);
        return $this;
    }

    public function jsonSerialize(): array
    {
        $mapped = [];
        foreach ($this->data as $dto) {
            $mapped[$dto->getDataTitle()] = $dto->getData();
        }

        return [
            'message' => $this->responseMessage,
            'statusCode' => $this->statusCode,
            'data' => $mapped,
        ];
    }
}
