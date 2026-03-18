<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


namespace App\Console\Commands;

class UserAssignCommand
{
    public function handle(\PDO $pdo, array $args): void
    {
        if (count($args) < 2) {
            echo "Usage: php ims/cli user:assign <email> <source_code>\n";
            exit(1);
        }

        [$email, $sourceCode] = $args;

        $stmt = $pdo->prepare("SELECT id, role FROM ims_users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            echo "Error: User '{$email}' not found.\n";
            exit(1);
        }

        // Check source exists
        $stmt = $pdo->prepare("SELECT source_code FROM inventory_source WHERE source_code = ?");
        $stmt->execute([$sourceCode]);
        if (!$stmt->fetch()) {
            echo "Warning: Source '{$sourceCode}' does not exist in inventory_source table.\n";
        }

        $stmt = $pdo->prepare("INSERT IGNORE INTO ims_user_source_assignments (user_id, source_code) VALUES (?, ?)");
        $stmt->execute([$user['id'], $sourceCode]);

        echo "Assigned user '{$email}' to source '{$sourceCode}'.\n";
    }
}
