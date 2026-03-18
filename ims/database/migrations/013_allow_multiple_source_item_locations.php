<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


return new class {
    public function up(PDO $pdo): void
    {
        // Drop the old unique(source_code, sku) so a product can occupy multiple locations
        // within the same source. Add a tighter unique on (source_code, sku, location_id)
        // to prevent exact duplicate assignments.
        $pdo->exec("ALTER TABLE ims_source_item_locations DROP INDEX uq_isil_source_sku");
        $pdo->exec("ALTER TABLE ims_source_item_locations ADD UNIQUE KEY uq_isil_source_sku_loc (source_code, sku, location_id)");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("ALTER TABLE ims_source_item_locations DROP INDEX uq_isil_source_sku_loc");
        $pdo->exec("ALTER TABLE ims_source_item_locations ADD UNIQUE KEY uq_isil_source_sku (source_code, sku)");
    }
};
