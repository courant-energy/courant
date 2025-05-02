<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DowntimeEvent extends Model
{
    protected $fillable = ['iot_device_id', 'start_time', 'end_time'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function iotDevice()
    {
        return $this->belongsTo(IotDevice::class);
    }

    public function scopeForDevice($query, $deviceId)
    {
        return $query->where('iot_device_id', $deviceId);
    }
}
