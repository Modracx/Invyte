<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


class CreateImsUserSourceAssignments
{
    public function up(\PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `ims_user_source_assignments` (
                `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
                `user_id`     INT UNSIGNED  NOT NULL,
                `source_code` VARCHAR(255)  NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_user_source` (`user_id`, `source_code`),
                CONSTRAINT `fk_usa_user` FOREIGN KEY (`user_id`) REFERENCES `ims_users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `ims_user_source_assignments`;");
    }
}
