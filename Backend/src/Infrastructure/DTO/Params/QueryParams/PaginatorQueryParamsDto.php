<?php

namespace App\Infrastructure\DTO\Params\QueryParams;

use App\Infrastructure\DTO\Params\Interface\BaseQueryParamsInterface;
use Symfony\Component\Validator\Constraints as Assert;

class PaginatorQueryParamsDto implements BaseQueryParamsInterface
{
    public function __construct(
        #[Assert\Positive(message: 'page deve ser maior que 0.')]
        public ?int $page = 1,

        #[Assert\Positive(message: 'perPage deve ser maior que 0.')]
        #[Assert\LessThanOrEqual(100, message: 'perPage deve ser no máximo 100.')]
        public ?int $perPage = 20,
    ) {}

    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'perPage' => $this->perPage,
        ];
    }
}
