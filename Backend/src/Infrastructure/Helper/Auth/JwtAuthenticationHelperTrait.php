<?php

declare(strict_types=1);

namespace App\Infrastructure\Helper\Auth;

use App\Infrastructure\DTO\Response\ResponseBuilder;
use App\Infrastructure\Handler\Response\JsonResponseHandler;
use App\Infrastructure\Handler\Response\JsonResponseHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait JwtAuthenticationHelperTrait
{
    private const string JWT_ISSUER = 'AppFinancasNew';
    /** @var array<string, mixed>|null */
    private ?array $authenticatedJwtPayload = null;

    protected function authenticateRequest(Request $request): ?JsonResponseHandlerInterface
    {
        $this->authenticatedJwtPayload = null;
        $authorization = trim((string) $request->headers->get('Authorization', ''));

        if ($authorization === '') {
            return $this->authenticationResponse('Token de autenticação não informado', Response::HTTP_UNAUTHORIZED);
        }

        if (!preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
            return $this->authenticationResponse('Token de autenticação inválido', Response::HTTP_UNAUTHORIZED);
        }

        try {
            $this->authenticatedJwtPayload = $this->validateJwt($matches[1]);

            return null;
        } catch (\InvalidArgumentException $exception) {
            return $this->authenticationResponse($exception->getMessage(), Response::HTTP_UNAUTHORIZED);
        } catch (\RuntimeException $exception) {
            return $this->authenticationResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\JsonException) {
            return $this->authenticationResponse('Token de autenticação inválido', Response::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \JsonException
     */
    private function validateJwt(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new \InvalidArgumentException('Token de autenticação inválido');
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
        $header = $this->decodeJwtJsonPart($encodedHeader);
        $payload = $this->decodeJwtJsonPart($encodedPayload);
        $signature = $this->base64UrlDecode($encodedSignature);

        if (($header['alg'] ?? null) !== 'HS256') {
            throw new \InvalidArgumentException('Token de autenticação inválido');
        }

        if (($payload['iss'] ?? null) !== self::JWT_ISSUER) {
            throw new \InvalidArgumentException('Token de autenticação inválido');
        }

        if (!isset($payload['sub'], $payload['email'], $payload['exp'])) {
            throw new \InvalidArgumentException('Token de autenticação inválido');
        }

        if (!is_numeric($payload['exp']) || time() > (int) $payload['exp']) {
            throw new \InvalidArgumentException('Token de autenticação expirado');
        }

        $expectedSignature = hash_hmac(
            'sha256',
            $encodedHeader . '.' . $encodedPayload,
            $this->jwtSecret(),
            true
        );

        if (!hash_equals($expectedSignature, $signature)) {
            throw new \InvalidArgumentException('Token de autenticação inválido');
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \JsonException
     */
    private function decodeJwtJsonPart(string $encodedPart): array
    {
        $decoded = $this->base64UrlDecode($encodedPart);
        $data = json_decode($decoded, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($data)) {
            throw new \InvalidArgumentException('Token de autenticação inválido');
        }

        return $data;
    }

    private function jwtSecret(): string
    {
        $secret = (string) ($_ENV['APP_SECRET'] ?? $_SERVER['APP_SECRET'] ?? '');

        if (trim($secret) === '') {
            throw new \RuntimeException('APP_SECRET precisa estar configurado para validar autenticação');
        }

        return $secret;
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;

        if ($remainder === 1) {
            throw new \InvalidArgumentException('Token de autenticação inválido');
        }

        if ($remainder > 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($data, '-_', '+/'), true);

        if ($decoded === false) {
            throw new \InvalidArgumentException('Token de autenticação inválido');
        }

        return $decoded;
    }

    private function authenticationResponse(string $message, int $statusCode): JsonResponseHandlerInterface
    {
        return JsonResponseHandler::create(ResponseBuilder::build($message, $statusCode));
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function authenticatedJwtPayload(): ?array
    {
        return $this->authenticatedJwtPayload;
    }
}
