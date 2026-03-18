<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


namespace App\Console\Commands;

class StockSyncCommand
{
    public function handle(\PDO $pdo, array $args): void
    {
        echo "Syncing cataloginventory_stock_item with inventory_source_item...\n";

        // Get all SKUs with their total quantity across all sources
        $rows = $pdo->query("
            SELECT sku, SUM(quantity) as total_qty
            FROM inventory_source_item
            GROUP BY sku
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $synced = 0;
        foreach ($rows as $row) {
            // Get product entity_id
            $stmt = $pdo->prepare("SELECT entity_id FROM catalog_product_entity WHERE sku = ?");
            $stmt->execute([$row['sku']]);
            $product = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$product) continue;

            $totalQty = (float) $row['total_qty'];
            $inStock = $totalQty > 0 ? 1 : 0;

            $stmt = $pdo->prepare("
                UPDATE cataloginventory_stock_item
                SET qty = ?, is_in_stock = ?
                WHERE product_id = ?
            ");
            $stmt->execute([$totalQty, $inStock, $product['entity_id']]);
            $synced++;
        }

        echo "Synced {$synced} products.\n";
    }
}
