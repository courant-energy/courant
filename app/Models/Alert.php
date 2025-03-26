<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = ['iot_device_id', 'socket_id', 'type', 'message', 'resolved','profile_id'];

    public function iotDevice()
    {
        return $this->belongsTo(IotDevice::class);
    }

    public function socket()
    {
        return $this->belongsTo(Socket::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
}
