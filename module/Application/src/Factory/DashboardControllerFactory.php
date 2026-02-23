<?php

declare(strict_types=1);

namespace Application\Factory;

use Application\Controller\DashboardController;
use Auth\Service\AuthService;
use Portfolio\Service\PortfolioService;
use Psr\Container\ContainerInterface;

class DashboardControllerFactory
{
    public function __invoke(ContainerInterface $container): DashboardController
    {
        return new DashboardController(
            $container->get(AuthService::class),
            $container->get(PortfolioService::class)
        );
    }
}
