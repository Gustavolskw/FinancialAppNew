<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Infrastructure\DTO\EntityAttributes\FieldsAttributeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class TransactionPayloadEntityDto extends DummyEntityDto
{
    /**
     * @param array<string, mixed> $transactionFieldValues
     */
    public function __construct(
        FieldsAttributeInterface $fields,
        ?EntityManagerInterface $entityManager = null,
        ?EntityRepository $repository = null,
        private array $transactionFieldValues = [],
    ) {
        parent::__construct($fields, $entityManager, $repository);
    }

    /**
     * @return array<string, mixed>
     */
    public function getTransactionFieldValues(): array
    {
        return $this->transactionFieldValues;
    }
}
