<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Handler\Action\Specific;

use App\Entity\Expense;
use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;
use App\Infrastructure\Handler\Action\Specific\ExpenseSpecificAction;
use App\Tests\Fixtures\TransactionPayloadEntityDto;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

final class ExpenseSpecificActionTest extends TestCase
{
    public function testSpecificActionCreatesTransactionFromExpensePayload(): void
    {
        $wallet = (new Wallet())->setId('42')->setTitle('Principal')->setDescription('Carteira');
        $walletRepository = $this->createMock(EntityRepository::class);
        $walletRepository->method('find')->with(42)->willReturn($wallet);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->with(Wallet::class)->willReturn($walletRepository);

        $fields = (new FieldsAttribute())
            ->setRelationalField('transaction', Transaction::class, 'getExpenseTransaction');

        $dto = new TransactionPayloadEntityDto(
            $fields,
            $entityManager,
            null,
            [
                'amount' => '89.90',
                'location' => 'Farmacia',
                'description' => 'Remedio',
                'date' => '2026-04-29T10:00:00-03:00',
                'month' => 4,
                'year' => 2026,
                'walletId' => 42,
            ],
        );

        (new ExpenseSpecificAction($dto))->specificAction($dto);

        $transaction = $fields->getRelationalField('transaction')?->getRawValue();

        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertSame('89.90', $transaction->getAmount());
        self::assertSame('Farmacia', $transaction->getLocation());
        self::assertSame('Remedio', $transaction->getDescription());
        self::assertSame($wallet, $transaction->getTransactionWallet());
    }

    public function testBeforeUpdateReturnsFalseWithoutIdOrExpense(): void
    {
        $fields = (new FieldsAttribute())->setIdField('id');
        $dto = new TransactionPayloadEntityDto($fields);

        self::assertFalse((new ExpenseSpecificAction($dto))->beforeUpdate($dto));

        $fields->getIdField()?->setValue(10);
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(10)->willReturn(null);
        $dto = new TransactionPayloadEntityDto($fields, repository: $repository);

        self::assertFalse((new ExpenseSpecificAction($dto))->beforeUpdate($dto));
    }

    public function testBeforeUpdateUpdatesLinkedTransactionFromPayload(): void
    {
        $transaction = (new Transaction())
            ->setAmount('10.00')
            ->setLocation('Antigo')
            ->setDate(new \DateTime('2026-01-01'))
            ->setMonth(1)
            ->setYear(2026);
        $expense = (new Expense())->setId('10')->setExpenseTransaction($transaction);
        $fields = (new FieldsAttribute())->setIdField('id');
        $fields->getIdField()?->setValue(10);
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(10)->willReturn($expense);
        $dto = new TransactionPayloadEntityDto($fields, repository: $repository, transactionFieldValues: [
            'amount' => '55.25',
            'location' => 'Novo',
            'date' => new \DateTime('2026-04-30 12:00:00'),
            'month' => 4,
            'year' => 2026,
        ]);

        self::assertTrue((new ExpenseSpecificAction($dto))->beforeUpdate($dto));
        self::assertSame('55.25', $transaction->getAmount());
        self::assertSame('Novo', $transaction->getLocation());
        self::assertSame(4, $transaction->getMonth());
        self::assertSame(2026, $transaction->getYear());
        self::assertSame('2026-04-30T12:00:00+00:00', $transaction->getDate()?->format(\DateTimeInterface::ATOM));
    }

    public function testBeforeUpdateRejectsExpenseWithoutLinkedTransaction(): void
    {
        $expense = (new Expense())->setId('10');
        $fields = (new FieldsAttribute())->setIdField('id');
        $fields->getIdField()?->setValue(10);
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(10)->willReturn($expense);
        $dto = new TransactionPayloadEntityDto($fields, repository: $repository);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Transação vinculada não encontrada');

        (new ExpenseSpecificAction($dto))->beforeUpdate($dto);
    }

    public function testBeforeDeleteHandlesMissingDataAndRemovesLinkedTransaction(): void
    {
        $fields = (new FieldsAttribute())->setIdField('id');
        $dto = new TransactionPayloadEntityDto($fields);

        self::assertFalse((new ExpenseSpecificAction($dto))->beforeDelete($dto));

        $fields->getIdField()?->setValue(10);
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(10)->willReturnOnConsecutiveCalls(null, (new Expense())->setId('10'));
        $dto = new TransactionPayloadEntityDto($fields, repository: $repository);

        self::assertFalse((new ExpenseSpecificAction($dto))->beforeDelete($dto));
        self::assertFalse((new ExpenseSpecificAction($dto))->beforeDelete($dto));

        $transaction = new Transaction();
        $expense = (new Expense())->setId('10')->setExpenseTransaction($transaction);
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(10)->willReturn($expense);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('remove')->with($transaction);
        $dto = new TransactionPayloadEntityDto($fields, $entityManager, $repository);

        self::assertTrue((new ExpenseSpecificAction($dto))->beforeDelete($dto));
    }
}
