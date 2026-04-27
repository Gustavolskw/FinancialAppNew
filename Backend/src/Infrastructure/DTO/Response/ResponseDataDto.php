<?php

namespace App\Infrastructure\DTO\Response;

use App\Infrastructure\Helper\Interface\EntityClassCollection;

final class ResponseDataDto
{
    private string $dataTitle;
    private mixed $data = null;

    public static function make(string $title, EntityClassCollection $data): self
    {
        $dto = new self();
        $dto->setDataTitle($title);
        $dto->setData($data->output());
        return $dto;
    }

    public function getDataTitle(): string
    {
        return $this->dataTitle;
    }

    public function setDataTitle(string $dataTitle): self
    {
        $this->dataTitle = $dataTitle;
        return $this;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function setData(mixed $data): self
    {
        $this->data = $data;
        return $this;
    }
}
