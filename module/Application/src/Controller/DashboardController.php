<?php

declare(strict_types=1);

namespace Application\Controller;

use Auth\Service\AuthService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Portfolio\Service\PortfolioService;

class DashboardController extends AbstractActionController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly PortfolioService $portfolioService
    ) {
    }

    public function indexAction()
    {
        $identity = $this->authService->getIdentity();
        if ($identity === null) {
            return $this->redirect()->toRoute('login');
        }

        return new ViewModel([
            'identity' => $identity,
            'dashboard' => $this->portfolioService->getDashboardData((int) $identity['id']),
        ]);
    }
}
