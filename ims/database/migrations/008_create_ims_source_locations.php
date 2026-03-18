<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS ims_source_locations (
                id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                source_code VARCHAR(255)    NOT NULL,
                parent_id   BIGINT UNSIGNED NULL DEFAULT NULL,
                type        ENUM('row','shelf','column') NOT NULL,
                name        VARCHAR(100)    NOT NULL,
                code        VARCHAR(50)     NOT NULL,
                sort_order  SMALLINT        NOT NULL DEFAULT 0,
                created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                CONSTRAINT fk_isl_parent FOREIGN KEY (parent_id)
                    REFERENCES ims_source_locations (id) ON DELETE CASCADE,
                INDEX idx_isl_source   (source_code),
                INDEX idx_isl_parent   (parent_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS ims_source_locations');
    }
};
