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
            CREATE TABLE IF NOT EXISTS `ims_fulfillments` (
                `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
                `order_id`      INT UNSIGNED     NOT NULL,
                `increment_id`  VARCHAR(32)      NOT NULL,
                `status`        VARCHAR(20)      NOT NULL DEFAULT 'draft',
                `notes`         TEXT             NULL,
                `created_by`    INT UNSIGNED     NULL,
                `confirmed_by`  INT UNSIGNED     NULL,
                `confirmed_at`  TIMESTAMP        NULL,
                `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_order_id` (`order_id`),
                INDEX `idx_status` (`status`),
                CONSTRAINT `fk_ful_created_by`   FOREIGN KEY (`created_by`)   REFERENCES `ims_users`(`id`) ON DELETE SET NULL,
                CONSTRAINT `fk_ful_confirmed_by` FOREIGN KEY (`confirmed_by`) REFERENCES `ims_users`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `ims_fulfillments`;");
    }
};
