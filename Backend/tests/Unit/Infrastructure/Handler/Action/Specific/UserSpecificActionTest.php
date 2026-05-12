<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Handler\Action\Specific;

use App\Entity\User;
use App\Entity\Wallet;
use App\Infrastructure\DTO\EntityAttributes\Enum\RolesEnum;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;
use App\Infrastructure\Handler\Action\Specific\UserSpecificAction;
use App\Tests\Fixtures\DummyEntityDto;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

final class UserSpecificActionTest extends TestCase
{
    public function testPreSaveHashesPasswordAndSetsDefaultUserRole(): void
    {
        $fields = (new FieldsAttribute())
            ->setPassword('password')
            ->setEnumField('role', 'getRole', RolesEnum::class);

        $fields->getPasswordField('password')?->setValue('Senha@123');

        $dto = new DummyEntityDto($fields);

        self::assertTrue((new UserSpecificAction($dto))->preSave($dto));

        $hash = $fields->getPasswordField('password')?->getValue();
        self::assertIsString($hash);
        self::assertNotSame('Senha@123', $hash);
        self::assertTrue(password_verify('Senha@123', $hash));
        self::assertSame(RolesEnum::USER->value(), $fields->getEnumField('role')?->getRawValue());
    }

    public function testPreUpdateHashesPasswordWithoutForcingRole(): void
    {
        $fields = (new FieldsAttribute())
            ->setPassword('password')
            ->setEnumField('role', 'getRole', RolesEnum::class);

        $fields->getPasswordField('password')?->setValue('NovaSenha@123');

        $dto = new DummyEntityDto($fields);

        self::assertTrue((new UserSpecificAction($dto))->preUpdate($dto));

        self::assertTrue(password_verify('NovaSenha@123', $fields->getPasswordField('password')?->getValue()));
        self::assertNull($fields->getEnumField('role')?->getRawValue());
    }

    public function testPreSaveKeepsExistingRoleAndIgnoresMissingPasswordField(): void
    {
        $fields = (new FieldsAttribute())
            ->setEnumField('role', 'getRole', RolesEnum::class);
        $fields->getEnumField('role')?->setValue(RolesEnum::ADM->value());

        $dto = new DummyEntityDto($fields);

        self::assertTrue((new UserSpecificAction($dto))->preSave($dto));
        self::assertSame(RolesEnum::ADM->value(), $fields->getEnumField('role')?->getRawValue());
    }

    public function testAfterActionStopsWhenCreatedUserIdIsUnavailable(): void
    {
        $dto = new DummyEntityDto(
            (new FieldsAttribute())->setIdField('id'),
            $this->entityManager($this->createStub(EntityRepository::class))
        );

        self::assertFalse((new UserSpecificAction($dto))->afterAction($dto));
    }

    public function testAfterActionStopsWhenCreatedUserCannotBeFound(): void
    {
        $fields = (new FieldsAttribute())->setIdField('id');
        $fields->getIdField()?->setValue(10);
        $userRepository = $this->createMock(EntityRepository::class);
        $userRepository->method('find')->with(10)->willReturn(null);
        $dto = new DummyEntityDto($fields, $this->entityManager($userRepository));

        self::assertFalse((new UserSpecificAction($dto))->afterAction($dto));
    }

    public function testAfterActionDoesNothingWhenUserAlreadyHasWallet(): void
    {
        $wallet = new Wallet();
        $user = $this->user()->setUserWallet($wallet);
        $fields = (new FieldsAttribute())->setIdField('id');
        $fields->getIdField()?->setValue(10);
        $userRepository = $this->createMock(EntityRepository::class);
        $userRepository->method('find')->with(10)->willReturn($user);
        $entityManager = $this->entityManager($userRepository, withExpectations: true);
        $entityManager->expects(self::never())->method('persist');

        $dto = new DummyEntityDto($fields, $entityManager);

        self::assertTrue((new UserSpecificAction($dto))->afterAction($dto));
    }

    public function testAfterActionDoesNothingWhenWalletAlreadyExistsForUser(): void
    {
        $user = $this->user();
        $fields = (new FieldsAttribute())->setIdField('id');
        $fields->getIdField()?->setValue(10);
        $userRepository = $this->createMock(EntityRepository::class);
        $userRepository->method('find')->with(10)->willReturn($user);
        $walletRepository = $this->createMock(EntityRepository::class);
        $walletRepository->method('findOneBy')->with(['walletUser' => $user])->willReturn(new Wallet());
        $entityManager = $this->entityManager($userRepository, $walletRepository, withExpectations: true);
        $entityManager->expects(self::never())->method('persist');

        $dto = new DummyEntityDto($fields, $entityManager);

        self::assertTrue((new UserSpecificAction($dto))->afterAction($dto));
    }

    public function testAfterActionCreatesDefaultWalletForNewUser(): void
    {
        $user = $this->user(str_repeat('A', 80));
        $fields = (new FieldsAttribute())->setIdField('id');
        $fields->getIdField()?->setValue(10);
        $userRepository = $this->createMock(EntityRepository::class);
        $userRepository->method('find')->with(10)->willReturn($user);
        $walletRepository = $this->createMock(EntityRepository::class);
        $walletRepository->method('findOneBy')->with(['walletUser' => $user])->willReturn(null);
        $entityManager = $this->entityManager($userRepository, $walletRepository, withExpectations: true);
        $entityManager->expects(self::once())
            ->method('persist')
            ->with(self::callback(function (Wallet $wallet) use ($user): bool {
                return $wallet->isStatus() === true
                    && $wallet->getWalletUser() === $user
                    && mb_strlen((string) $wallet->getTitle()) <= 50
                    && mb_strlen((string) $wallet->getDescription()) <= 255;
            }));
        $entityManager->expects(self::once())->method('flush');

        $dto = new DummyEntityDto($fields, $entityManager);

        self::assertTrue((new UserSpecificAction($dto))->afterAction($dto));
        self::assertInstanceOf(Wallet::class, $user->getUserWallet());
    }

    private function user(string $name = 'Ana'): User
    {
        return (new User())
            ->setId('10')
            ->setName($name)
            ->setEmail('ana@example.com')
            ->setPassword('hash')
            ->setStatus(true)
            ->setRole(RolesEnum::USER->value());
    }

    private function entityManager(
        EntityRepository $userRepository,
        ?EntityRepository $walletRepository = null,
        bool $withExpectations = false,
    ): EntityManagerInterface {
        $walletRepository ??= $this->createStub(EntityRepository::class);
        $entityManager = $withExpectations
            ? $this->createMock(EntityManagerInterface::class)
            : $this->createStub(EntityManagerInterface::class);
        $entityManager
            ->method('getRepository')
            ->willReturnMap([
                [User::class, $userRepository],
                [Wallet::class, $walletRepository],
            ]);

        return $entityManager;
    }
}
