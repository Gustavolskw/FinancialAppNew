<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Handler\Analytics;

use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;
use App\Infrastructure\DTO\EntityAttributes\FieldTypeEnum;
use App\Infrastructure\Handler\Analytics\SimpleDataAnalytics;
use App\Tests\Fixtures\DummyEntityDto;
use PHPUnit\Framework\TestCase;

final class SimpleDataAnalyticsTest extends TestCase
{
    public function testAnalyticsCountsPercentagesAndSumsConfiguredFields(): void
    {
        $first = $this->dto('Mercado', true, '1.234,56');
        $second = $this->dto('Lazer', false, 10);

        $output = SimpleDataAnalytics::build([$first, $second])
            ->countAnalyses()
            ->percentAnalyses('activePercent', 'status', 'true')
            ->someAnalyses('amount')
            ->output();

        self::assertSame([
            'dummyEntitiesCount' => 2,
            'activePercent' => 50.0,
            'amountSum' => 1244.56,
        ], $output);
    }

    public function testAnalyticsHandlesEmptyCollections(): void
    {
        $output = SimpleDataAnalytics::build([])
            ->countAnalyses()
            ->percentAnalyses('activePercent', 'status', 'true')
            ->someAnalyses('amount')
            ->output();

        self::assertSame([
            'itemsCount' => 0,
            'activePercent' => 0,
            'amountSum' => 0.0,
        ], $output);
    }

    public function testPercentAnalysesComparesStringsNumbersBooleansDatesAndSkipsMissingValues(): void
    {
        $first = $this->dtoWithFields(
            name: 'Mercado Central',
            status: false,
            amount: 10,
            date: new \DateTimeImmutable('2026-04-30 10:00:00')
        );
        $second = $this->dtoWithFields(
            name: 'Lazer',
            status: true,
            amount: 20,
            date: new \DateTimeImmutable('2026-05-01 10:00:00')
        );
        $nullValues = $this->dtoWithFields(name: null, status: null, amount: null, date: null);
        $missingFields = new DummyEntityDto(new FieldsAttribute());

        $output = SimpleDataAnalytics::build([$first, $second, $nullValues, $missingFields])
            ->percentAnalyses('marketPercent', 'name', 'mercado')
            ->percentAnalyses('inactivePercent', 'status', 'false')
            ->percentAnalyses('amountPercent', 'amount', '10')
            ->percentAnalyses('aprilPercent', 'date', '2026-04')
            ->output();

        self::assertSame([
            'marketPercent' => 25.0,
            'inactivePercent' => 25.0,
            'amountPercent' => 25.0,
            'aprilPercent' => 25.0,
        ], $output);
    }

    public function testSomeAnalysesSkipsMissingNullAndNonNumericValues(): void
    {
        $numeric = $this->dtoWithFields(amount: 10.5);
        $brlString = $this->dtoWithFields(amount: '1.000,25');
        $nonNumeric = $this->dtoWithFields(amount: 'abc');
        $nullValue = $this->dtoWithFields(amount: null);
        $missingFields = new DummyEntityDto(new FieldsAttribute());

        $output = SimpleDataAnalytics::build([$numeric, $brlString, $nonNumeric, $nullValue, $missingFields])
            ->someAnalyses('amount')
            ->output();

        self::assertSame(['amountSum' => 1010.75], $output);
    }

    private function dto(string $name, bool $status, int|float|string $amount): DummyEntityDto
    {
        $fields = (new FieldsAttribute())
            ->setNameField('name')
            ->setStatusField('status')
            ->setValueField('amount', 'getAmount');

        $fields->getNameField()?->setValue($name);
        $fields->getStatusField()?->setValue($status);
        $fields->getValueField('amount')?->setValue($amount);

        return new DummyEntityDto($fields);
    }

    private function dtoWithFields(
        ?string $name = null,
        ?bool $status = null,
        int|float|string|null $amount = null,
        ?\DateTimeInterface $date = null,
    ): DummyEntityDto {
        $fields = (new FieldsAttribute())
            ->setNameField('name')
            ->setStatusField('status')
            ->setValueField('amount', 'getAmount')
            ->setDateField('date', 'getDate', FieldTypeEnum::DATETIMEFIELD);

        $fields->getNameField()?->setValue($name);
        $fields->getStatusField()?->setValue($status);
        $fields->getValueField('amount')?->setValue($amount);
        $fields->getDateField('date', FieldTypeEnum::DATETIMEFIELD)?->setValue($date);

        return new DummyEntityDto($fields);
    }
}
