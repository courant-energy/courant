<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IOTDevice extends Model
{
    protected $table = 'iot_devices';
    protected $fillable = ['name', 'device_uuid', 'user_id', 'is_active', 'password'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
