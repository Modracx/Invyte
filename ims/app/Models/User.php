<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    protected $table = 'ims_users';
    protected $hidden = ['password'];
    protected $casts = ['is_active' => 'boolean'];

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active'];

    public function sourceAssignments(): HasMany
    {
        return $this->hasMany(UserSourceAssignment::class, 'user_id');
    }

    public function getAssignedSourceCodes(): array
    {
        return $this->sourceAssignments()->pluck('source_code')->toArray();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canAccessSource(string $sourceCode): bool
    {
        if ($this->isAdmin()) return true;
        return in_array($sourceCode, $this->getAssignedSourceCodes());
    }
}
