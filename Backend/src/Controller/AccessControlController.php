<?php

declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\DTO\EntityDto\User;
use App\Infrastructure\DTO\Forms\Login\LoginFormDto;
use App\Infrastructure\Handler\Action\PrimaryAction\AccessControlAction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class AccessControlController extends AbstractController
{
    #[Route('/login', name: 'login', methods: ['POST'], format: 'json')]
    public function login(
        #[MapRequestPayload] LoginFormDto $formDto,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        return AccessControlAction::build(User::build($entityManager))
            ->login($formDto)
            ->output();
    }

    #[Route('/logoff', name: 'logoff', methods: ['POST'], format: 'json')]
    public function logoff(EntityManagerInterface $entityManager): JsonResponse
    {
        return AccessControlAction::build(User::build($entityManager))
            ->logoff()
            ->output();
    }
}
