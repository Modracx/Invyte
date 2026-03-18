<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


return new class {
    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `ims_fulfillment_allocations` (
                `id`              BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,
                `fulfillment_id`  BIGINT UNSIGNED   NOT NULL,
                `order_item_id`   INT UNSIGNED      NOT NULL,
                `sku`             VARCHAR(64)       NOT NULL,
                `source_code`     VARCHAR(255)      NOT NULL,
                `location_id`     BIGINT UNSIGNED   NULL,
                `qty_allocated`   DECIMAL(12,4)     NOT NULL,
                `created_at`      TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`      TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_fulfillment` (`fulfillment_id`),
                INDEX `idx_sku` (`sku`),
                CONSTRAINT `fk_fa_fulfillment` FOREIGN KEY (`fulfillment_id`) REFERENCES `ims_fulfillments`(`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_fa_location`    FOREIGN KEY (`location_id`)    REFERENCES `ims_source_locations`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `ims_fulfillment_allocations`;");
    }
};
