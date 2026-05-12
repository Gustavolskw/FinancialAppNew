<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\DTO\EntityAttributes;

use App\Entity\Wallet;
use App\Infrastructure\DTO\EntityAttributes\Enum\RolesEnum;
use App\Infrastructure\DTO\EntityAttributes\FieldTypeEnum;
use App\Infrastructure\DTO\EntityAttributes\Fields\BasicFieldDto;
use App\Infrastructure\DTO\EntityAttributes\Fields\DateFieldDto;
use App\Infrastructure\DTO\EntityAttributes\Fields\EnumFieldDto;
use App\Infrastructure\DTO\EntityAttributes\Fields\FieldsInterface;
use App\Infrastructure\DTO\EntityAttributes\Fields\IdFieldDto;
use App\Infrastructure\DTO\EntityAttributes\Fields\NameFieldDto;
use App\Infrastructure\DTO\EntityAttributes\Fields\PasswordFieldDto;
use App\Infrastructure\DTO\EntityAttributes\Fields\RelationalAttributeDto;
use App\Infrastructure\DTO\EntityAttributes\Fields\StatusFieldDto;
use App\Infrastructure\DTO\EntityAttributes\Fields\TextFieldDto;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;
use App\Tests\Fixtures\DummyEntityDto;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FieldDtosTest extends TestCase
{
    public function testFieldTypeEnumReportsExpectedNativeTypesAndSizes(): void
    {
        $expected = [
            FieldTypeEnum::IDFIELD->name => ['int', 10],
            FieldTypeEnum::NAMEFIELD->name => ['string', 100],
            FieldTypeEnum::TEXTFIELD->name => ['string', 255],
            FieldTypeEnum::EMAILFIELD->name => ['string', 100],
            FieldTypeEnum::LOCATIONFIELD->name => ['string', 50],
            FieldTypeEnum::PASSWORDFIELD->name => ['string', 255],
            FieldTypeEnum::RELATIONALFIELD->name => ['int', 10],
            FieldTypeEnum::OPTIONSFIELD->name => ['array', 50],
            FieldTypeEnum::VALUEFIELD->name => ['float', 255],
            FieldTypeEnum::NUMERICFIELD->name => ['int', 5],
            FieldTypeEnum::ENUMFIELD->name => ['int', 5],
            FieldTypeEnum::DATEFIELD->name => ['DateTime', 255],
            FieldTypeEnum::DATETIMEFIELD->name => ['DateTimeImmutable', 255],
            FieldTypeEnum::STATUSFIELD->name => ['bool', 5],
        ];

        foreach (FieldTypeEnum::cases() as $case) {
            self::assertSame($expected[$case->name][0], $case->getFieldType());
            self::assertSame($expected[$case->name][1], $case->getFieldSizeValidation());
        }
    }

    public function testFieldsAttributeCreatesAndRetrievesTypedFields(): void
    {
        $fields = (new FieldsAttribute())
            ->setOptionsField('paymentMethod', 'getPaymentMethod', ['pix' => 'PIX', 'cash' => 'Dinheiro'])
            ->setNumericField('amount', 'getAmount')
            ->setTextField('description', 'getDescription');

        self::assertInstanceOf(BasicFieldDto::class, $fields->getOptionsField('paymentMethod'));
        self::assertInstanceOf(BasicFieldDto::class, $fields->getNumericField('amount'));
        self::assertInstanceOf(TextFieldDto::class, $fields->getTextField('description', FieldTypeEnum::TEXTFIELD));
        self::assertNull($fields->getOptionsField('unknown'));
        self::assertNull($fields->getTextField('amount', FieldTypeEnum::TEXTFIELD));
        self::assertNull($fields->getNumericField('description'));
    }

    public function testFieldBaseAccessorsAndValidationCallback(): void
    {
        $called = false;
        $field = NameFieldDto::factory('name', FieldTypeEnum::NAMEFIELD, 'getName')
            ->setTable('wallet')
            ->setFieldType(FieldTypeEnum::TEXTFIELD)
            ->setEnumClass(RolesEnum::class)
            ->setName('title')
            ->setValidation(false, ['principal'], function (FieldsInterface $field) use (&$called): void {
                $called = $field->getValue() === 'Carteira principal';
            });

        self::assertSame('wallet', $field->getTableName());
        self::assertSame(FieldTypeEnum::TEXTFIELD, $field->getFieldType());
        self::assertSame(RolesEnum::class, $field->getEnumClass());
        self::assertSame('title', $field->getName());
        self::assertFalse($field->isRequired());
        self::assertSame(['principal'], $field->getOptions());
        self::assertSame('getName', $field->getEntityGetter());
        self::assertFalse($field->hasValue());

        $field->setValue('Carteira principal')->validate();

        self::assertTrue($called);
        self::assertTrue($field->isValidated());
    }

    public function testOptionalEmptyFieldIsConsideredValid(): void
    {
        $field = NameFieldDto::factory('name', FieldTypeEnum::NAMEFIELD, 'getName')
            ->setValidation(false)
            ->setValue('   ')
            ->validate();

        self::assertFalse($field->hasValue());
        self::assertTrue($field->isValidated());

        $arrayField = BasicFieldDto::factory('options', FieldTypeEnum::OPTIONSFIELD, 'getOptions')
            ->setValidation(false)
            ->setValue([])
            ->validate();

        self::assertFalse($arrayField->hasValue());
        self::assertTrue($arrayField->isValidated());
    }

    #[DataProvider('numericFieldsProvider')]
    public function testBasicNumericFieldsAcceptNumericValues(FieldTypeEnum $fieldType): void
    {
        $field = BasicFieldDto::factory('amount', $fieldType, 'getAmount')
            ->setValidation(true)
            ->setValue('150.75')
            ->validate();

        self::assertSame('150.75', $field->getValue());
    }

    /**
     * @return iterable<string, array{FieldTypeEnum}>
     */
    public static function numericFieldsProvider(): iterable
    {
        yield 'numeric' => [FieldTypeEnum::NUMERICFIELD];
        yield 'value' => [FieldTypeEnum::VALUEFIELD];
    }

    public function testBasicNumericFieldRejectsInvalidValue(): void
    {
        $field = BasicFieldDto::factory('amount', FieldTypeEnum::NUMERICFIELD, 'getAmount')
            ->setValidation(true)
            ->setValue('abc');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Valor inválido para campo numérico amount');

        $field->validate();
    }

    public function testBasicValueFieldValidatesDecimalPrecisionWhenConfigured(): void
    {
        $field = BasicFieldDto::factory('amount', FieldTypeEnum::VALUEFIELD, 'getAmount')
            ->setValidation(true, ['precision' => 10, 'scale' => 2])
            ->setValue('12345678.90')
            ->validate();

        self::assertSame('12345678.90', $field->getValue());
    }

    #[DataProvider('invalidDecimalPrecisionProvider')]
    public function testBasicValueFieldRejectsInvalidDecimalPrecision(string $amount): void
    {
        $field = BasicFieldDto::factory('amount', FieldTypeEnum::VALUEFIELD, 'getAmount')
            ->setValidation(true, ['precision' => 10, 'scale' => 2])
            ->setValue($amount);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Campo amount deve respeitar numeric(10, 2): até 8 dígitos antes do separador decimal e 2 casas decimais'
        );

        $field->validate();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidDecimalPrecisionProvider(): iterable
    {
        yield 'integer digits exceed precision' => ['123456789.90'];
        yield 'scale exceeds precision' => ['12345678.901'];
        yield 'scientific notation is rejected' => ['1e3'];
    }

    public function testOptionsFieldAcceptsKeysValuesArraysAndMissingOptionList(): void
    {
        $field = BasicFieldDto::factory('paymentMethod', FieldTypeEnum::OPTIONSFIELD, 'getPaymentMethod')
            ->setValidation(true, ['pix' => 'PIX', 'cash' => 'Dinheiro']);

        $field->setValue('pix')->validate();
        self::assertSame('pix', $field->getValue());

        $field->setValue('Dinheiro')->validate();
        self::assertSame('Dinheiro', $field->getValue());

        $field->setValue(['pix', 'Dinheiro'])->validate();
        self::assertSame(['pix', 'Dinheiro'], $field->getValue());

        $withoutOptions = BasicFieldDto::factory('freeOption', FieldTypeEnum::OPTIONSFIELD, 'getFreeOption')
            ->setValidation(false)
            ->setValue('qualquer')
            ->validate();

        self::assertSame('qualquer', $withoutOptions->getValue());
    }

    public function testOptionsFieldRejectsUnknownValue(): void
    {
        $field = BasicFieldDto::factory('paymentMethod', FieldTypeEnum::OPTIONSFIELD, 'getPaymentMethod')
            ->setValidation(true, ['pix' => 'PIX'])
            ->setValue(['pix', 'unknown']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Opção inválida para campo paymentMethod');

        $field->validate();
    }

    public function testBasicFieldValidationAllowsEmptyAndUnsupportedGenericFieldType(): void
    {
        $empty = BasicFieldDto::factory('paymentMethod', FieldTypeEnum::OPTIONSFIELD, 'getPaymentMethod')
            ->setValidation(false);

        self::assertSame($empty, $empty->fieldValidation());

        $generic = BasicFieldDto::factory('generic', FieldTypeEnum::NAMEFIELD, 'getGeneric')
            ->setValidation(false)
            ->setValue('valor');

        self::assertSame($generic, $generic->fieldValidation());
    }

    public function testDateFieldAcceptsDatesAndRejectsOtherValues(): void
    {
        $date = new \DateTimeImmutable('2026-04-30 10:00:00');
        $field = DateFieldDto::factory('date', FieldTypeEnum::DATETIMEFIELD, 'getDate')
            ->setValidation(true)
            ->setValue($date)
            ->validate();

        self::assertSame($date, $field->getValue());

        $optional = DateFieldDto::factory('optionalDate', FieldTypeEnum::DATEFIELD, 'getOptionalDate')
            ->setValidation(false)
            ->validate();

        self::assertNull($optional->getValue());

        $invalid = DateFieldDto::factory('date', FieldTypeEnum::DATEFIELD, 'getDate')
            ->setValidation(true)
            ->setValue('2026-04-30');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Valor inválido para campo de data date');

        $invalid->validate();
    }

    public function testIdFieldCastsNumericValuesAndRejectsInvalidValues(): void
    {
        $field = IdFieldDto::factory('id', FieldTypeEnum::IDFIELD, 'getId')
            ->setValidation(true)
            ->setValue('42')
            ->validate();

        self::assertSame(42, $field->getValue());

        $optional = IdFieldDto::factory('optionalId', FieldTypeEnum::IDFIELD, 'getOptionalId')
            ->setValidation(false)
            ->validate();

        self::assertNull($optional->getValue());

        $invalid = IdFieldDto::factory('id', FieldTypeEnum::IDFIELD, 'getId')
            ->setValidation(true)
            ->setValue('xpto');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Valor inválido para campo id id');

        $invalid->validate();
    }

    #[DataProvider('stringFieldProvider')]
    public function testStringFieldsAcceptStringsAndOptionalEmptyValues(string $className, FieldTypeEnum $fieldType): void
    {
        /** @var NameFieldDto|PasswordFieldDto|TextFieldDto $field */
        $field = $className::factory('description', $fieldType, 'getDescription')
            ->setValidation(true)
            ->setValue('valor')
            ->validate();

        self::assertSame('valor', $field->getValue());

        /** @var NameFieldDto|PasswordFieldDto|TextFieldDto $optional */
        $optional = $className::factory('optional', $fieldType, 'getOptional')
            ->setValidation(false)
            ->validate();

        self::assertNull($optional->getValue());
    }

    /**
     * @return iterable<string, array{class-string<NameFieldDto|PasswordFieldDto|TextFieldDto>, FieldTypeEnum}>
     */
    public static function stringFieldProvider(): iterable
    {
        yield 'name' => [NameFieldDto::class, FieldTypeEnum::NAMEFIELD];
        yield 'password' => [PasswordFieldDto::class, FieldTypeEnum::PASSWORDFIELD];
        yield 'text' => [TextFieldDto::class, FieldTypeEnum::TEXTFIELD];
    }

    #[DataProvider('invalidStringFieldProvider')]
    public function testStringFieldsRejectInvalidValues(
        string $className,
        FieldTypeEnum $fieldType,
        mixed $value,
        string $message
    ): void {
        /** @var NameFieldDto|PasswordFieldDto|TextFieldDto $field */
        $field = $className::factory('fieldName', $fieldType, 'getFieldName')
            ->setValidation(true)
            ->setValue($value);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $field->validate();
    }

    /**
     * @return iterable<string, array{class-string<NameFieldDto|PasswordFieldDto|TextFieldDto>, FieldTypeEnum, mixed, string}>
     */
    public static function invalidStringFieldProvider(): iterable
    {
        yield 'name non string' => [
            NameFieldDto::class,
            FieldTypeEnum::NAMEFIELD,
            123,
            'Valor inválido para campo de nome fieldName',
        ];
        yield 'name too long' => [
            NameFieldDto::class,
            FieldTypeEnum::NAMEFIELD,
            str_repeat('a', FieldTypeEnum::NAMEFIELD->getFieldSizeValidation() + 1),
            'Valor muito longo para campo fieldName',
        ];
        yield 'password non string' => [
            PasswordFieldDto::class,
            FieldTypeEnum::PASSWORDFIELD,
            false,
            'Valor inválido para campo de senha fieldName',
        ];
        yield 'password too long' => [
            PasswordFieldDto::class,
            FieldTypeEnum::PASSWORDFIELD,
            str_repeat('a', FieldTypeEnum::PASSWORDFIELD->getFieldSizeValidation() + 1),
            'Valor muito longo para campo fieldName',
        ];
        yield 'text non string' => [
            TextFieldDto::class,
            FieldTypeEnum::TEXTFIELD,
            10.5,
            'Valor inválido para campo de texto fieldName',
        ];
        yield 'text too long' => [
            TextFieldDto::class,
            FieldTypeEnum::TEXTFIELD,
            str_repeat('a', FieldTypeEnum::TEXTFIELD->getFieldSizeValidation() + 1),
            'Valor muito longo para campo fieldName',
        ];
    }

    public function testRelationalFieldAcceptsSupportedRelationShapes(): void
    {
        $wallet = new Wallet();
        $dto = new DummyEntityDto(DummyEntityDto::defaultFields());
        $field = RelationalAttributeDto::factory('wallet', FieldTypeEnum::RELATIONALFIELD, 'getWallet')
            ->setRelationalEntityClass(Wallet::class)
            ->setValidation(false);

        self::assertSame(Wallet::class, $field->getRelationalEntityClass());

        $field->setValue(10)->validate();
        self::assertSame(10, $field->getValue());

        $field->setValue('11')->validate();
        self::assertSame(11, $field->getValue());

        $field->setValue($wallet)->validate();
        self::assertSame($wallet, $field->getValue());

        $field->setValue($dto)->validate();
        self::assertSame($dto, $field->getValue());

        $optional = RelationalAttributeDto::factory('optionalWallet', FieldTypeEnum::RELATIONALFIELD, 'getOptionalWallet')
            ->setRelationalEntityClass(Wallet::class)
            ->setValidation(false)
            ->validate();

        self::assertNull($optional->getValue());
    }

    public function testRelationalFieldRejectsInvalidValue(): void
    {
        $field = RelationalAttributeDto::factory('wallet', FieldTypeEnum::RELATIONALFIELD, 'getWallet')
            ->setRelationalEntityClass(Wallet::class)
            ->setValidation(true)
            ->setValue(new \stdClass());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Valor inválido para campo relacional wallet');

        $field->validate();
    }

    public function testStatusFieldAcceptsBooleansAndOptionalEmptyValue(): void
    {
        $field = StatusFieldDto::factory('status', FieldTypeEnum::STATUSFIELD, 'isStatus')
            ->setValidation(true)
            ->setValue(false)
            ->validate();

        self::assertFalse($field->getValue());

        $optional = StatusFieldDto::factory('optionalStatus', FieldTypeEnum::STATUSFIELD, 'isOptionalStatus')
            ->setValidation(false)
            ->validate();

        self::assertNull($optional->getValue());
    }

    public function testConcreteFieldValidationMethodsAllowEmptyValues(): void
    {
        $fields = [
            DateFieldDto::factory('date', FieldTypeEnum::DATEFIELD, 'getDate')->setValidation(false),
            EnumFieldDto::factory('role', FieldTypeEnum::ENUMFIELD, 'getRole')
                ->setEnumClass(RolesEnum::class)
                ->setValidation(false),
            IdFieldDto::factory('id', FieldTypeEnum::IDFIELD, 'getId')->setValidation(false),
            NameFieldDto::factory('name', FieldTypeEnum::NAMEFIELD, 'getName')->setValidation(false),
            PasswordFieldDto::factory('password', FieldTypeEnum::PASSWORDFIELD, 'getPassword')->setValidation(false),
            RelationalAttributeDto::factory('wallet', FieldTypeEnum::RELATIONALFIELD, 'getWallet')
                ->setRelationalEntityClass(Wallet::class)
                ->setValidation(false),
            StatusFieldDto::factory('status', FieldTypeEnum::STATUSFIELD, 'isStatus')->setValidation(false),
            TextFieldDto::factory('description', FieldTypeEnum::TEXTFIELD, 'getDescription')->setValidation(false),
        ];

        foreach ($fields as $field) {
            self::assertSame($field, $field->fieldValidation());
        }
    }

    public function testEnumFieldHandlesEmptyAndInvalidConfiguration(): void
    {
        $optional = EnumFieldDto::factory('role', FieldTypeEnum::ENUMFIELD, 'getRole')
            ->setEnumClass(RolesEnum::class)
            ->setValidation(false)
            ->validate();

        self::assertNull($optional->getValue());
        self::assertNull($optional->getRawValue());

        $notInteger = EnumFieldDto::factory('role', FieldTypeEnum::ENUMFIELD, 'getRole')
            ->setEnumClass(RolesEnum::class)
            ->setValidation(true)
            ->setValue('1');

        try {
            $notInteger->validate();
            self::fail('Enum validation should reject non-integer values.');
        } catch (\InvalidArgumentException $exception) {
            self::assertSame('Valor inválido para campo enum role', $exception->getMessage());
        }

        $withoutEnumClass = EnumFieldDto::factory('role', FieldTypeEnum::ENUMFIELD, 'getRole')
            ->setValidation(true)
            ->setValue(1);

        try {
            $withoutEnumClass->validate();
            self::fail('Enum validation should reject missing enum class.');
        } catch (\InvalidArgumentException $exception) {
            self::assertSame('Classe enum não configurada para campo role', $exception->getMessage());
        }

        $invalidEnumClass = EnumFieldDto::factory('role', FieldTypeEnum::ENUMFIELD, 'getRole')
            ->setEnumClass(Wallet::class)
            ->setValidation(true)
            ->setValue(1);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Classe enum inválida para campo role');

        $invalidEnumClass->validate();
    }
}
