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

    public function readings()
    {
        return $this->hasMany(Reading::class);
    }

    public function scopeCurrentProfile($query)
    {
        return $query->where('profile_id', Profile::current()->id);
    }

    public function scopeForDates($query, $startDate, $endDate)
    {
        return $query->whereHas('readings', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('reading_time', [$startDate, $endDate]);
        });
    }
}
