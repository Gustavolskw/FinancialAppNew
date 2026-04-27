<?php

declare(strict_types=1);

namespace App\Infrastructure\Handler\Action\PrimaryAction\Interface;

use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\Forms\Login\LoginFormDto;
use App\Infrastructure\Handler\Response\JsonResponseHandlerInterface;

interface AccessControlActionInterface
{
    public function login(LoginFormDto $formDto): JsonResponseHandlerInterface;

    public function logoff(): JsonResponseHandlerInterface;

    public static function build(BaseEntityClassInterface $baseEntityClass): self;
}
