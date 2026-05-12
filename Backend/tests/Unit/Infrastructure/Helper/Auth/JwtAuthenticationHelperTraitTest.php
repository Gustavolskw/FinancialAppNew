<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Helper\Auth;

use App\Tests\Fixtures\JwtAuthenticationProbe;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class JwtAuthenticationHelperTraitTest extends TestCase
{
    protected function setUp(): void
    {
        $_ENV['APP_SECRET'] = 'test-secret';
        $_SERVER['APP_SECRET'] = 'test-secret';
    }

    public function testAuthenticateRequestRejectsMissingBearerToken(): void
    {
        $response = (new JwtAuthenticationProbe())
            ->authenticate(Request::create('/wallet', 'GET'))
            ?->output();

        self::assertNotNull($response);
        self::assertSame(401, $response->getStatusCode());
        self::assertSame('Token de autenticação não informado', $this->responseMessage($response));
    }

    public function testAuthenticateRequestRejectsInvalidBearerFormat(): void
    {
        $request = Request::create('/wallet', 'GET');
        $request->headers->set('Authorization', 'Basic abc');

        $response = (new JwtAuthenticationProbe())->authenticate($request)?->output();

        self::assertNotNull($response);
        self::assertSame(401, $response->getStatusCode());
        self::assertSame('Token de autenticação inválido', $this->responseMessage($response));
    }

    public function testAuthenticateRequestAcceptsValidTokenAndStoresPayload(): void
    {
        $probe = new JwtAuthenticationProbe();
        $request = Request::create('/wallet', 'GET');
        $request->headers->set('Authorization', 'Bearer ' . $this->jwt([
            'iss' => 'AppFinancasNew',
            'sub' => 10,
            'email' => 'ana@example.com',
            'exp' => time() + 3600,
        ]));

        self::assertNull($probe->authenticate($request));
        self::assertSame(10, $probe->payload()['sub'] ?? null);
        self::assertSame('ana@example.com', $probe->payload()['email'] ?? null);
    }

    public function testAuthenticateRequestRejectsExpiredToken(): void
    {
        $request = Request::create('/wallet', 'GET');
        $request->headers->set('Authorization', 'Bearer ' . $this->jwt([
            'iss' => 'AppFinancasNew',
            'sub' => 10,
            'email' => 'ana@example.com',
            'exp' => time() - 10,
        ]));

        $response = (new JwtAuthenticationProbe())->authenticate($request)?->output();

        self::assertNotNull($response);
        self::assertSame(401, $response->getStatusCode());
        self::assertSame('Token de autenticação expirado', $this->responseMessage($response));
    }

    public function testAuthenticateRequestRejectsMalformedTokenStructures(): void
    {
        foreach (['abc', 'a.b.c', '****.****.****'] as $token) {
            $request = Request::create('/wallet', 'GET');
            $request->headers->set('Authorization', 'Bearer ' . $token);

            $response = (new JwtAuthenticationProbe())->authenticate($request)?->output();

            self::assertNotNull($response);
            self::assertSame(401, $response->getStatusCode());
            self::assertSame('Token de autenticação inválido', $this->responseMessage($response));
        }
    }

    public function testAuthenticateRequestRejectsInvalidJsonTokenParts(): void
    {
        $request = Request::create('/wallet', 'GET');
        $request->headers->set('Authorization', 'Bearer ' . implode('.', [
            $this->base64UrlEncode('{'),
            $this->base64UrlEncode(json_encode([], JSON_THROW_ON_ERROR)),
            $this->base64UrlEncode('signature'),
        ]));

        $response = (new JwtAuthenticationProbe())->authenticate($request)?->output();

        self::assertNotNull($response);
        self::assertSame(401, $response->getStatusCode());
        self::assertSame('Token de autenticação inválido', $this->responseMessage($response));
    }

    public function testAuthenticateRequestRejectsNonArrayJsonTokenParts(): void
    {
        $request = Request::create('/wallet', 'GET');
        $request->headers->set('Authorization', 'Bearer ' . implode('.', [
            $this->base64UrlEncode(json_encode('header', JSON_THROW_ON_ERROR)),
            $this->base64UrlEncode(json_encode([], JSON_THROW_ON_ERROR)),
            $this->base64UrlEncode('signature'),
        ]));

        $response = (new JwtAuthenticationProbe())->authenticate($request)?->output();

        self::assertNotNull($response);
        self::assertSame(401, $response->getStatusCode());
        self::assertSame('Token de autenticação inválido', $this->responseMessage($response));
    }

    public function testAuthenticateRequestRejectsInvalidTokenClaimsAndSignature(): void
    {
        $payload = [
            'iss' => 'AppFinancasNew',
            'sub' => 10,
            'email' => 'ana@example.com',
            'exp' => time() + 3600,
        ];

        $tokens = [
            $this->jwtWith($payload, ['typ' => 'JWT', 'alg' => 'none']),
            $this->jwtWith([...$payload, 'iss' => 'OutroIssuer']),
            $this->jwtWith(['iss' => 'AppFinancasNew', 'exp' => time() + 3600]),
            $this->jwtWith([...$payload, 'exp' => 'amanha']),
            $this->jwtWith($payload, secret: 'wrong-secret'),
        ];

        foreach ($tokens as $token) {
            $request = Request::create('/wallet', 'GET');
            $request->headers->set('Authorization', 'Bearer ' . $token);

            $response = (new JwtAuthenticationProbe())->authenticate($request)?->output();

            self::assertNotNull($response);
            self::assertSame(401, $response->getStatusCode());
            self::assertContains(
                $this->responseMessage($response),
                ['Token de autenticação inválido', 'Token de autenticação expirado']
            );
        }
    }

    public function testAuthenticateRequestReturnsServerErrorWhenSecretIsMissing(): void
    {
        $token = $this->jwt([
            'iss' => 'AppFinancasNew',
            'sub' => 10,
            'email' => 'ana@example.com',
            'exp' => time() + 3600,
        ]);
        unset($_ENV['APP_SECRET'], $_SERVER['APP_SECRET']);

        $request = Request::create('/wallet', 'GET');
        $request->headers->set('Authorization', 'Bearer ' . $token);

        $response = (new JwtAuthenticationProbe())->authenticate($request)?->output();

        self::assertNotNull($response);
        self::assertSame(500, $response->getStatusCode());
        self::assertSame(
            'APP_SECRET precisa estar configurado para validar autenticação',
            $this->responseMessage($response)
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function jwt(array $payload): string
    {
        return $this->jwtWith($payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $header
     */
    private function jwtWith(
        array $payload,
        array $header = ['typ' => 'JWT', 'alg' => 'HS256'],
        string $secret = 'test-secret',
    ): string {
        $header = $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR));
        $body = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = hash_hmac('sha256', $header . '.' . $body, $secret, true);

        return $header . '.' . $body . '.' . $this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function responseMessage(\Symfony\Component\HttpFoundation\Response $response): string
    {
        return json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR)['message'];
    }
}
