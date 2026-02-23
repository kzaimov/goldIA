<?php

declare(strict_types=1);

namespace Auth\Factory;

use Auth\Service\UserService;
use Psr\Container\ContainerInterface;

class UserServiceFactory
{
    public function __invoke(ContainerInterface $container): UserService
    {
        return new UserService($container->get('Laminas\\Db\\Adapter\\Adapter'));
    }
}
