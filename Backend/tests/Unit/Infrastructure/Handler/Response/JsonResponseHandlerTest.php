<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Handler\Response;

use App\Infrastructure\DTO\Response\ResponseBuilder;
use App\Infrastructure\Handler\Response\JsonResponseHandler;
use PHPUnit\Framework\TestCase;

final class JsonResponseHandlerTest extends TestCase
{
    public function testOutputUsesSerializedStatusCodeAsHttpStatus(): void
    {
        $response = JsonResponseHandler::create(ResponseBuilder::build('Sem permissão', 403))
            ->output();

        self::assertSame(403, $response->getStatusCode());
        self::assertSame([
            'message' => 'Sem permissão',
            'statusCode' => 403,
            'data' => [],
        ], json_decode((string) $response->getContent(), true));
    }
}
