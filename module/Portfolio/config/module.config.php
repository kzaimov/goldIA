<?php

declare(strict_types=1);

namespace Portfolio;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'portfolio-assets' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/portfolio/assets',
                    'defaults' => [
                        'controller' => Controller\AssetController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'add' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/add',
                            'defaults' => [
                                'action' => 'add',
                            ],
                        ],
                    ],
                    'edit' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:id/edit',
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                            'defaults' => [
                                'action' => 'edit',
                            ],
                        ],
                    ],
                    'detail' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:id',
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                            'defaults' => [
                                'action' => 'detail',
                            ],
                        ],
                    ],
                    'valuation' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:id/valuation',
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                            'defaults' => [
                                'action' => 'saveValuation',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\AssetController::class => Factory\AssetControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\PortfolioService::class => Factory\PortfolioServiceFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
