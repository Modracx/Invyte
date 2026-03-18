<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


class CreateImsSuppliers
{
    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `ims_suppliers` (
                `supplier_id` INT UNSIGNED  NOT NULL AUTO_INCREMENT,
                `name`        VARCHAR(255)  NOT NULL,
                `code`        VARCHAR(64)   NOT NULL,
                `contact_name` VARCHAR(255) NULL,
                `email`       VARCHAR(255)  NULL,
                `phone`       VARCHAR(64)   NULL,
                `address`     TEXT          NULL,
                `country_id`  CHAR(2)       NULL,
                `city`        VARCHAR(255)  NULL,
                `notes`       TEXT          NULL,
                `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
                `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`supplier_id`),
                UNIQUE KEY `uq_supplier_code` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `ims_suppliers`;");
    }
}
