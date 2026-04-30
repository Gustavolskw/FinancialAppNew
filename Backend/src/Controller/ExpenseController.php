<?php

declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\DTO\EntityDto\Expense;
use App\Infrastructure\DTO\Forms\Expense\ExpenseEditFormDto;
use App\Infrastructure\DTO\Forms\Expense\ExpenseInsertEditFormDto;
use App\Infrastructure\DTO\Forms\Expense\ExpensePostFormDto;
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

final class ExpenseController extends AbstractController
{
    #[Route('/expense', name: 'expenseList', methods: ['GET'], format: 'json')]
    public function list(Request $request, #[MapQueryString] PaginatorQueryParamsDto $queryDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(Expense::build($entityManager), $request, QueryParams::fromArray($queryDto->toArray()))
            ->output();
    }

    #[Route('/expense/{id}', name: 'expenseView', requirements: ['id' => '\d+'], methods: ['GET'], format: 'json')]
    public function view(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(Expense::build($entityManager), $request, id: $id)
            ->output();
    }

    #[Route('/expense', name: 'expensePost', methods: ['POST'], format: 'json')]
    public function post(Request $request, #[MapRequestPayload] ExpensePostFormDto $formDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(Expense::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/expense', name: 'expenseInsertEdit', methods: ['PUT'], format: 'json')]
    public function insertEdit(Request $request, #[MapRequestPayload] ExpenseInsertEditFormDto $formDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(Expense::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/expense', name: 'expenseEdit', methods: ['PATCH'], format: 'json')]
    public function edit(Request $request, #[MapRequestPayload] ExpenseEditFormDto $formDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(Expense::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/expense/{id}', name: 'expenseDelete', requirements: ['id' => '\d+'], methods: ['DELETE'], format: 'json')]
    public function delete(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(Expense::build($entityManager), $request, id: $id)
            ->output();
    }
}
