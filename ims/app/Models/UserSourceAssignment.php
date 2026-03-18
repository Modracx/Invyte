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

class UserSourceAssignment extends Model
{
    protected $table = 'ims_user_source_assignments';
    public $timestamps = false;
    protected $fillable = ['user_id', 'source_code'];
}
