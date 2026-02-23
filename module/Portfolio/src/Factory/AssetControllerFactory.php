<?php

declare(strict_types=1);

namespace Portfolio\Factory;

use Auth\Service\AuthService;
use Portfolio\Controller\AssetController;
use Portfolio\Service\PortfolioService;
use Psr\Container\ContainerInterface;

class AssetControllerFactory
{
    public function __invoke(ContainerInterface $container): AssetController
    {
        return new AssetController(
            $container->get(AuthService::class),
            $container->get(PortfolioService::class)
        );
    }
}
