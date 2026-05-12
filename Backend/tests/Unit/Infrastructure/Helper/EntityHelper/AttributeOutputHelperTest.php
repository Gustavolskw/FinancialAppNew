<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Helper\EntityHelper;

use App\Entity\Wallet;
use App\Infrastructure\DTO\EntityAttributes\Enum\RolesEnum;
use App\Infrastructure\DTO\EntityAttributes\FieldTypeEnum;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;
use App\Infrastructure\Helper\EntityHelper\AttributeOutputHelper;
use App\Tests\Fixtures\DummyEntity;
use App\Tests\Fixtures\DummyEntityDto;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class AttributeOutputHelperTest extends TestCase
{
    public function testOutputEntityFieldsFormatsApiValuesAndHidesPasswords(): void
    {
        $fields = (new FieldsAttribute())
            ->setIdField('id')
            ->setNameField('name')
            ->setPassword('password')
            ->setEnumField('role', 'getRole', RolesEnum::class)
            ->setRelationalField('wallet', Wallet::class, 'getWallet')
            ->setDateField('date', 'getDate')
            ->setDateField('paidAt', 'getPaidAt', FieldTypeEnum::DATETIMEFIELD)
            ->setDateField('createdAt', 'getCreatedAt', FieldTypeEnum::DATETIMEFIELD)
            ->setDateField('updatedAt', 'getUpdatedAt', FieldTypeEnum::DATETIMEFIELD);

        $fields->getIdField()?->setValue(7);
        $fields->getNameField()?->setValue('Ana');
        $fields->getPasswordField('password')?->setValue('segredo');
        $fields->getEnumField('role')?->setValue(RolesEnum::ADM->value());
        $fields->getRelationalField('wallet')?->setValue(42);
        $fields->getDateField('date', FieldTypeEnum::DATEFIELD)?->setValue(new \DateTimeImmutable('2026-04-30 15:20:10+00:00'));
        $fields->getDateField('paidAt', FieldTypeEnum::DATETIMEFIELD)?->setValue(new \DateTimeImmutable('2026-04-30 14:30:00+00:00'));
        $fields->getDateField('createdAt', FieldTypeEnum::DATETIMEFIELD)?->setValue(new \DateTimeImmutable('2026-04-30 12:00:00+00:00'));
        $fields->getDateField('updatedAt', FieldTypeEnum::DATETIMEFIELD)?->setValue(new \DateTimeImmutable('2026-04-30 13:00:00+00:00'));

        self::assertSame([
            'id' => 7,
            'name' => 'Ana',
            'role' => 'Admin',
            'walletId' => 42,
            'date' => '30/04/2026',
            'paidAt' => '30/04/2026 11:30:00',
            'createdAt' => '30/04/2026 09:00:00',
            'updatedAt' => '30/04/2026 10:00:00',
        ], AttributeOutputHelper::outputEntityFields($fields->getFields()));
    }

    public function testOutputAttributeHandlesDtoIntegerAndNullValues(): void
    {
        $fields = DummyEntityDto::defaultFields();
        $fields->getIdField()?->setValue(10);
        $fields->getNameField()?->setValue('Conta');

        $dto = new DummyEntityDto($fields);

        self::assertSame([
            'id' => 10,
            'name' => 'Conta',
            'status' => null,
            'updatedAt' => null,
        ], AttributeOutputHelper::outputAttribute($dto));
        self::assertSame(15, AttributeOutputHelper::outputAttribute(15));
        self::assertNull(AttributeOutputHelper::outputAttribute(null));
    }

    public function testSetRelationalAttributeReturnsNullIdOrDeepDto(): void
    {
        $entity = (new DummyEntity())
            ->setId(99)
            ->setName('Relacionada')
            ->setStatus(true);
        $dto = new DummyEntityDto(
            DummyEntityDto::defaultFields(),
            $this->createStub(EntityManagerInterface::class),
        );

        self::assertNull(AttributeOutputHelper::setRelationalAttribute(false, null, $dto));
        self::assertSame(99, AttributeOutputHelper::setRelationalAttribute(false, $entity, $dto));
        self::assertNull(AttributeOutputHelper::setRelationalAttribute(false, new \stdClass(), $dto));

        $deepDto = AttributeOutputHelper::setRelationalAttribute(true, $entity, $dto);

        self::assertInstanceOf(DummyEntityDto::class, $deepDto);
        self::assertSame([
            'id' => 99,
            'name' => 'Relacionada',
            'status' => true,
            'updatedAt' => null,
        ], $deepDto->output());
    }

    public function testOutputEntityFieldsHandlesRelationalDtoAndNullDeferredDates(): void
    {
        $relatedFields = DummyEntityDto::defaultFields();
        $relatedFields->getIdField()?->setValue(20);
        $relatedFields->getNameField()?->setValue('Relacionada');
        $relatedDto = new DummyEntityDto($relatedFields);

        $fields = (new FieldsAttribute())
            ->setRelationalField('related', DummyEntity::class, 'getRelated')
            ->setDateField('createdAt', 'getCreatedAt', FieldTypeEnum::DATETIMEFIELD)
            ->setDateField('updatedAt', 'getUpdatedAt', FieldTypeEnum::DATETIMEFIELD);

        $fields->getRelationalField('related')?->setValue($relatedDto);

        self::assertSame([
            'related' => [
                'id' => 20,
                'name' => 'Relacionada',
                'status' => null,
                'updatedAt' => null,
            ],
            'createdAt' => null,
            'updatedAt' => null,
        ], AttributeOutputHelper::outputEntityFields($fields->getFields()));
    }
}
