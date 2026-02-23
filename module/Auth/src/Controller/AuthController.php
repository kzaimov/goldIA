<?php

declare(strict_types=1);

namespace Auth\Controller;

use Auth\Service\AuthService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class AuthController extends AbstractActionController
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function loginAction()
    {
        if ($this->authService->hasIdentity()) {
            return $this->redirect()->toRoute('home');
        }

        $error = null;
        $request = $this->getRequest();
        if ($request->isPost()) {
            $email = trim((string) $request->getPost('email', ''));
            $password = (string) $request->getPost('password', '');

            if ($this->authService->login($email, $password)) {
                return $this->redirect()->toRoute('home');
            }

            $error = 'Invalid credentials.';
        }

        return new ViewModel(['error' => $error]);
    }

    public function logoutAction()
    {
        $this->authService->logout();
        return $this->redirect()->toRoute('login');
    }
}
