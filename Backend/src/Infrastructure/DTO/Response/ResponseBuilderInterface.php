<?php

namespace App\Infrastructure\DTO\Response;

use App\Infrastructure\Helper\Interface\EntityClassCollection;

interface ResponseBuilderInterface extends \JsonSerializable
{
    public static function build(string $responseMessage, int $statusCode): self;
    public function addData(string $dataTitle, EntityClassCollection $data): self;

}
