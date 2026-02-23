<?php

declare(strict_types=1);

namespace Auth\Factory;

use Auth\Controller\AuthController;
use Auth\Service\AuthService;
use Psr\Container\ContainerInterface;

class AuthControllerFactory
{
    public function __invoke(ContainerInterface $container): AuthController
    {
        return new AuthController($container->get(AuthService::class));
    }
}
