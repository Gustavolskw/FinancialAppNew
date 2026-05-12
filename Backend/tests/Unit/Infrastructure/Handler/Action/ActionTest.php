<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Handler\Action;

use App\Infrastructure\Handler\Action\Action;
use App\Tests\Fixtures\DummyEntity;
use App\Tests\Fixtures\DummyEntityDto;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

final class ActionTest extends TestCase
{
    public function testViewReturnsNotFoundWhenRepositoryDoesNotFindEntity(): void
    {
        $repository = $this->createStub(EntityRepository::class);
        $repository->method('find')->willReturn(null);

        $action = Action::build(new DummyEntityDto(
            DummyEntityDto::defaultFields(),
            $this->entityManager($repository),
            $repository,
        ));

        $response = $action->view(99)->output();

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('Registro não encontrado', json_decode((string) $response->getContent(), true)['message']);
    }

    public function testViewReturnsHydratedSingleResourceResponse(): void
    {
        $entity = (new DummyEntity())
            ->setId(5)
            ->setName('Principal')
            ->setStatus(true);

        $repository = $this->createStub(EntityRepository::class);
        $repository->method('find')->willReturn($entity);

        $action = Action::build(new DummyEntityDto(
            DummyEntityDto::defaultFields(),
            $this->entityManager($repository),
            $repository,
        ));

        $payload = json_decode((string) $action->view(5)->output()->getContent(), true);

        self::assertSame(200, $payload['statusCode']);
        self::assertSame([
            'id' => 5,
            'name' => 'Principal',
            'status' => true,
            'updatedAt' => null,
        ], $payload['data']['dummyEntity']);
    }

    public function testDeleteRejectsInvalidIdBeforeRepositoryLookup(): void
    {
        $action = Action::build(new DummyEntityDto(DummyEntityDto::defaultFields()));

        $response = $action->delete(0)->output();

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('ID inválido para exclusão', json_decode((string) $response->getContent(), true)['message']);
    }

    public function testSaveRejectsInvalidFieldsBeforePersistence(): void
    {
        $fields = (new \App\Infrastructure\DTO\EntityAttributes\FieldsAttribute())
            ->setNameField('name', required: true);

        $response = Action::build(new DummyEntityDto($fields))
            ->save()
            ->output();

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('Campo name é obrigatório', json_decode((string) $response->getContent(), true)['message']);
    }

    public function testSavePersistsEntityAndReturnsSingleResource(): void
    {
        $fields = DummyEntityDto::defaultFields();
        $fields->getNameField()?->setValue('Nova carteira');

        $repository = $this->createStub(EntityRepository::class);
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())->method('beginTransaction');
        $connection->expects(self::once())->method('commit');
        $connection->expects(self::never())->method('rollBack');

        $entityManager = $this->entityManager($repository, $connection);
        $entityManager->expects(self::once())
            ->method('persist')
            ->with(self::callback(function (DummyEntity $entity): bool {
                self::assertSame('Nova carteira', $entity->getName());
                self::assertTrue($entity->isStatus());
                self::assertInstanceOf(\DateTimeImmutable::class, $entity->getUpdatedAt());
                $entity->setId(15);

                return true;
            }));
        $entityManager->expects(self::once())->method('flush');

        $payload = json_decode((string) Action::build(new DummyEntityDto($fields, $entityManager, $repository))
            ->save()
            ->output()
            ->getContent(), true);

        self::assertSame(200, $payload['statusCode']);
        self::assertSame(15, $payload['data']['dummyEntity']['id']);
        self::assertSame('Nova carteira', $payload['data']['dummyEntity']['name']);
        self::assertTrue($payload['data']['dummyEntity']['status']);
    }

    public function testDeleteRemovesEntityAndFlushes(): void
    {
        $entity = (new DummyEntity())->setId(8)->setName('Reserva');
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(8)->willReturn($entity);

        $entityManager = $this->entityManager($repository);
        $entityManager->expects(self::once())->method('remove')->with($entity);
        $entityManager->expects(self::once())->method('flush');

        $action = Action::build(new DummyEntityDto(DummyEntityDto::defaultFields(), $entityManager, $repository));

        $response = $action->delete(8)->output();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Registro excluído com sucesso', json_decode((string) $response->getContent(), true)['message']);
    }

    public function testDeleteReturnsNotFoundAndMissingIdFieldErrors(): void
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(8)->willReturn(null);

        $response = Action::build(new DummyEntityDto(
            DummyEntityDto::defaultFields(),
            $this->entityManager($repository),
            $repository,
        ))->delete(8)->output();

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('Registro não encontrado para exclusão', json_decode((string) $response->getContent(), true)['message']);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(9)->willReturn(new DummyEntity());

        $response = Action::build(new DummyEntityDto(
            new \App\Infrastructure\DTO\EntityAttributes\FieldsAttribute(),
            $this->entityManager($repository),
            $repository,
        ))->delete(9)->output();

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('Campo id não configurado', json_decode((string) $response->getContent(), true)['message']);
    }

    public function testEditRejectsInvalidIdAndMissingEntity(): void
    {
        $fields = DummyEntityDto::defaultFields();

        $response = Action::build(new DummyEntityDto($fields))
            ->edit()
            ->output();

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('ID inválido para atualização', json_decode((string) $response->getContent(), true)['message']);

        $fields->getIdField()?->setValue(10);
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(10)->willReturn(null);

        $response = Action::build(new DummyEntityDto($fields, $this->entityManager($repository), $repository))
            ->edit()
            ->output();

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('Registro não encontrado para atualização', json_decode((string) $response->getContent(), true)['message']);
    }

    public function testEditUpdatesOnlyProvidedFieldsAndReturnsResource(): void
    {
        $entity = (new DummyEntity())
            ->setId(10)
            ->setName('Antiga')
            ->setStatus(true);
        $fields = DummyEntityDto::defaultFields();
        $fields->getIdField()?->setValue(10);
        $fields->getNameField()?->setValue('Atualizada');

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(10)->willReturn($entity);
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())->method('beginTransaction');
        $connection->expects(self::once())->method('commit');
        $connection->expects(self::never())->method('rollBack');
        $entityManager = $this->entityManager($repository, $connection);
        $entityManager->expects(self::once())->method('flush');

        $payload = json_decode((string) Action::build(new DummyEntityDto($fields, $entityManager, $repository))
            ->edit()
            ->output()
            ->getContent(), true);

        self::assertSame('Atualizada', $entity->getName());
        self::assertTrue($entity->isStatus());
        self::assertInstanceOf(\DateTimeImmutable::class, $entity->getUpdatedAt());
        self::assertSame(200, $payload['statusCode']);
        self::assertSame('Atualizada', $payload['data']['dummyEntity']['name']);
    }

    public function testStatusChangesEntityStatusAndFlushes(): void
    {
        $entity = (new DummyEntity())
            ->setId(3)
            ->setName('Principal')
            ->setStatus(true);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(3)->willReturn($entity);

        $entityManager = $this->entityManager($repository);
        $entityManager->expects(self::once())->method('flush');

        $action = Action::build(new DummyEntityDto(DummyEntityDto::defaultFields(), $entityManager, $repository));

        $payload = json_decode((string) $action->status(3, false)->output()->getContent(), true);

        self::assertFalse($entity->isStatus());
        self::assertSame(200, $payload['statusCode']);
        self::assertFalse($payload['data']['dummyEntity']['status']);
    }

    public function testStatusRejectsInvalidIdMissingEntityUnsupportedEntityAndMissingStatusField(): void
    {
        $response = Action::build(new DummyEntityDto(DummyEntityDto::defaultFields()))
            ->status(0, true)
            ->output();

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('ID inválido para atualização de status', json_decode((string) $response->getContent(), true)['message']);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(10)->willReturn(null);

        $response = Action::build(new DummyEntityDto(
            DummyEntityDto::defaultFields(),
            $this->entityManager($repository),
            $repository,
        ))->status(10, true)->output();

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('Registro não encontrado para atualização de status', json_decode((string) $response->getContent(), true)['message']);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(11)->willReturn(new \stdClass());

        $response = Action::build(new DummyEntityDto(
            DummyEntityDto::defaultFields(),
            $this->entityManager($repository),
            $repository,
        ))->status(11, true)->output();

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('Entidade não permite atualização de status', json_decode((string) $response->getContent(), true)['message']);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(12)->willReturn((new DummyEntity())->setId(12)->setStatus(true));

        $response = Action::build(new DummyEntityDto(
            (new \App\Infrastructure\DTO\EntityAttributes\FieldsAttribute())->setIdField('id'),
            $this->entityManager($repository),
            $repository,
        ))->status(12, false)->output();

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('Campo status não configurado', json_decode((string) $response->getContent(), true)['message']);
    }

    private function entityManager(EntityRepository $repository, ?Connection $connection = null): EntityManagerInterface
    {
        $connection ??= $this->createStub(Connection::class);
        if (!$connection instanceof \PHPUnit\Framework\MockObject\MockObject) {
            $connection->method('beginTransaction');
            $connection->method('commit');
            $connection->method('rollBack');
        }

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->with(DummyEntity::class)->willReturn($repository);
        $entityManager->method('getConnection')->willReturn($connection);

        return $entityManager;
    }
}
