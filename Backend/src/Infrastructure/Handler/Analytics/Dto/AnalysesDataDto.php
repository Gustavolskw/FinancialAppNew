<?php

namespace App\Infrastructure\Handler\Analytics\Dto;

class AnalysesDataDto
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
