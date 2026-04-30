<?php

declare(strict_types=1);

namespace App\Infrastructure\Handler\Action\PrimaryAction;

use App\Entity\User;
use App\Infrastructure\DTO\EntityAttributes\Enum\RolesEnum;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\Forms\Login\LoginFormDto;
use App\Infrastructure\DTO\Response\AuthSessionDataDto;
use App\Infrastructure\DTO\Response\ResponseBuilder;
use App\Infrastructure\Handler\Action\PrimaryAction\Interface\AccessControlActionInterface;
use App\Infrastructure\Handler\Response\JsonResponseHandler;
use App\Infrastructure\Handler\Response\JsonResponseHandlerInterface;
use App\Infrastructure\Helper\PasswordHashHelperTrait;

final class AccessControlAction implements AccessControlActionInterface
{
    use PasswordHashHelperTrait;

    private const int TOKEN_TTL_SECONDS = 36000;

    public function __construct(
        private readonly BaseEntityClassInterface $baseEntityClass,
    ) {
    }

    public function login(LoginFormDto $formDto): JsonResponseHandlerInterface
    {
        try {
            $email = $this->requiredString($formDto->email, 'email');
            $password = $this->requiredString($formDto->password, 'password');

            $user = $this->baseEntityClass->getRepository()->findOneBy(['email' => $email]);

            if (!$user instanceof User || !$this->passwordMatches($password, (string) $user->getPassword())) {
                return $this->response('Credenciais inválidas', 401);
            }

            if ($user->isStatus() === false) {
                return $this->response('Usuário inativo', 403);
            }

            $expiresAt = new \DateTimeImmutable(sprintf('+%d seconds', self::TOKEN_TTL_SECONDS));
            $token = $this->createJwt($user, $expiresAt);

            $response = ResponseBuilder::build('Login realizado com sucesso', 200)
                ->addData('auth', AuthSessionDataDto::make(
                    $token,
                    self::TOKEN_TTL_SECONDS,
                    $expiresAt->format(\DateTimeInterface::ATOM),
                    $this->userData($user),
                ));

            return JsonResponseHandler::create($response);
        } catch (\InvalidArgumentException $exception) {
            return $this->response($exception->getMessage(), 400);
        } catch (\RuntimeException $exception) {
            return $this->response($exception->getMessage(), 500);
        } catch (\JsonException $exception) {
            return $this->response('Não foi possível gerar o token de autenticação', 500);
        }
    }

    public function logoff(): JsonResponseHandlerInterface
    {
        return $this->response('Logoff realizado com sucesso', 200);
    }

    public static function build(BaseEntityClassInterface $baseEntityClass): AccessControlActionInterface
    {
        return new self($baseEntityClass);
    }

    private function requiredString(?string $value, string $field): string
    {
        if ($value === null || trim($value) === '') {
            throw new \InvalidArgumentException("Campo {$field} é obrigatório");
        }

        return trim($value);
    }

    /**
     * @throws \JsonException
     */
    private function createJwt(User $user, \DateTimeImmutable $expiresAt): string
    {
        $issuedAt = new \DateTimeImmutable();
        $payload = [
            'iss' => 'AppFinancasNew',
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'iat' => $issuedAt->getTimestamp(),
            'exp' => $expiresAt->getTimestamp(),
            'jti' => bin2hex(random_bytes(16)),
        ];

        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];

        $encodedHeader = $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR));
        $encodedPayload = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, $this->tokenSecret(), true);

        return $encodedHeader . '.' . $encodedPayload . '.' . $this->base64UrlEncode($signature);
    }

    private function tokenSecret(): string
    {
        $secret = (string) ($_ENV['APP_SECRET'] ?? $_SERVER['APP_SECRET'] ?? '');

        if (trim($secret) === '') {
            throw new \RuntimeException('APP_SECRET precisa estar configurado para gerar token de login');
        }

        return $secret;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * @return array<string, mixed>
     */
    private function userData(User $user): array
    {
        return [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'role' => $user->getRole() !== null ? RolesEnum::match($user->getRole())->name() : null,
            'status' => $user->isStatus(),
        ];
    }

    private function response(string $message, int $statusCode): JsonResponseHandlerInterface
    {
        return JsonResponseHandler::create(ResponseBuilder::build($message, $statusCode));
    }
}
