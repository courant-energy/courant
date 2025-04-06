<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reading extends Model
{
    protected $fillable = ['socket_id', 'voltage', 'current', 'power', 'energy', 'frequency', 'power_factor','profile_id','reading_time'];

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

}
