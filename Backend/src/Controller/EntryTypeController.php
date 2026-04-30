<?php

declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\DTO\EntityDto\EntryType;
use App\Infrastructure\DTO\Forms\EntryType\EntryTypeEditFormDto;
use App\Infrastructure\DTO\Forms\EntryType\EntryTypeInsertEditFormDto;
use App\Infrastructure\DTO\Forms\EntryType\EntryTypePostFormDto;
use App\Infrastructure\DTO\Params\QueryParams;
use App\Infrastructure\DTO\Params\QueryParams\EntityQueryParamsDto;
use App\Infrastructure\Handler\Action\Manager\ActionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class EntryTypeController extends AbstractController
{
    #[Route('/entry-type', name: 'entryTypeList', methods: ['GET'], format: 'json')]
    public function list(Request $request, #[MapQueryString] EntityQueryParamsDto $queryDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(EntryType::build($entityManager), $request, QueryParams::fromArray($queryDto->toArray()))
            ->output();
    }

    #[Route('/entry-type/{id}', name: 'entryTypeView', requirements: ['id' => '\d+'], methods: ['GET'], format: 'json')]
    public function view(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(EntryType::build($entityManager), $request, id: $id)
            ->output();
    }

    #[Route('/entry-type', name: 'entryTypePost', methods: ['POST'], format: 'json')]
    public function post(Request $request, #[MapRequestPayload] EntryTypePostFormDto $formDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(EntryType::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/entry-type', name: 'entryTypeInsertEdit', methods: ['PUT'], format: 'json')]
    public function insertEdit(Request $request, #[MapRequestPayload] EntryTypeInsertEditFormDto $formDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(EntryType::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/entry-type', name: 'entryTypeEdit', methods: ['PATCH'], format: 'json')]
    public function edit(Request $request, #[MapRequestPayload] EntryTypeEditFormDto $formDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(EntryType::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/entry-type/{id}', name: 'entryTypeDelete', requirements: ['id' => '\d+'], methods: ['DELETE'], format: 'json')]
    public function delete(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(EntryType::build($entityManager), $request, id: $id)
            ->output();
    }
}
