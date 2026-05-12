<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Helper\Response;

use App\Tests\Fixtures\DummyEntityDto;
use App\Infrastructure\Helper\Response\EntityBuilder;
use App\Infrastructure\Helper\Response\EntityListBuilder;
use PHPUnit\Framework\TestCase;

final class EntityBuilderTest extends TestCase
{
    public function testEntityBuilderReturnsSingleDtoOutput(): void
    {
        $dto = new DummyEntityDto(DummyEntityDto::defaultFields());
        $dto->getFields()->getIdField()?->setValue(1);
        $dto->getFields()->getNameField()?->setValue('Principal');

        self::assertSame([
            'id' => 1,
            'name' => 'Principal',
            'status' => null,
            'updatedAt' => null,
        ], EntityBuilder::factory($dto)->output());
    }

    public function testEntityListBuilderReturnsListOfDtoOutputs(): void
    {
        $first = new DummyEntityDto(DummyEntityDto::defaultFields());
        $first->getFields()->getIdField()?->setValue(1);
        $first->getFields()->getNameField()?->setValue('Principal');

        $second = new DummyEntityDto(DummyEntityDto::defaultFields());
        $second->getFields()->getIdField()?->setValue(2);
        $second->getFields()->getNameField()?->setValue('Reserva');

        self::assertSame([
            [
                'id' => 1,
                'name' => 'Principal',
                'status' => null,
                'updatedAt' => null,
            ],
            [
                'id' => 2,
                'name' => 'Reserva',
                'status' => null,
                'updatedAt' => null,
            ],
        ], EntityListBuilder::factory([$first, $second])->output());
    }
}
