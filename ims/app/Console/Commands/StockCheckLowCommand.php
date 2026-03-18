<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


namespace App\Console\Commands;

class StockCheckLowCommand
{
    public function handle(\PDO $pdo, array $args): void
    {
        $threshold = 10;
        foreach ($args as $arg) {
            if (preg_match('/^--threshold=(\d+)$/', $arg, $m)) {
                $threshold = (int)$m[1];
            }
        }

        echo "Checking for low stock items (threshold: {$threshold})...\n";

        $rows = $pdo->query("
            SELECT source_code, sku, quantity
            FROM inventory_source_item
            WHERE quantity <= {$threshold} AND quantity >= 0
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $inserted = 0;
        foreach ($rows as $row) {
            // Check if unresolved alert already exists
            $stmt = $pdo->prepare("
                SELECT id FROM ims_alerts
                WHERE sku = ? AND source_code = ? AND resolved_at IS NULL
            ");
            $stmt->execute([$row['sku'], $row['source_code']]);

            if ($stmt->fetch()) {
                // Update current qty
                $stmt = $pdo->prepare("
                    UPDATE ims_alerts SET current_qty = ? WHERE sku = ? AND source_code = ? AND resolved_at IS NULL
                ");
                $stmt->execute([$row['quantity'], $row['sku'], $row['source_code']]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO ims_alerts (sku, source_code, threshold, current_qty)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$row['sku'], $row['source_code'], $threshold, $row['quantity']]);
                $inserted++;
            }
        }

        echo "Found " . count($rows) . " low stock items. Created {$inserted} new alert(s).\n";
    }
}
