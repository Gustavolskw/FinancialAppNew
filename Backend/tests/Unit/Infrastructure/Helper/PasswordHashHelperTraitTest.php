<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Helper;

use App\Infrastructure\Helper\PasswordHashHelperTrait;
use PHPUnit\Framework\TestCase;

final class PasswordHashHelperTraitTest extends TestCase
{
    public function testHashPasswordAndPasswordMatchesUsePhpPasswordApi(): void
    {
        $helper = new class {
            use PasswordHashHelperTrait;
        };

        $hash = $helper->hashPassword('Senha@123');

        self::assertNotSame('Senha@123', $hash);
        self::assertTrue($helper->passwordMatches('Senha@123', $hash));
        self::assertFalse($helper->passwordMatches('SenhaErrada@123', $hash));
    }
}
