<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


class CreateImsPurchaseOrderItems
{
    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `ims_purchase_order_items` (
                `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
                `po_id`         INT UNSIGNED  NOT NULL,
                `sku`           VARCHAR(255)  NOT NULL,
                `product_name`  VARCHAR(255)  NULL,
                `qty_ordered`   DECIMAL(12,4) NOT NULL DEFAULT 0,
                `qty_received`  DECIMAL(12,4) NOT NULL DEFAULT 0,
                `unit_cost`     DECIMAL(12,4) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk_poi_po` FOREIGN KEY (`po_id`) REFERENCES `ims_purchase_orders`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `ims_purchase_order_items`;");
    }
}
