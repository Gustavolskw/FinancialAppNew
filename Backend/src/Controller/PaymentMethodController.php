<?php

declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\DTO\EntityDto\PaymentMethod;
use App\Infrastructure\DTO\Forms\PaymentMethod\PaymentMethodEditFormDto;
use App\Infrastructure\DTO\Forms\PaymentMethod\PaymentMethodInsertEditFormDto;
use App\Infrastructure\DTO\Forms\PaymentMethod\PaymentMethodPostFormDto;
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

final class PaymentMethodController extends AbstractController
{
    #[Route('/payment-method', name: 'paymentMethodList', methods: ['GET'], format: 'json')]
    public function list(Request $request, #[MapQueryString] EntityQueryParamsDto $queryDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(PaymentMethod::build($entityManager), $request, QueryParams::fromArray($queryDto->toArray()))
            ->output();
    }

    #[Route('/payment-method/{id}', name: 'paymentMethodView', requirements: ['id' => '\d+'], methods: ['GET'], format: 'json')]
    public function view(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(PaymentMethod::build($entityManager), $request, id: $id)
            ->output();
    }

    #[Route('/payment-method', name: 'paymentMethodPost', methods: ['POST'], format: 'json')]
    public function post(Request $request, #[MapRequestPayload] PaymentMethodPostFormDto $formDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(PaymentMethod::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/payment-method', name: 'paymentMethodInsertEdit', methods: ['PUT'], format: 'json')]
    public function insertEdit(Request $request, #[MapRequestPayload] PaymentMethodInsertEditFormDto $formDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(PaymentMethod::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/payment-method', name: 'paymentMethodEdit', methods: ['PATCH'], format: 'json')]
    public function edit(Request $request, #[MapRequestPayload] PaymentMethodEditFormDto $formDto, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(PaymentMethod::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/payment-method/{id}', name: 'paymentMethodDelete', requirements: ['id' => '\d+'], methods: ['DELETE'], format: 'json')]
    public function delete(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(PaymentMethod::build($entityManager), $request, id: $id)
            ->output();
    }
}
