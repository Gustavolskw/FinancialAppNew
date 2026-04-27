<?php

declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\DTO\EntityDto\User;
use App\Infrastructure\DTO\Forms\StatusFormDto;
use App\Infrastructure\DTO\Forms\User\UserEditFormDto;
use App\Infrastructure\DTO\Forms\User\UserInsertEditFormDto;
use App\Infrastructure\DTO\Forms\User\UserPostFormDto;
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

class UserController extends AbstractController
{
    #[Route('/user', name:'userList', methods: ['GET'], format: 'json')]
    public function list(
        Request $request,
        #[MapQueryString] EntityQueryParamsDto $userDto,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $queryParams = QueryParams::fromArray($userDto->toArray());

        return (new ActionManager())
            ->handle(User::build($entityManager), $request, $queryParams)
            ->output();
    }

    #[Route('/user/{id}', name:'userView', requirements: ['id' => '\d+'], methods: ['GET'], format: 'json')]
    public function view(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        return (new ActionManager())
            ->handle(User::build($entityManager), $request, id: $id)
            ->output();
    }

    #[Route('/user', name:'userPost', methods: ['POST'], format: 'json')]
    public function post(
        Request $request,
        #[MapRequestPayload] UserPostFormDto $formDto,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        return (new ActionManager())
            ->handle(User::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/user', name:'userInsertEdit', methods: ['PUT'], format: 'json')]
    public function insertEdit(
        Request $request,
        #[MapRequestPayload] UserInsertEditFormDto $formDto,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        return (new ActionManager())
            ->handle(User::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/user', name:'userEdit', methods: ['PATCH'], format: 'json')]
    public function edit(
        Request $request,
        #[MapRequestPayload] UserEditFormDto $formDto,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        return (new ActionManager())
            ->handle(User::build($entityManager), $request, formDto: $formDto)
            ->output();
    }

    #[Route('/user/{id}', name:'userDelete', requirements: ['id' => '\d+'], methods: ['DELETE'], format: 'json')]
    public function delete(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        return (new ActionManager())
            ->handle(User::build($entityManager), $request, id: $id)
            ->output();
    }

    #[Route('/user/{id}/status', name:'userStatus', requirements: ['id' => '\d+'], methods: ['PATCH'], format: 'json')]
    public function status(
        int $id,
        #[MapRequestPayload] StatusFormDto $formDto,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        return (new ActionManager())
            ->handleStatus(User::build($entityManager), $id, $formDto)
            ->output();
    }
}
