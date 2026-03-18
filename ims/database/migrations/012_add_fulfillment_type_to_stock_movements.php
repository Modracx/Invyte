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
            ALTER TABLE `ims_stock_movements`
            MODIFY COLUMN `type` ENUM('adjustment','receive','sync','transfer','fulfillment') NOT NULL DEFAULT 'adjustment';
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec("
            ALTER TABLE `ims_stock_movements`
            MODIFY COLUMN `type` ENUM('adjustment','receive','sync','transfer') NOT NULL DEFAULT 'adjustment';
        ");
    }
};
