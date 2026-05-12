<?php

namespace App\Infrastructure\DTO\Params\QueryParams;

use Symfony\Component\Validator\Constraints as Assert;

final class EntryQueryParamsDto extends PaginatorQueryParamsDto
{
    public function __construct(
        ?int $page = 1,
        ?int $perPage = 20,
        public ?int $entryTypeId = null,
        public ?string $amount = null,
        #[Assert\Length(max: 50)]
        public ?string $location = null,
        #[Assert\Length(max: 255)]
        public ?string $description = null,
        public ?string $date = null,
        public ?int $month = null,
        public ?int $year = null,
        public ?int $walletId = null,
    ) {
        parent::__construct($page, $perPage);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            ...parent::toArray(),
            'entryTypeId' => $this->entryTypeId,
            'amount' => $this->amount,
            'location' => $this->location,
            'description' => $this->description,
            'date' => $this->date,
            'month' => $this->month,
            'year' => $this->year,
            'walletId' => $this->walletId,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }
}
