<?php

declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\DTO\EntityDto\Wallet;
use App\Infrastructure\DTO\Forms\StatusFormDto;
use App\Infrastructure\DTO\Forms\Wallet\WalletEditFormDto;
use App\Infrastructure\DTO\Forms\Wallet\WalletInsertEditFormDto;
use App\Infrastructure\DTO\Forms\Wallet\WalletPostFormDto;
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

final class WalletController extends AbstractController
{
    #[Route('/wallet', name: 'walletList', methods: ['GET'], format: 'json')]
    public function list(
        Request $request,
        #[MapQueryString] PaginatorQueryParamsDto $queryDto,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        return (new ActionManager())
            ->handle(Wallet::build($entityManager), $request, QueryParams::fromArray($queryDto->toArray()))
            ->output();
    }

    #[Route('/wallet/user/{userId}', name: 'walletByUser', requirements: ['userId' => '\d+'], methods: ['GET'], format: 'json')]
    public function byUser(int $userId, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $queryParams = $request->query->all();
        $queryParams['userId'] = $userId;

        return (new ActionManager())
            ->handle(Wallet::build($entityManager), $request, QueryParams::fromArray($queryParams))
            ->output();
    }

    #[Route('/wallet/{id}', name: 'walletView', requirements: ['id' => '\d+'], methods: ['GET'], format: 'json')]
    public function view(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(Wallet::build($entityManager), $request, id: $id)
            ->output();
    }

    #[Route('/wallet', name: 'walletPost', methods: ['POST'], format: 'json')]
    public function post(
        Request $request,
        #[MapRequestPayload] WalletPostFormDto $formDto,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        return (new ActionManager())
            ->handle(Wallet::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/wallet', name: 'walletInsertEdit', methods: ['PUT'], format: 'json')]
    public function insertEdit(
        Request $request,
        #[MapRequestPayload] WalletInsertEditFormDto $formDto,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        return (new ActionManager())
            ->handle(Wallet::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/wallet', name: 'walletEdit', methods: ['PATCH'], format: 'json')]
    public function edit(
        Request $request,
        #[MapRequestPayload] WalletEditFormDto $formDto,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        return (new ActionManager())
            ->handle(Wallet::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/wallet/{id}/status', name: 'walletStatus', requirements: ['id' => '\d+'], methods: ['PATCH'], format: 'json')]
    public function status(
        int $id,
        Request $request,
        #[MapRequestPayload] StatusFormDto $formDto,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        return (new ActionManager())
            ->handleStatus(Wallet::build($entityManager), $request, $id, $formDto)
            ->output();
    }
}
