<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\DTO\EntityAttributes;

use App\Entity\Wallet;
use App\Infrastructure\DTO\EntityAttributes\Enum\RolesEnum;
use App\Infrastructure\DTO\EntityAttributes\Fields\EnumFieldDto;
use App\Infrastructure\DTO\EntityAttributes\Fields\FieldsInterface;
use App\Infrastructure\DTO\EntityAttributes\Fields\RelationalAttributeDto;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;
use PHPUnit\Framework\TestCase;

final class FieldsAttributeTest extends TestCase
{
    public function testRequiredFieldValidationFailsWhenValueIsMissing(): void
    {
        $fields = (new FieldsAttribute())->setNameField('name', required: true);
        $field = $fields->getNameField();

        self::assertInstanceOf(FieldsInterface::class, $field);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Campo name é obrigatório');

        $field->validate();
    }

    public function testFieldValueChangeResetsValidationState(): void
    {
        $field = (new FieldsAttribute())
            ->setNameField('name', required: true)
            ->getNameField();

        self::assertInstanceOf(FieldsInterface::class, $field);

        $field->setValue('Carteira principal')->validate();
        self::assertTrue($field->isValidated());

        $field->setValue('Carteira reserva');
        self::assertFalse($field->isValidated());
    }

    public function testEnumFieldStoresRawValueAndReturnsEnumRepresentation(): void
    {
        $field = (new FieldsAttribute())
            ->setEnumField('role', 'getRole', RolesEnum::class, required: true)
            ->getEnumField('role');

        self::assertInstanceOf(EnumFieldDto::class, $field);

        $field->setValue(RolesEnum::ADM->value())->validate();

        self::assertSame(RolesEnum::ADM->value(), $field->getRawValue());
        self::assertSame(RolesEnum::ADM, $field->getValue());
    }

    public function testEnumFieldRejectsUnknownOption(): void
    {
        $field = (new FieldsAttribute())
            ->setEnumField('role', 'getRole', RolesEnum::class)
            ->getEnumField('role');

        self::assertInstanceOf(EnumFieldDto::class, $field);

        $field->setValue(99);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Opção inválida para campo role');

        $field->validate();
    }

    public function testRelationalFieldAcceptsNumericStringAndCastsItToInt(): void
    {
        $field = (new FieldsAttribute())
            ->setRelationalField('wallet', Wallet::class, 'getTransactionWallet', required: true)
            ->getRelationalField('wallet');

        self::assertInstanceOf(RelationalAttributeDto::class, $field);

        $field->setValue('42')->validate();

        self::assertSame(42, $field->getRawValue());
    }

    public function testStatusFieldRejectsNonBooleanValues(): void
    {
        $field = (new FieldsAttribute())
            ->setStatusField('status', required: true)
            ->getStatusField();

        self::assertInstanceOf(FieldsInterface::class, $field);
        $field->setValue('true');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Valor inválido para campo status');

        $field->validate();
    }
}
