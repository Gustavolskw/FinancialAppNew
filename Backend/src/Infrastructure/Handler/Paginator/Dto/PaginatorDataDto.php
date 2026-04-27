<?php

namespace App\Infrastructure\Handler\Paginator\Dto;

class PaginatorDataDto
{
    private string $title;
    private mixed $value;

    public function __construct(string $title, mixed $value)
    {
        $this->title = $title;
        $this->value = $value;
    }

    public function output(): array
    {
        return [$this->title => $this->value];
    }


}
