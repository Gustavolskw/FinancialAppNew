<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Handler\Action\Specific;

use App\Entity\Entry;
use App\Entity\Transaction;
use App\Entity\Wallet;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;
use App\Infrastructure\Handler\Action\Specific\EntrySpecificAction;
use App\Tests\Fixtures\TransactionPayloadEntityDto;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

final class EntrySpecificActionTest extends TestCase
{
    public function testSpecificActionCreatesTransactionFromEntryPayload(): void
    {
        $wallet = (new Wallet())->setId('42')->setTitle('Principal')->setDescription('Carteira');
        $walletRepository = $this->createStub(EntityRepository::class);
        $walletRepository->method('find')->willReturn($wallet);

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($walletRepository);

        $fields = (new FieldsAttribute())
            ->setRelationalField('transaction', Transaction::class, 'getTransaction');

        $dto = new TransactionPayloadEntityDto(
            $fields,
            $entityManager,
            null,
            [
                'amount' => '150.75',
                'location' => 'Mercado',
                'description' => 'Compra do mes',
                'date' => '2026-04-29T10:00:00-03:00',
                'month' => 4,
                'year' => 2026,
                'walletId' => 42,
            ],
        );

        (new EntrySpecificAction($dto))->specificAction($dto);

        $transaction = $fields->getRelationalField('transaction')?->getRawValue();

        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertSame('150.75', $transaction->getAmount());
        self::assertSame('Mercado', $transaction->getLocation());
        self::assertSame('Compra do mes', $transaction->getDescription());
        self::assertSame(4, $transaction->getMonth());
        self::assertSame(2026, $transaction->getYear());
        self::assertSame($wallet, $transaction->getTransactionWallet());
        self::assertSame('2026-04-29T10:00:00-03:00', $transaction->getDate()?->format(\DateTimeInterface::ATOM));
    }

    public function testSpecificActionRequiresCoreTransactionFields(): void
    {
        $fields = (new FieldsAttribute())
            ->setRelationalField('transaction', Transaction::class, 'getTransaction');

        $dto = new TransactionPayloadEntityDto($fields, transactionFieldValues: [
            'amount' => '150.75',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Campo location é obrigatório');

        (new EntrySpecificAction($dto))->specificAction($dto);
    }

    public function testSpecificActionRequiresTransactionFieldConfiguration(): void
    {
        $dto = new TransactionPayloadEntityDto(
            new FieldsAttribute(),
            $this->walletEntityManager(),
            transactionFieldValues: $this->validTransactionPayload(),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Campo transaction não configurado');

        (new EntrySpecificAction($dto))->specificAction($dto);
    }

    public function testSpecificActionHandlesDtoWithoutTransactionPayloadMethodAsEmptyPayload(): void
    {
        $dto = new \App\Tests\Fixtures\DummyEntityDto(
            (new FieldsAttribute())->setRelationalField('transaction', Transaction::class, 'getTransaction'),
            $this->walletEntityManager(),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Campo amount é obrigatório');

        (new EntrySpecificAction($dto))->specificAction($dto);
    }

    /**
     * @param array<string, mixed> $payloadOverride
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('invalidPayloadProvider')]
    public function testSpecificActionRejectsInvalidTransactionPayload(array $payloadOverride, string $message): void
    {
        $fields = (new FieldsAttribute())
            ->setRelationalField('transaction', Transaction::class, 'getTransaction');
        $dto = new TransactionPayloadEntityDto(
            $fields,
            $this->walletEntityManager(walletFound: $message !== 'Carteira informada não encontrada'),
            transactionFieldValues: array_replace($this->validTransactionPayload(), $payloadOverride),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        (new EntrySpecificAction($dto))->specificAction($dto);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function invalidPayloadProvider(): iterable
    {
        yield 'amount not numeric' => [['amount' => 'abc'], 'Valor inválido para campo amount'];
        yield 'amount exceeds precision' => [['amount' => '123456789.90'], 'Campo amount deve respeitar numeric(10, 2)'];
        yield 'amount exceeds scale' => [['amount' => '12345678.901'], 'Campo amount deve respeitar numeric(10, 2)'];
        yield 'location blank' => [['location' => '  '], 'Campo location é obrigatório'];
        yield 'date invalid' => [['date' => 'not-a-date'], 'Valor inválido para campo date'];
        yield 'month not numeric' => [['month' => 'abril'], 'Valor inválido para campo month'];
        yield 'year zero' => [['year' => 0], 'Campo year deve ser maior que 0'];
        yield 'wallet not found' => [['walletId' => 404], 'Carteira informada não encontrada'];
    }

    public function testBeforeUpdateReturnsFalseWithoutIdOrEntry(): void
    {
        $fields = (new FieldsAttribute())->setIdField('id');
        $dto = new TransactionPayloadEntityDto($fields);

        self::assertFalse((new EntrySpecificAction($dto))->beforeUpdate($dto));

        $fields->getIdField()?->setValue(10);
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(10)->willReturn(null);
        $dto = new TransactionPayloadEntityDto($fields, repository: $repository);

        self::assertFalse((new EntrySpecificAction($dto))->beforeUpdate($dto));
    }

    public function testBeforeUpdateUpdatesLinkedTransactionFromPayload(): void
    {
        $transaction = (new Transaction())
            ->setAmount('10.00')
            ->setLocation('Antigo')
            ->setDate(new \DateTime('2026-01-01'))
            ->setMonth(1)
            ->setYear(2026);
        $entry = (new Entry())->setId('10')->setTransaction($transaction);
        $fields = (new FieldsAttribute())->setIdField('id');
        $fields->getIdField()?->setValue(10);
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(10)->willReturn($entry);
        $dto = new TransactionPayloadEntityDto($fields, repository: $repository, transactionFieldValues: [
            'amount' => '20.50',
            'location' => 'Novo',
            'description' => 'Atualizada',
            'date' => new \DateTimeImmutable('2026-04-30 12:00:00'),
            'month' => 4,
            'year' => 2026,
        ]);

        self::assertTrue((new EntrySpecificAction($dto))->beforeUpdate($dto));
        self::assertSame('20.50', $transaction->getAmount());
        self::assertSame('Novo', $transaction->getLocation());
        self::assertSame('Atualizada', $transaction->getDescription());
        self::assertSame(4, $transaction->getMonth());
        self::assertSame(2026, $transaction->getYear());
        self::assertSame('2026-04-30T12:00:00+00:00', $transaction->getDate()?->format(\DateTimeInterface::ATOM));
    }

    public function testBeforeUpdateRejectsEntryWithoutLinkedTransaction(): void
    {
        $entry = (new Entry())->setId('10');
        $fields = (new FieldsAttribute())->setIdField('id');
        $fields->getIdField()?->setValue(10);
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(10)->willReturn($entry);
        $dto = new TransactionPayloadEntityDto($fields, repository: $repository);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Transação vinculada não encontrada');

        (new EntrySpecificAction($dto))->beforeUpdate($dto);
    }

    public function testBeforeDeleteHandlesMissingDataAndRemovesLinkedTransaction(): void
    {
        $fields = (new FieldsAttribute())->setIdField('id');
        $dto = new TransactionPayloadEntityDto($fields);

        self::assertFalse((new EntrySpecificAction($dto))->beforeDelete($dto));

        $fields->getIdField()?->setValue(10);
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(10)->willReturnOnConsecutiveCalls(null, (new Entry())->setId('10'));
        $dto = new TransactionPayloadEntityDto($fields, repository: $repository);

        self::assertFalse((new EntrySpecificAction($dto))->beforeDelete($dto));
        self::assertFalse((new EntrySpecificAction($dto))->beforeDelete($dto));

        $transaction = new Transaction();
        $entry = (new Entry())->setId('10')->setTransaction($transaction);
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(10)->willReturn($entry);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('remove')->with($transaction);
        $dto = new TransactionPayloadEntityDto($fields, $entityManager, $repository);

        self::assertTrue((new EntrySpecificAction($dto))->beforeDelete($dto));
    }

    /**
     * @return array<string, mixed>
     */
    private function validTransactionPayload(): array
    {
        return [
            'amount' => '150.75',
            'location' => 'Mercado',
            'description' => 'Compra do mes',
            'date' => '2026-04-29T10:00:00-03:00',
            'month' => 4,
            'year' => 2026,
            'walletId' => 42,
        ];
    }

    private function walletEntityManager(bool $walletFound = true): EntityManagerInterface
    {
        $wallet = (new Wallet())->setId('42')->setTitle('Principal')->setDescription('Carteira');
        $walletRepository = $this->createStub(EntityRepository::class);
        $walletRepository->method('find')->willReturn($walletFound ? $wallet : null);

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($walletRepository);

        return $entityManager;
    }
}
