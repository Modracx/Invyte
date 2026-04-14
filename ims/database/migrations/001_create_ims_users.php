<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


class CreateImsUsers
{
    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `ims_users` (
                `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
                `name`        VARCHAR(255)  NOT NULL,
                `email`       VARCHAR(255)  NOT NULL,
                `password`    VARCHAR(255)  NOT NULL,
                `role`        VARCHAR(20)   NOT NULL DEFAULT 'manager',
                `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
                `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `ims_users`;");
    }
}
