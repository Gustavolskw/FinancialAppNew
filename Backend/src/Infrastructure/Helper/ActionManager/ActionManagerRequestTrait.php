<?php

declare(strict_types=1);

namespace App\Infrastructure\Helper\ActionManager;

use App\Entity\User as UserEntity;
use App\Infrastructure\DTO\Configuration\Interface\BaseEntityClassInterface;
use App\Infrastructure\DTO\Forms\FormDtoInterface;
use Symfony\Component\HttpFoundation\Request;

trait ActionManagerRequestTrait
{
    private function getFormId(?FormDtoInterface $formDto): ?int
    {
        if ($formDto === null || !property_exists($formDto, 'id')) {
            return null;
        }

        $id = $formDto->id;

        if ($id === null || $id === '') {
            return null;
        }

        return is_numeric($id) ? (int) $id : 0;
    }

    private function isPublicUserCreate(BaseEntityClassInterface $baseEntityClass, Request $request, ?int $id): bool
    {
        return $baseEntityClass->getEntityClass() === UserEntity::class
            && $request->getMethod() === Request::METHOD_POST
            && $request->attributes->get('_route') === 'userPost'
            && $id === null;
    }

    private function actionManagerRequestPayloadHas(Request $request, string $property): bool
    {
        if ($request->getContent() === '') {
            return false;
        }

        $payload = json_decode($request->getContent(), true);

        return is_array($payload) && array_key_exists($property, $payload);
    }
}
