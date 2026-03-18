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
        $pdo->exec("
            ALTER TABLE ims_source_item_locations
                ADD COLUMN qty DECIMAL(12,4) NOT NULL DEFAULT 0
                    AFTER location_id
        ");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec("ALTER TABLE ims_source_item_locations DROP COLUMN qty");
    }
};
