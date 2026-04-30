<?php

declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\DTO\EntityDto\Entry;
use App\Infrastructure\DTO\Forms\Entry\EntryEditFormDto;
use App\Infrastructure\DTO\Forms\Entry\EntryInsertEditFormDto;
use App\Infrastructure\DTO\Forms\Entry\EntryPostFormDto;
use App\Infrastructure\DTO\Params\QueryParams;
use App\Infrastructure\DTO\Params\QueryParams\PaginatorQueryParamsDto;
use App\Infrastructure\Handler\Action\Manager\ActionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class EntryController extends AbstractController
{
    #[Route('/entry', name: 'entryList', methods: ['GET'], format: 'json')]
    public function list(Request $request, #[MapQueryString] PaginatorQueryParamsDto $queryDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(Entry::build($entityManager), $request, QueryParams::fromArray($queryDto->toArray()))
            ->output();
    }

    #[Route('/entry/{id}', name: 'entryView', requirements: ['id' => '\d+'], methods: ['GET'], format: 'json')]
    public function view(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(Entry::build($entityManager), $request, id: $id)
            ->output();
    }

    #[Route('/entry', name: 'entryPost', methods: ['POST'], format: 'json')]
    public function post(Request $request, #[MapRequestPayload] EntryPostFormDto $formDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(Entry::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/entry', name: 'entryInsertEdit', methods: ['PUT'], format: 'json')]
    public function insertEdit(Request $request, #[MapRequestPayload] EntryInsertEditFormDto $formDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(Entry::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/entry', name: 'entryEdit', methods: ['PATCH'], format: 'json')]
    public function edit(Request $request, #[MapRequestPayload] EntryEditFormDto $formDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(Entry::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/entry/{id}', name: 'entryDelete', requirements: ['id' => '\d+'], methods: ['DELETE'], format: 'json')]
    public function delete(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(Entry::build($entityManager), $request, id: $id)
            ->output();
    }
}
