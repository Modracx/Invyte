<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


namespace App\Console\Commands;

class MigrateCommand
{
    public function handle(\PDO $pdo, array $args): void
    {
        // Create migrations table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `ims_migrations` (
                `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `migration`  VARCHAR(255) NOT NULL,
                `ran_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uq_migration` (`migration`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $migrationPath = dirname(__DIR__, 3) . '/database/migrations';
        $files = glob($migrationPath . '/*.php');
        sort($files);

        $ran = $pdo->query("SELECT migration FROM ims_migrations")->fetchAll(\PDO::FETCH_COLUMN);

        $count = 0;
        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (in_array($name, $ran)) {
                echo "  Skipped: {$name}\n";
                continue;
            }

            $result = require $file;
            // Support both anonymous classes (return new class{}) and named classes
            if (is_object($result)) {
                $migration = $result;
            } else {
                $class     = $this->getClassName($name);
                $migration = new $class();
            }
            $migration->up($pdo);

            $stmt = $pdo->prepare("INSERT INTO ims_migrations (migration) VALUES (?)");
            $stmt->execute([$name]);

            echo "  Migrated: {$name}\n";
            $count++;
        }

        echo $count > 0 ? "\n  {$count} migration(s) ran.\n" : "\n  Nothing to migrate.\n";
    }

    private function getClassName(string $filename): string
    {
        // Convert 001_create_ims_users → CreateImsUsers
        $parts = explode('_', preg_replace('/^\d+_/', '', $filename));
        return implode('', array_map('ucfirst', $parts));
    }
}
