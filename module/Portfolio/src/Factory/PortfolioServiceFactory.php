<?php

declare(strict_types=1);

namespace Portfolio\Factory;

use Portfolio\Service\PortfolioService;
use Psr\Container\ContainerInterface;

class PortfolioServiceFactory
{
    public function __invoke(ContainerInterface $container): PortfolioService
    {
        return new PortfolioService($container->get('Laminas\\Db\\Adapter\\Adapter'));
    }
}
