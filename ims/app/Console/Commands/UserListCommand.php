<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


namespace App\Console\Commands;

class UserListCommand
{
    public function handle(\PDO $pdo, array $args): void
    {
        $users = $pdo->query("SELECT id, name, email, role, is_active, created_at FROM ims_users ORDER BY id")->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($users)) {
            echo "No users found.\n";
            return;
        }

        printf("%-4s %-25s %-35s %-10s %-8s %-20s\n", 'ID', 'Name', 'Email', 'Role', 'Active', 'Created');
        echo str_repeat('-', 110) . "\n";

        foreach ($users as $u) {
            // Get assigned sources
            $stmt = $pdo->prepare("SELECT source_code FROM ims_user_source_assignments WHERE user_id = ?");
            $stmt->execute([$u['id']]);
            $sources = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            printf("%-4s %-25s %-35s %-10s %-8s %-20s\n",
                $u['id'],
                substr($u['name'], 0, 24),
                substr($u['email'], 0, 34),
                $u['role'],
                $u['is_active'] ? 'Yes' : 'No',
                $u['created_at']
            );

            if ($sources) {
                echo "       Sources: " . implode(', ', $sources) . "\n";
            }
        }
    }
}
