<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Handler\Action\Manager;

use App\Entity\User;
use App\Entity\Wallet;
use App\Infrastructure\DTO\EntityAttributes\Enum\RolesEnum;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\Forms\StatusFormDto;
use App\Infrastructure\Handler\Action\Manager\ActionManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class ActionManagerTest extends TestCase
{
    protected function setUp(): void
    {
        $_ENV['APP_SECRET'] = 'test-secret';
        $_SERVER['APP_SECRET'] = 'test-secret';
    }

    public function testPublicUserCreateRejectsRolePayloadBeforeAuthentication(): void
    {
        $baseEntity = $this->createStub(BaseEntityClassInterface::class);
        $baseEntity->method('getEntityClass')->willReturn(User::class);

        $request = Request::create(
            '/user',
            Request::METHOD_POST,
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'Ana', 'role' => 2], JSON_THROW_ON_ERROR),
        );
        $request->attributes->set('_route', 'userPost');

        $response = (new ActionManager())
            ->handle($baseEntity, $request)
            ->output();

        self::assertSame(403, $response->getStatusCode());
        self::assertSame(
            'Perfil de acesso não pode ser enviado na criação normal de usuário',
            json_decode((string) $response->getContent(), true)['message'],
        );
    }

    public function testPublicUserCreateWithoutPayloadRoleDelegatesToSaveValidation(): void
    {
        $baseEntity = $this->createStub(BaseEntityClassInterface::class);
        $baseEntity->method('getEntityClass')->willReturn(User::class);

        $request = Request::create('/user', Request::METHOD_POST);
        $request->attributes->set('_route', 'userPost');

        $response = (new ActionManager())
            ->handle($baseEntity, $request)
            ->output();

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('Dados obrigatórios para cadastro', json_decode((string) $response->getContent(), true)['message']);
    }

    public function testProtectedCrudRouteRejectsMissingAuthenticationToken(): void
    {
        $baseEntity = $this->createStub(BaseEntityClassInterface::class);
        $baseEntity->method('getEntityClass')->willReturn(Wallet::class);

        $response = (new ActionManager())
            ->handle($baseEntity, Request::create('/wallet', Request::METHOD_GET))
            ->output();

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('Token de autenticação não informado', json_decode((string) $response->getContent(), true)['message']);
    }

    public function testStatusRouteRejectsMissingAuthenticationToken(): void
    {
        $baseEntity = $this->createStub(BaseEntityClassInterface::class);
        $baseEntity->method('getEntityClass')->willReturn(Wallet::class);

        $response = (new ActionManager())
            ->handleStatus($baseEntity, Request::create('/wallet/10/status', Request::METHOD_PATCH), 10, new StatusFormDto(true))
            ->output();

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('Token de autenticação não informado', json_decode((string) $response->getContent(), true)['message']);
    }

    public function testStatusRouteValidatesIdAndStatusAfterAuthentication(): void
    {
        $baseEntity = $this->baseEntityWithAuthenticatedAdmin();

        $request = Request::create('/wallet/0/status', Request::METHOD_PATCH);
        $request->headers->set('Authorization', 'Bearer ' . $this->jwt());

        $invalidId = (new ActionManager())
            ->handleStatus($baseEntity, $request, 0, new StatusFormDto(true))
            ->output();

        self::assertSame(400, $invalidId->getStatusCode());
        self::assertSame('ID inválido para atualização de status', json_decode((string) $invalidId->getContent(), true)['message']);

        $missingStatus = (new ActionManager())
            ->handleStatus($baseEntity, $request, 10, new StatusFormDto())
            ->output();

        self::assertSame(400, $missingStatus->getStatusCode());
        self::assertSame('Status é obrigatório', json_decode((string) $missingStatus->getContent(), true)['message']);
    }

    private function baseEntityWithAuthenticatedAdmin(): BaseEntityClassInterface
    {
        $user = (new User())
            ->setId('10')
            ->setName('Admin')
            ->setEmail('admin@example.com')
            ->setPassword('hash')
            ->setStatus(true)
            ->setRole(RolesEnum::ADM->value());
        $userRepository = $this->createStub(EntityRepository::class);
        $userRepository->method('find')->willReturn($user);
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($userRepository);

        $baseEntity = $this->createStub(BaseEntityClassInterface::class);
        $baseEntity->method('getEntityClass')->willReturn(Wallet::class);
        $baseEntity->method('getEntityManager')->willReturn($entityManager);

        return $baseEntity;
    }

    private function jwt(): string
    {
        $header = $this->base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256'], JSON_THROW_ON_ERROR));
        $payload = $this->base64UrlEncode(json_encode([
            'iss' => 'AppFinancasNew',
            'sub' => 10,
            'email' => 'admin@example.com',
            'exp' => time() + 3600,
        ], JSON_THROW_ON_ERROR));
        $signature = hash_hmac('sha256', $header . '.' . $payload, 'test-secret', true);

        return $header . '.' . $payload . '.' . $this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
