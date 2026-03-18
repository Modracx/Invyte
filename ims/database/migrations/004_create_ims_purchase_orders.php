<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


class CreateImsPurchaseOrders
{
    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `ims_purchase_orders` (
                `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
                `supplier_id`   INT UNSIGNED  NULL,
                `source_code`   VARCHAR(255)  NOT NULL,
                `status`        ENUM('draft','pending','partial','received','cancelled') NOT NULL DEFAULT 'draft',
                `expected_date` DATE          NULL,
                `notes`         TEXT          NULL,
                `created_by`    INT UNSIGNED  NULL,
                `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk_po_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `ims_suppliers`(`supplier_id`) ON DELETE SET NULL,
                CONSTRAINT `fk_po_user` FOREIGN KEY (`created_by`) REFERENCES `ims_users`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `ims_purchase_orders`;");
    }
}
