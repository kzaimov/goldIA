<?php

declare(strict_types=1);

namespace Portfolio\Controller;

use Auth\Service\AuthService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Portfolio\Service\PortfolioService;

class AssetController extends AbstractActionController
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
            'assets' => $this->portfolioService->getAssetList((int) $identity['id']),
        ]);
    }

    public function addAction()
    {
        $identity = $this->authService->getIdentity();
        if ($identity === null) {
            return $this->redirect()->toRoute('login');
        }

        $error = null;
        if ($this->getRequest()->isPost()) {
            $name = trim((string) $this->params()->fromPost('name', ''));
            $symbol = trim((string) $this->params()->fromPost('symbol', ''));
            $initialCost = (float) $this->params()->fromPost('initial_cost', 0);
            $purchaseDate = (string) $this->params()->fromPost('purchase_date', '');

            if ($name !== '' && $symbol !== '' && $initialCost > 0 && $purchaseDate !== '') {
                $this->portfolioService->createAsset((int) $identity['id'], $name, $symbol, $initialCost, $purchaseDate);
                return $this->redirect()->toRoute('portfolio-assets');
            }

            $error = 'All fields are required and initial cost must be greater than 0.';
        }

        return new ViewModel([
            'error' => $error,
        ]);
    }

    public function detailAction()
    {
        $identity = $this->authService->getIdentity();
        if ($identity === null) {
            return $this->redirect()->toRoute('login');
        }

        $assetId = (int) $this->params()->fromRoute('id', 0);
        $detail = $this->portfolioService->getAssetDetail((int) $identity['id'], $assetId);

        if ($detail === null) {
            return $this->redirect()->toRoute('portfolio-assets');
        }

        return new ViewModel($detail + ['error' => null]);
    }

    public function editAction()
    {
        $identity = $this->authService->getIdentity();
        if ($identity === null) {
            return $this->redirect()->toRoute('login');
        }

        $assetId = (int) $this->params()->fromRoute('id', 0);
        $userId = (int) $identity['id'];
        $asset = $this->portfolioService->getAsset($userId, $assetId);

        if ($asset === null) {
            return $this->redirect()->toRoute('portfolio-assets');
        }

        $error = null;
        if ($this->getRequest()->isPost()) {
            $name = trim((string) $this->params()->fromPost('name', ''));
            $symbol = trim((string) $this->params()->fromPost('symbol', ''));
            $initialCost = (float) $this->params()->fromPost('initial_cost', 0);
            $purchaseDate = (string) $this->params()->fromPost('purchase_date', '');

            if ($name !== '' && $symbol !== '' && $initialCost > 0 && $purchaseDate !== '') {
                $this->portfolioService->updateAsset($userId, $assetId, $name, $symbol, $initialCost, $purchaseDate);
                return $this->redirect()->toRoute('portfolio-assets/detail', ['id' => $assetId]);
            }

            $error = 'All fields are required and initial cost must be greater than 0.';

            $asset = [
                'id' => $assetId,
                'name' => $name,
                'symbol' => $symbol,
                'initial_cost' => $initialCost,
                'purchase_date' => $purchaseDate,
            ];
        }

        return new ViewModel([
            'asset' => $asset,
            'error' => $error,
        ]);
    }

    public function saveValuationAction()
    {
        $identity = $this->authService->getIdentity();
        if ($identity === null) {
            return $this->redirect()->toRoute('login');
        }

        $assetId = (int) $this->params()->fromRoute('id', 0);
        if ($this->getRequest()->isPost() && $assetId > 0) {
            $valuationDate = (string) $this->params()->fromPost('valuation_date', '');
            $currentValue = (float) $this->params()->fromPost('current_value', 0);

            if ($valuationDate !== '' && $currentValue > 0) {
                $this->portfolioService->saveValuation((int) $identity['id'], $assetId, $valuationDate, $currentValue);
            }
        }

        return $this->redirect()->toRoute('portfolio-assets/detail', ['id' => $assetId]);
    }
}
