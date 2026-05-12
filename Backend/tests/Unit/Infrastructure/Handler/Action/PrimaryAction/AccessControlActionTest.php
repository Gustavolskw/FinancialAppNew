<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Handler\Action\PrimaryAction;

use App\Entity\User;
use App\Infrastructure\DTO\EntityAttributes\Enum\RolesEnum;
use App\Infrastructure\DTO\EntityDto\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\Forms\Login\LoginFormDto;
use App\Infrastructure\Handler\Action\PrimaryAction\AccessControlAction;
use App\Infrastructure\Helper\PasswordHashHelperTrait;
use App\Tests\Fixtures\JwtAuthenticationProbe;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class AccessControlActionTest extends TestCase
{
    use PasswordHashHelperTrait;

    protected function setUp(): void
    {
        $_ENV['APP_SECRET'] = 'test-secret';
        $_SERVER['APP_SECRET'] = 'test-secret';
    }

    public function testLoginRejectsInvalidCredentials(): void
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::once())
            ->method('findOneBy')
            ->with(['email' => 'ana@example.com'])
            ->willReturn(null);

        $response = AccessControlAction::build($this->baseEntity($repository))
            ->login(new LoginFormDto('ana@example.com', 'Senha@123'))
            ->output();

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('Credenciais inválidas', json_decode((string) $response->getContent(), true)['message']);
    }

    public function testLoginRejectsMissingEmailOrPasswordBeforeRepositoryLookup(): void
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::never())->method('findOneBy');

        $missingEmail = AccessControlAction::build($this->baseEntity($repository))
            ->login(new LoginFormDto(' ', 'Senha@123'))
            ->output();

        self::assertSame(400, $missingEmail->getStatusCode());
        self::assertSame('Campo email é obrigatório', json_decode((string) $missingEmail->getContent(), true)['message']);

        $missingPassword = AccessControlAction::build($this->baseEntity($repository))
            ->login(new LoginFormDto('ana@example.com'))
            ->output();

        self::assertSame(400, $missingPassword->getStatusCode());
        self::assertSame('Campo password é obrigatório', json_decode((string) $missingPassword->getContent(), true)['message']);
    }

    public function testLoginRejectsInactiveUser(): void
    {
        $repository = $this->createStub(EntityRepository::class);
        $repository->method('findOneBy')->willReturn($this->user(status: false));

        $response = AccessControlAction::build($this->baseEntity($repository))
            ->login(new LoginFormDto('ana@example.com', 'Senha@123'))
            ->output();

        self::assertSame(403, $response->getStatusCode());
        self::assertSame('Usuário inativo', json_decode((string) $response->getContent(), true)['message']);
    }

    public function testLoginReturnsJwtAndUserDataForValidCredentials(): void
    {
        $repository = $this->createStub(EntityRepository::class);
        $repository->method('findOneBy')->willReturn($this->user());

        $response = AccessControlAction::build($this->baseEntity($repository))
            ->login(new LoginFormDto('ana@example.com', 'Senha@123'))
            ->output();
        $payload = json_decode((string) $response->getContent(), true);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Login realizado com sucesso', $payload['message']);
        self::assertArrayHasKey('token', $payload['data']['auth']);
        self::assertSame('Bearer', $payload['data']['auth']['tokenType']);
        self::assertSame('Admin', $payload['data']['auth']['user']['role']);

        $request = Request::create('/wallet', 'GET');
        $request->headers->set('Authorization', 'Bearer ' . $payload['data']['auth']['token']);

        $probe = new JwtAuthenticationProbe();
        self::assertNull($probe->authenticate($request));
        self::assertSame('ana@example.com', $probe->payload()['email'] ?? null);
    }

    public function testLoginReturnsNullRoleWhenUserHasNoRole(): void
    {
        $repository = $this->createStub(EntityRepository::class);
        $repository->method('findOneBy')->willReturn($this->user(withRole: false));

        $response = AccessControlAction::build($this->baseEntity($repository))
            ->login(new LoginFormDto('ana@example.com', 'Senha@123'))
            ->output();
        $payload = json_decode((string) $response->getContent(), true);

        self::assertSame(200, $response->getStatusCode());
        self::assertNull($payload['data']['auth']['user']['role']);
    }

    public function testLoginReturnsServerErrorWhenSecretIsMissing(): void
    {
        unset($_ENV['APP_SECRET'], $_SERVER['APP_SECRET']);
        $repository = $this->createStub(EntityRepository::class);
        $repository->method('findOneBy')->willReturn($this->user());

        $response = AccessControlAction::build($this->baseEntity($repository))
            ->login(new LoginFormDto('ana@example.com', 'Senha@123'))
            ->output();

        self::assertSame(500, $response->getStatusCode());
        self::assertSame(
            'APP_SECRET precisa estar configurado para gerar token de login',
            json_decode((string) $response->getContent(), true)['message']
        );
    }

    public function testLoginReturnsServerErrorWhenJwtPayloadCannotBeEncoded(): void
    {
        $repository = $this->createStub(EntityRepository::class);
        $repository->method('findOneBy')->willReturn($this->user(email: "\xB1\x31"));

        $response = AccessControlAction::build($this->baseEntity($repository))
            ->login(new LoginFormDto('ana@example.com', 'Senha@123'))
            ->output();

        self::assertSame(500, $response->getStatusCode());
        self::assertSame(
            'Não foi possível gerar o token de autenticação',
            json_decode((string) $response->getContent(), true)['message']
        );
    }

    public function testLogoffReturnsSuccessResponse(): void
    {
        $response = AccessControlAction::build($this->baseEntity($this->createStub(EntityRepository::class)))
            ->logoff()
            ->output();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(
            'Logoff realizado com sucesso',
            json_decode((string) $response->getContent(), true)['message']
        );
    }

    private function baseEntity(EntityRepository $repository): BaseEntityClassInterface
    {
        $baseEntity = $this->createStub(BaseEntityClassInterface::class);
        $baseEntity->method('getRepository')->willReturn($repository);

        return $baseEntity;
    }

    private function user(
        bool $status = true,
        ?int $role = null,
        string $email = 'ana@example.com',
        bool $withRole = true,
    ): User
    {
        $user = (new User())
            ->setId('10')
            ->setName('Ana')
            ->setEmail($email)
            ->setPassword($this->hashPassword('Senha@123'))
            ->setStatus($status);

        if ($withRole) {
            $user->setRole($role ?? RolesEnum::ADM->value());
        }

        return $user;
    }
}
