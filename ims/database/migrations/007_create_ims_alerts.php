<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


class CreateImsAlerts
{
    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `ims_alerts` (
                `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
                `sku`          VARCHAR(255)  NOT NULL,
                `source_code`  VARCHAR(255)  NOT NULL,
                `threshold`    DECIMAL(12,4) NOT NULL DEFAULT 0,
                `current_qty`  DECIMAL(12,4) NOT NULL DEFAULT 0,
                `resolved_at`  TIMESTAMP     NULL,
                `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_sku_source` (`sku`, `source_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `ims_alerts`;");
    }
}
