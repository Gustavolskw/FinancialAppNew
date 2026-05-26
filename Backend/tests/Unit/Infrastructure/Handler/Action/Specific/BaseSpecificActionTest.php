<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Handler\Action\Specific;

use App\Entity\Wallet;
use App\Infrastructure\DTO\EntityAttributes\FieldTypeEnum;
use App\Infrastructure\DTO\EntityAttributes\Fields\BasicFieldDto;
use App\Infrastructure\DTO\EntityAttributes\Fields\RelationalAttributeDto;
use App\Infrastructure\DTO\EntityAttributes\FieldsAttribute;
use App\Infrastructure\DTO\Configuration\Interface\BaseEntityClassInterface;
use App\Infrastructure\Handler\Action\Specific\BaseSpecificAction;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

final class BaseSpecificActionTest extends TestCase
{
    public function testDefaultHooksAllowActionFlow(): void
    {
        $dto = $this->dto(new FieldsAttribute());
        $action = new BaseSpecificAction($dto);

        self::assertTrue($action->preActionValidation($dto));
        self::assertTrue($action->preSave($dto));
        self::assertTrue($action->preUpdate($dto));
        self::assertNull($action->specificAction($dto));
        self::assertTrue($action->afterAction($dto));
        self::assertTrue($action->beforeChangeStatus($dto));
        self::assertTrue($action->afterChangeStatus($dto));
        self::assertTrue($action->beforeDelete($dto));
        self::assertTrue($action->afterDelete($dto));
        self::assertTrue($action->beforeUpdate($dto));
        self::assertTrue($action->afterUpdate($dto));
    }

    public function testPreActionValidationSkipsEmptyInvalidShapeAndObjectRelations(): void
    {
        $wallet = new Wallet();
        $fields = (new FieldsAttribute())
            ->setNameField('name')
            ->setRelationalField('emptyWallet', Wallet::class, 'getEmptyWallet')
            ->setRelationalField('objectWallet', Wallet::class, 'getObjectWallet')
            ->setRelationalField('stringWallet', Wallet::class, 'getStringWallet');

        $fields->getNameField()?->setValue('Carteira');
        $fields->getRelationalField('objectWallet')?->setValue($wallet);
        $fields->getRelationalField('stringWallet')?->setValue('abc');
        $fields->getFields()->set(
            'genericRelation',
            BasicFieldDto::factory('genericRelation', FieldTypeEnum::RELATIONALFIELD, 'getGenericRelation')
                ->setValidation(false)
                ->setValue(10)
        );

        $dto = $this->dto($fields);

        self::assertTrue((new BaseSpecificAction($dto))->preActionValidation($dto));
    }

    public function testPreActionValidationRejectsMissingRelationClass(): void
    {
        $fields = new FieldsAttribute();
        $fields->getFields()->set(
            'wallet',
            RelationalAttributeDto::factory('wallet', FieldTypeEnum::RELATIONALFIELD, 'getWallet')
                ->setValidation(false)
                ->setValue(10)
        );
        $dto = $this->dto($fields);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Classe relacional não configurada para campo wallet');

        (new BaseSpecificAction($dto))->preActionValidation($dto);
    }

    public function testPreActionValidationRejectsUnknownRelatedRecord(): void
    {
        $fields = (new FieldsAttribute())
            ->setRelationalField('wallet', Wallet::class, 'getWallet');
        $fields->getRelationalField('wallet')?->setValue(99);
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(99)->willReturn(null);

        $dto = $this->dto($fields, $repository);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Registro relacionado não encontrado para campo wallet');

        (new BaseSpecificAction($dto))->preActionValidation($dto);
    }

    public function testPreActionValidationAcceptsExistingRelatedRecord(): void
    {
        $wallet = new Wallet();
        $fields = (new FieldsAttribute())
            ->setRelationalField('wallet', Wallet::class, 'getWallet');
        $fields->getRelationalField('wallet')?->setValue(99);
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(99)->willReturn($wallet);

        $dto = $this->dto($fields, $repository);

        self::assertTrue((new BaseSpecificAction($dto))->preActionValidation($dto));
    }

    private function dto(FieldsAttribute $fields, ?EntityRepository $repository = null): BaseEntityClassInterface
    {
        if ($repository instanceof EntityRepository) {
            $entityManager = $this->createMock(EntityManagerInterface::class);
            $entityManager
                ->method('getRepository')
                ->with(Wallet::class)
                ->willReturn($repository);
        } else {
            $entityManager = $this->createStub(EntityManagerInterface::class);
        }

        $dto = $this->createStub(BaseEntityClassInterface::class);
        $dto->method('getFields')->willReturn($fields);
        $dto->method('getEntityManager')->willReturn($entityManager);

        return $dto;
    }
}
