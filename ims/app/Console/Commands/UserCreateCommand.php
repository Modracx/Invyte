<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


namespace App\Console\Commands;

class UserCreateCommand
{
    public function handle(\PDO $pdo, array $args): void
    {
        $opts = $this->parseArgs($args);

        $name = $opts['name'] ?? null;
        $email = $opts['email'] ?? null;
        $password = $opts['password'] ?? null;
        $role = $opts['role'] ?? 'manager';

        if (!$name || !$email || !$password) {
            echo "Usage: php ims/cli user:create --name=\"Name\" --email=\"email\" --password=\"pass\" --role=admin|manager\n";
            exit(1);
        }

        if (!in_array($role, ['admin', 'manager'])) {
            echo "Error: role must be 'admin' or 'manager'\n";
            exit(1);
        }

        // Check duplicate
        $stmt = $pdo->prepare("SELECT id FROM ims_users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo "Error: User with email '{$email}' already exists.\n";
            exit(1);
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO ims_users (name, email, password, role, is_active) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$name, $email, $hash, $role]);

        $id = $pdo->lastInsertId();
        echo "User created: [{$id}] {$name} <{$email}> ({$role})\n";
    }

    private function parseArgs(array $args): array
    {
        $result = [];
        foreach ($args as $arg) {
            if (preg_match('/^--(\w+)=(.+)$/', $arg, $m)) {
                $result[$m[1]] = trim($m[2], '"\'');
            }
        }
        return $result;
    }
}
