<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


namespace App\Console\Commands;

class ReportExportCommand
{
    public function handle(\PDO $pdo, array $args): void
    {
        $format = $args[0] ?? 'csv';
        $outputDir = dirname(__DIR__, 3) . '/storage/exports';

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0775, true);
        }

        $filename = $outputDir . '/inventory_' . date('Y-m-d_His') . '.' . $format;

        $rows = $pdo->query("
            SELECT
                isi.source_code,
                isi.sku,
                isi.quantity,
                isi.status,
                COALESCE(nv.value, isi.sku) as product_name
            FROM inventory_source_item isi
            LEFT JOIN catalog_product_entity cpe ON isi.sku = cpe.sku
            LEFT JOIN catalog_product_entity_varchar nv
                ON cpe.entity_id = nv.entity_id
                AND nv.attribute_id = 73
                AND nv.store_id = 0
            ORDER BY isi.source_code, isi.sku
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $fp = fopen($filename, 'w');
        fputcsv($fp, ['Source', 'SKU', 'Product Name', 'Quantity', 'Status']);
        foreach ($rows as $row) {
            fputcsv($fp, [$row['source_code'], $row['sku'], $row['product_name'], $row['quantity'], $row['status'] ? 'In Stock' : 'Out of Stock']);
        }
        fclose($fp);

        echo "Exported " . count($rows) . " records to: {$filename}\n";
    }
}
