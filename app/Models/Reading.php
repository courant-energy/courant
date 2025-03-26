<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reading extends Model
{
    protected $fillable = ['socket_id', 'voltage', 'current', 'power', 'energy', 'frequency', 'power_factor','profile_id','reading_time'];

    public function iotDevice()
    {
        return $this->belongsTo(IotDevice::class);
    }
}
