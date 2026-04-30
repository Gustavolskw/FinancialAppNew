<?php

declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\DTO\EntityDto\Transaction;
use App\Infrastructure\DTO\Forms\Transaction\TransactionEditFormDto;
use App\Infrastructure\DTO\Forms\Transaction\TransactionInsertEditFormDto;
use App\Infrastructure\DTO\Forms\Transaction\TransactionPostFormDto;
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

final class TransactionController extends AbstractController
{
    #[Route('/transaction', name: 'transactionList', methods: ['GET'], format: 'json')]
    public function list(Request $request, #[MapQueryString] PaginatorQueryParamsDto $queryDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(Transaction::build($entityManager), $request, QueryParams::fromArray($queryDto->toArray()))
            ->output();
    }

    #[Route('/transaction/wallet/{walletId}', name: 'transactionByWallet', requirements: ['walletId' => '\d+'], methods: ['GET'], format: 'json')]
    public function byWallet(int $walletId, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $queryParams = $request->query->all();
        $queryParams['walletId'] = $walletId;

        return (new ActionManager())
            ->handle(Transaction::build($entityManager), $request, QueryParams::fromArray($queryParams))
            ->output();
    }

    #[Route('/transaction/{id}', name: 'transactionView', requirements: ['id' => '\d+'], methods: ['GET'], format: 'json')]
    public function view(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(Transaction::build($entityManager), $request, id: $id)
            ->output();
    }

    #[Route('/transaction', name: 'transactionPost', methods: ['POST'], format: 'json')]
    public function post(Request $request, #[MapRequestPayload] TransactionPostFormDto $formDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(Transaction::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/transaction', name: 'transactionInsertEdit', methods: ['PUT'], format: 'json')]
    public function insertEdit(Request $request, #[MapRequestPayload] TransactionInsertEditFormDto $formDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(Transaction::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/transaction', name: 'transactionEdit', methods: ['PATCH'], format: 'json')]
    public function edit(Request $request, #[MapRequestPayload] TransactionEditFormDto $formDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(Transaction::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/transaction/{id}', name: 'transactionDelete', requirements: ['id' => '\d+'], methods: ['DELETE'], format: 'json')]
    public function delete(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(Transaction::build($entityManager), $request, id: $id)
            ->output();
    }
}
