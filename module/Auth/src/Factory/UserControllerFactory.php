<?php

declare(strict_types=1);

namespace Auth\Factory;

use Auth\Controller\UserController;
use Auth\Service\AuthService;
use Auth\Service\UserService;
use Psr\Container\ContainerInterface;

class UserControllerFactory
{
    public function __invoke(ContainerInterface $container): UserController
    {
        return new UserController(
            $container->get(AuthService::class),
            $container->get(UserService::class)
        );
    }
}
