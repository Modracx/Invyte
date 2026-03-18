<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


namespace App\Console\Commands;

class UserUpdateCommand
{
    public function handle(\PDO $pdo, array $args): void
    {
        if (empty($args[0])) {
            echo "Usage: php ims/cli user:update <email> [--name=...] [--password=...] [--role=...] [--deactivate] [--activate]\n";
            exit(1);
        }

        $email = $args[0];
        $opts = $this->parseArgs(array_slice($args, 1));

        $stmt = $pdo->prepare("SELECT * FROM ims_users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            echo "Error: User '{$email}' not found.\n";
            exit(1);
        }

        $updates = [];
        $params = [];

        if (isset($opts['name'])) {
            $updates[] = 'name = ?';
            $params[] = $opts['name'];
        }
        if (isset($opts['password'])) {
            $updates[] = 'password = ?';
            $params[] = password_hash($opts['password'], PASSWORD_BCRYPT);
        }
        if (isset($opts['role'])) {
            if (!in_array($opts['role'], ['admin', 'manager'])) {
                echo "Error: role must be 'admin' or 'manager'\n";
                exit(1);
            }
            $updates[] = 'role = ?';
            $params[] = $opts['role'];
        }
        if (array_key_exists('deactivate', $opts)) {
            $updates[] = 'is_active = ?';
            $params[] = 0;
        } elseif (array_key_exists('activate', $opts)) {
            $updates[] = 'is_active = ?';
            $params[] = 1;
        }

        if (empty($updates)) {
            echo "No changes specified.\n";
            exit(0);
        }

        $params[] = $user['id'];
        $sql = 'UPDATE ims_users SET ' . implode(', ', $updates) . ' WHERE id = ?';
        $pdo->prepare($sql)->execute($params);

        echo "User '{$email}' updated.\n";
    }

    private function parseArgs(array $args): array
    {
        $result = [];
        foreach ($args as $arg) {
            if (preg_match('/^--(\w+)=(.+)$/', $arg, $m)) {
                $result[$m[1]] = trim($m[2], '"\'');
            } elseif (preg_match('/^--(\w+)$/', $arg, $m)) {
                $result[$m[1]] = true;
            }
        }
        return $result;
    }
}
