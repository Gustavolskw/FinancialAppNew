<?php

namespace App\Infrastructure\DTO\Params\QueryParams;

use Symfony\Component\Validator\Constraints as Assert;

final class ExpenseQueryParamsDto extends PaginatorQueryParamsDto
{
    public function __construct(
        ?int $page = 1,
        ?int $perPage = 20,

        public ?int $expenseTypeId = null,

        public ?int $paymentMethodId = null,

        public ?int $installments = null,

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
            'expenseTypeId' => $this->expenseTypeId,
            'paymentMethodId' => $this->paymentMethodId,
            'installments' => $this->installments,
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
