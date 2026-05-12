<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\DTO\Response;

use App\Infrastructure\DTO\Response\ResponseBuilder;
use App\Infrastructure\Helper\Interface\EntityClassCollection;
use PHPUnit\Framework\TestCase;

final class ResponseBuilderTest extends TestCase
{
    public function testJsonSerializeUsesStandardResponseShapeAndNamedData(): void
    {
        $collection = new class implements EntityClassCollection {
            public function output(): array
            {
                return ['id' => 10, 'name' => 'Carteira'];
            }
        };

        $response = ResponseBuilder::build('Criado', 201)
            ->addData('wallet', $collection);

        self::assertSame([
            'message' => 'Criado',
            'statusCode' => 201,
            'data' => [
                'wallet' => ['id' => 10, 'name' => 'Carteira'],
            ],
        ], $response->jsonSerialize());
    }
}
