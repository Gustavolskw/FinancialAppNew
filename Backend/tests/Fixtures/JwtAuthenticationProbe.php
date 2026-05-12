<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Infrastructure\Handler\Response\JsonResponseHandlerInterface;
use App\Infrastructure\Helper\Auth\JwtAuthenticationHelperTrait;
use Symfony\Component\HttpFoundation\Request;

final class JwtAuthenticationProbe
{
    use JwtAuthenticationHelperTrait;

    public function authenticate(Request $request): ?JsonResponseHandlerInterface
    {
        return $this->authenticateRequest($request);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function payload(): ?array
    {
        return $this->authenticatedJwtPayload();
    }
}
