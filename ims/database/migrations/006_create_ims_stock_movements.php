<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


class CreateImsStockMovements
{
    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `ims_stock_movements` (
                `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
                `source_code`   VARCHAR(255)  NOT NULL,
                `sku`           VARCHAR(255)  NOT NULL,
                `type`          VARCHAR(30)   NOT NULL DEFAULT 'adjustment',
                `qty_before`    DECIMAL(12,4) NOT NULL DEFAULT 0,
                `qty_after`     DECIMAL(12,4) NOT NULL DEFAULT 0,
                `qty_change`    DECIMAL(12,4) NOT NULL DEFAULT 0,
                `reason`        VARCHAR(255)  NULL,
                `user_id`       INT UNSIGNED  NULL,
                `reference_id`  INT UNSIGNED  NULL,
                `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_sku` (`sku`),
                KEY `idx_source` (`source_code`),
                KEY `idx_created` (`created_at`),
                CONSTRAINT `fk_sm_user` FOREIGN KEY (`user_id`) REFERENCES `ims_users`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `ims_stock_movements`;");
    }
}
