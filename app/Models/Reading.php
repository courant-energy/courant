<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reading extends Model
{
    protected $fillable = ['socket_id', 'voltage', 'current', 'power', 'energy', 'frequency', 'power_factor','profile_id','reading_time'];

    protected $casts = [
        'reading_time' => 'datetime',
    ];

    public function iotDevice()
    {
        return $this->socket->iotDevice();
    }


    public function socket()
    {
        return $this->belongsTo(Socket::class);
    }

    public function scopeForProfile($query, $profileId)
    {
        return $query->where('profile_id', $profileId);
    }

    protected static function booted()
    {
        static::created(function ($reading) {
            
        });
    }

}
