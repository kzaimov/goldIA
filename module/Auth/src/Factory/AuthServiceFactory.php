<?php

declare(strict_types=1);

namespace Auth\Factory;

use Auth\Service\AuthService;
use Psr\Container\ContainerInterface;

class AuthServiceFactory
{
    public function __invoke(ContainerInterface $container): AuthService
    {
        return new AuthService($container->get('Laminas\\Db\\Adapter\\Adapter'));
    }
}
