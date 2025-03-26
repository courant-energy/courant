<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'name',
        'expected_power',
        'max_power_threshold',
        'min_power_threshold',
        'user_id'
    ];
  
}
