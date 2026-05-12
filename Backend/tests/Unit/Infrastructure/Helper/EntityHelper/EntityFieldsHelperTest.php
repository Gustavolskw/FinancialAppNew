<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Helper\EntityHelper;

use App\Infrastructure\DTO\EntityAttributes\FieldTypeEnum;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;
use App\Infrastructure\Helper\EntityHelper\EntityFieldsHelper;
use App\Tests\Fixtures\DummyEntity;
use App\Tests\Fixtures\DummyEntityDto;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

final class EntityFieldsHelperTest extends TestCase
{
    public function testSetFieldsFromEntityDataCopiesGetterValuesIntoConfiguredFields(): void
    {
        $entity = (new DummyEntity())
            ->setId(10)
            ->setName('Conta corrente')
            ->setStatus(true);

        $fields = (new FieldsAttribute())
            ->setIdField('id')
            ->setNameField('name')
            ->setStatusField('status');

        EntityFieldsHelper::setFieldsFromEntityData(
            $entity,
            DummyEntity::class,
            $fields,
            $this->createStub(EntityManagerInterface::class),
        );

        self::assertSame(10, $fields->getIdField()?->getValue());
        self::assertSame('Conta corrente', $fields->getNameField()?->getValue());
        self::assertTrue($fields->getStatusField()?->getValue());
    }

    public function testSetFieldsFromEntityDataSkipsFieldsWithoutGetter(): void
    {
        $fields = (new FieldsAttribute())
            ->setTextField('ignored', '');

        EntityFieldsHelper::setFieldsFromEntityData(
            new DummyEntity(),
            DummyEntity::class,
            $fields,
            $this->createStub(EntityManagerInterface::class),
        );

        self::assertNull($fields->getTextField('ignored', FieldTypeEnum::TEXTFIELD)?->getValue());
    }

    public function testSetFieldsFromEntityDataMapsRelationalEntityToIdWhenDeepFetchIsDisabled(): void
    {
        $related = (new DummyEntity())->setId(22)->setName('Relacionada');
        $entity = (new DummyEntity())->setId(10)->setRelated($related);
        $fields = (new FieldsAttribute())
            ->setIdField('id')
            ->setRelationalField('related', DummyEntity::class, 'getRelated');

        EntityFieldsHelper::setFieldsFromEntityData(
            $entity,
            DummyEntity::class,
            $fields,
            $this->entityManagerForDummyDto(),
            DummyEntityDto::class,
        );

        self::assertSame(10, $fields->getIdField()?->getValue());
        self::assertSame(22, $fields->getRelationalField('related')?->getValue());
    }

    public function testSetFieldsFromEntityDataMapsRelationalEntityToDtoWhenDeepFetchIsEnabled(): void
    {
        $related = (new DummyEntity())
            ->setId(22)
            ->setName('Relacionada')
            ->setStatus(true);
        $entity = (new DummyEntity())->setRelated($related);
        $fields = (new FieldsAttribute())
            ->setRelationalField('related', DummyEntity::class, 'getRelated');

        EntityFieldsHelper::setFieldsFromEntityData(
            $entity,
            DummyEntity::class,
            $fields,
            $this->entityManagerForDummyDto(),
            ['related' => DummyEntityDto::class],
            true,
        );

        $value = $fields->getRelationalField('related')?->getValue();

        self::assertInstanceOf(DummyEntityDto::class, $value);
        self::assertSame([
            'id' => 22,
            'name' => 'Relacionada',
            'status' => true,
            'updatedAt' => null,
        ], $value->output());
    }

    public function testSetFieldsFromEntityDataKeepsRelationalEntityWhenNoRelationalDtoIsConfigured(): void
    {
        $related = (new DummyEntity())->setId(22);
        $entity = (new DummyEntity())->setRelated($related);
        $fields = (new FieldsAttribute())
            ->setRelationalField('related', DummyEntity::class, 'getRelated');

        EntityFieldsHelper::setFieldsFromEntityData(
            $entity,
            DummyEntity::class,
            $fields,
            $this->createStub(EntityManagerInterface::class),
            ['other' => DummyEntityDto::class],
        );

        self::assertSame($related, $fields->getRelationalField('related')?->getValue());

        $fieldsWithoutMap = (new FieldsAttribute())
            ->setRelationalField('related', DummyEntity::class, 'getRelated');

        EntityFieldsHelper::setFieldsFromEntityData(
            $entity,
            DummyEntity::class,
            $fieldsWithoutMap,
            $this->createStub(EntityManagerInterface::class),
        );

        self::assertSame($related, $fieldsWithoutMap->getRelationalField('related')?->getValue());
    }

    public function testSetFieldsFromEntityDataFailsWhenConfiguredGetterDoesNotExist(): void
    {
        $fields = (new FieldsAttribute())
            ->setTextField('missing', 'getMissingValue');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Getter getMissingValue não existe');

        EntityFieldsHelper::setFieldsFromEntityData(
            new DummyEntity(),
            DummyEntity::class,
            $fields,
            $this->createStub(EntityManagerInterface::class),
        );
    }

    public function testSetFieldsFromEntityDataRejectsUnexpectedEntityClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Entity must be instance of ' . DummyEntity::class);

        EntityFieldsHelper::setFieldsFromEntityData(
            new \stdClass(),
            DummyEntity::class,
            new FieldsAttribute(),
            $this->createStub(EntityManagerInterface::class),
        );
    }

    private function entityManagerForDummyDto(): EntityManagerInterface
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->method('getRepository')
            ->with(DummyEntity::class)
            ->willReturn($this->createStub(EntityRepository::class));

        return $entityManager;
    }
}
