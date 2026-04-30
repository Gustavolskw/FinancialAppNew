<?php

declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\DTO\EntityDto\ExpenseType;
use App\Infrastructure\DTO\Forms\ExpenseType\ExpenseTypeEditFormDto;
use App\Infrastructure\DTO\Forms\ExpenseType\ExpenseTypeInsertEditFormDto;
use App\Infrastructure\DTO\Forms\ExpenseType\ExpenseTypePostFormDto;
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

final class ExpenseTypeController extends AbstractController
{
    #[Route('/expense-type', name: 'expenseTypeList', methods: ['GET'], format: 'json')]
    public function list(Request $request, #[MapQueryString] EntityQueryParamsDto $queryDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(ExpenseType::build($entityManager), $request, QueryParams::fromArray($queryDto->toArray()))
            ->output();
    }

    #[Route('/expense-type/{id}', name: 'expenseTypeView', requirements: ['id' => '\d+'], methods: ['GET'], format: 'json')]
    public function view(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(ExpenseType::build($entityManager), $request, id: $id)
            ->output();
    }

    #[Route('/expense-type', name: 'expenseTypePost', methods: ['POST'], format: 'json')]
    public function post(Request $request, #[MapRequestPayload] ExpenseTypePostFormDto $formDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(ExpenseType::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/expense-type', name: 'expenseTypeInsertEdit', methods: ['PUT'], format: 'json')]
    public function insertEdit(Request $request, #[MapRequestPayload] ExpenseTypeInsertEditFormDto $formDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(ExpenseType::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/expense-type', name: 'expenseTypeEdit', methods: ['PATCH'], format: 'json')]
    public function edit(Request $request, #[MapRequestPayload] ExpenseTypeEditFormDto $formDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(ExpenseType::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/expense-type/{id}', name: 'expenseTypeDelete', requirements: ['id' => '\d+'], methods: ['DELETE'], format: 'json')]
    public function delete(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(ExpenseType::build($entityManager), $request, id: $id)
            ->output();
    }
}
