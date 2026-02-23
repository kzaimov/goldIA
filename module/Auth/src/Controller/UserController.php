<?php

declare(strict_types=1);

namespace Auth\Controller;

use Auth\Service\AuthService;
use Auth\Service\UserService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class UserController extends AbstractActionController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly UserService $userService
    ) {
    }

    public function indexAction()
    {
        if (! $this->authService->isAdmin()) {
            return $this->redirect()->toRoute('home');
        }

        return new ViewModel([
            'users' => $this->userService->fetchAll(),
        ]);
    }

    public function addAction()
    {
        if (! $this->authService->isAdmin()) {
            return $this->redirect()->toRoute('home');
        }

        $error = null;
        if ($this->getRequest()->isPost()) {
            $username = trim((string) $this->params()->fromPost('username', ''));
            $email = trim((string) $this->params()->fromPost('email', ''));
            $password = (string) $this->params()->fromPost('password', '');
            $role = (string) $this->params()->fromPost('role', 'user');

            if ($username !== '' && $email !== '' && $password !== '' && in_array($role, ['admin', 'user'], true)) {
                $this->userService->create($username, $email, $password, $role);
                return $this->redirect()->toRoute('admin-users');
            }

            $error = 'All fields are required.';
        }

        return new ViewModel([
            'error' => $error,
        ]);
    }

    public function deleteAction()
    {
        if (! $this->authService->isAdmin()) {
            return $this->redirect()->toRoute('home');
        }

        $userId = (int) $this->params()->fromRoute('id', 0);
        $identity = $this->authService->getIdentity();

        if ($userId > 0 && $identity !== null && $identity['id'] !== $userId) {
            $this->userService->delete($userId);
        }

        return $this->redirect()->toRoute('admin-users');
    }
}
