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

    public function sockets()
    {
        return $this->hasMany(Socket::class);
    }

    public function getIsActiveAttribute()
    {
        return $this->sockets()->exists();
    }
  
    public function scopeUnlinked($query)
    {
        return $query->whereDoesntHave('sockets');
    }
}
