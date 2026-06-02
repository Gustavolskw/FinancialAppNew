<?php

declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\DTO\Configuration\WalletConfiguration;
use App\Infrastructure\DTO\Params\QueryParams;
use App\Infrastructure\DTO\Params\QueryParams\AnalyticsQueryParamsDto;
use App\Infrastructure\Handler\Action\Manager\ActionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

final class AnalyticsController extends AbstractController
{
    public function __construct(private readonly ActionManager $actionManager)
    {
    }

    #[Route('/analytics/annual', name: 'analyticsAnnual', methods: ['GET'], format: 'json')]
    public function annual(
        Request $request,
        #[MapQueryString] AnalyticsQueryParamsDto $queryDto,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        return $this->actionManager
            ->handleAnalytics(
                WalletConfiguration::build($entityManager),
                $request,
                QueryParams::fromArray($queryDto->toArray()),
                $queryDto->walletId,
                $queryDto->year
            )
            ->output();
    }
}
