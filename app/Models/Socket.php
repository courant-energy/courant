<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Socket extends Model
{
    protected $fillable = ['name', 'iot_device_id', 'profile_id'];

    public function iotDevice()
    {
        return $this->belongsTo(IOTDevice::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
