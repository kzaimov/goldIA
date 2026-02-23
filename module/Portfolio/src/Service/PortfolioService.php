<?php

declare(strict_types=1);

namespace Portfolio\Service;

use Laminas\Db\Adapter\AdapterInterface;

class PortfolioService
{
    public function __construct(private readonly AdapterInterface $db)
    {
    }

    public function getAssetList(int $userId): array
    {
        $sql = <<<'SQL'
            SELECT a.id, a.name, a.symbol, a.initial_cost, a.purchase_date,
                (
                    SELECT v.current_value
                    FROM asset_valuations v
                    WHERE v.asset_id = a.id
                    ORDER BY v.valuation_date DESC
                    LIMIT 1
                ) AS latest_value
            FROM assets a
            WHERE a.user_id = ?
            ORDER BY a.created_at DESC
        SQL;

        return $this->db->query($sql, [$userId])->toArray();
    }

    public function createAsset(int $userId, string $name, string $symbol, float $initialCost, string $purchaseDate): void
    {
        $sql = 'INSERT INTO assets (user_id, name, symbol, initial_cost, purchase_date) VALUES (?, ?, ?, ?, ?)';
        $this->db->query($sql, [$userId, $name, strtoupper($symbol), $initialCost, $purchaseDate]);
    }

    public function getAsset(int $userId, int $assetId): ?array
    {
        $sql = <<<'SQL'
            SELECT id, user_id, name, symbol, initial_cost, purchase_date
            FROM assets
            WHERE id = ? AND user_id = ?
            LIMIT 1
        SQL;

        $result = $this->db->query($sql, [$assetId, $userId]);
        $asset = $result->current();

        if (! $asset) {
            return null;
        }

        return is_array($asset) ? $asset : (array) $asset;
    }

    public function updateAsset(int $userId, int $assetId, string $name, string $symbol, float $initialCost, string $purchaseDate): void
    {
        $sql = <<<'SQL'
            UPDATE assets
            SET name = ?, symbol = ?, initial_cost = ?, purchase_date = ?
            WHERE id = ? AND user_id = ?
        SQL;

        $this->db->query($sql, [$name, strtoupper($symbol), $initialCost, $purchaseDate, $assetId, $userId]);
    }

    public function getAssetDetail(int $userId, int $assetId): ?array
    {
        $assetSql = <<<'SQL'
            SELECT id, user_id, name, symbol, initial_cost, purchase_date
            FROM assets
            WHERE id = ? AND user_id = ?
            LIMIT 1
        SQL;

        $assetResult = $this->db->query($assetSql, [$assetId, $userId]);
        $asset = $assetResult->current();
        if (! $asset) {
            return null;
        }

        $valuationSql = <<<'SQL'
            SELECT id, valuation_date, current_value
            FROM asset_valuations
            WHERE asset_id = ?
            ORDER BY valuation_date DESC
        SQL;
        $valuations = $this->db->query($valuationSql, [$assetId])->toArray();

        return [
            'asset' => $asset,
            'valuations' => $valuations,
            'yearlyPerformance' => $this->calculateYearlyPerformance((float) $asset['initial_cost'], $valuations),
        ];
    }

    public function saveValuation(int $userId, int $assetId, string $valuationDate, float $currentValue): void
    {
        $ownershipSql = 'SELECT 1 FROM assets WHERE id = ? AND user_id = ? LIMIT 1';
        $ownership = $this->db->query($ownershipSql, [$assetId, $userId])->current();
        if (! $ownership) {
            return;
        }

        $upsertSql = <<<'SQL'
            INSERT INTO asset_valuations (asset_id, valuation_date, current_value)
            VALUES (?, ?, ?)
            ON CONFLICT (asset_id, valuation_date)
            DO UPDATE SET current_value = EXCLUDED.current_value, updated_at = NOW()
        SQL;
        $this->db->query($upsertSql, [$assetId, $valuationDate, $currentValue]);
    }

    public function getDashboardData(int $userId): array
    {
        $assets = $this->getAssetList($userId);
        $performanceSeries = $this->buildDashboardPerformanceSeries($userId);

        $totalInitial = 0.0;
        $totalCurrent = 0.0;
        foreach ($assets as $asset) {
            $initial = (float) $asset['initial_cost'];
            $latest = isset($asset['latest_value']) ? (float) $asset['latest_value'] : $initial;
            $totalInitial += $initial;
            $totalCurrent += $latest;
        }

        return [
            'assetCount' => count($assets),
            'totalInitial' => $totalInitial,
            'totalCurrent' => $totalCurrent,
            'profitLoss' => $totalCurrent - $totalInitial,
            'assets' => $assets,
            'performanceYears' => $performanceSeries['years'],
            'performanceSeries' => $performanceSeries['series'],
        ];
    }

    private function buildDashboardPerformanceSeries(int $userId): array
    {
        $sql = <<<'SQL'
            SELECT
                a.id AS asset_id,
                a.name,
                a.symbol,
                a.initial_cost,
                v.valuation_date,
                v.current_value
            FROM assets a
            LEFT JOIN asset_valuations v ON v.asset_id = a.id
            WHERE a.user_id = ?
            ORDER BY a.id ASC, v.valuation_date DESC
        SQL;

        $rows = $this->db->query($sql, [$userId])->toArray();

        $assetYearEndValues = [];
        $allYears = [];

        foreach ($rows as $row) {
            $assetId = (int) $row['asset_id'];
            if (! isset($assetYearEndValues[$assetId])) {
                $symbol = trim((string) $row['symbol']);
                $assetYearEndValues[$assetId] = [
                    'label' => $symbol !== '' ? $symbol : (string) $row['name'],
                    'initial_cost' => (float) $row['initial_cost'],
                    'year_end_values' => [],
                ];
            }

            if (! isset($row['valuation_date']) || ! isset($row['current_value'])) {
                continue;
            }

            $year = substr((string) $row['valuation_date'], 0, 4);
            if (! isset($assetYearEndValues[$assetId]['year_end_values'][$year])) {
                $assetYearEndValues[$assetId]['year_end_values'][$year] = (float) $row['current_value'];
                $allYears[$year] = true;
            }
        }

        $years = array_keys($allYears);
        sort($years);

        $series = [];
        foreach ($assetYearEndValues as $assetData) {
            $initial = (float) $assetData['initial_cost'];
            $values = [];
            foreach ($years as $year) {
                if (! isset($assetData['year_end_values'][$year])) {
                    $values[] = null;
                    continue;
                }

                $yearEndValue = (float) $assetData['year_end_values'][$year];
                $values[] = $initial > 0 ? (($yearEndValue - $initial) / $initial) * 100 : 0;
            }

            $series[] = [
                'label' => $assetData['label'],
                'data' => $values,
            ];
        }

        return [
            'years' => $years,
            'series' => $series,
        ];
    }

    private function calculateYearlyPerformance(float $initialCost, array $valuations): array
    {
        if ($valuations === []) {
            return [];
        }

        $yearEndValues = [];
        foreach ($valuations as $valuation) {
            $date = (string) $valuation['valuation_date'];
            $year = substr($date, 0, 4);
            if (! isset($yearEndValues[$year])) {
                $yearEndValues[$year] = (float) $valuation['current_value'];
            }
        }

        ksort($yearEndValues);

        $results = [];
        $previousYearEnd = $initialCost;
        $first = true;

        foreach ($yearEndValues as $year => $yearEndValue) {
            $baseline = $first ? $initialCost : $previousYearEnd;
            $profitLoss = $yearEndValue - $baseline;
            $percent = $baseline > 0 ? ($profitLoss / $baseline) * 100 : 0;

            $results[] = [
                'year' => $year,
                'year_end_value' => $yearEndValue,
                'baseline' => $baseline,
                'profit_loss' => $profitLoss,
                'profit_loss_pct' => $percent,
            ];

            $previousYearEnd = $yearEndValue;
            $first = false;
        }

        return $results;
    }
}
