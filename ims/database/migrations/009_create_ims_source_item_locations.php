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
            CREATE TABLE IF NOT EXISTS ims_source_item_locations (
                id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                source_code VARCHAR(255)    NOT NULL,
                sku         VARCHAR(64)     NOT NULL,
                location_id BIGINT UNSIGNED NOT NULL,
                qty         DECIMAL(12,4)   NOT NULL DEFAULT 0,
                created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_isil_source_sku_loc (source_code, sku, location_id),
                CONSTRAINT fk_isil_location FOREIGN KEY (location_id)
                    REFERENCES ims_source_locations (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS ims_source_item_locations');
    }
};
