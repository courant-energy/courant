<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Socket extends Model
{
    protected $fillable = ['name', 'iot_device_id', 'profile_id', 'assigned_id', 'state'];
    protected $casts = [
        'state' => 'boolean',
    ];

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
        return $query->where('profile_id', $this->profile_id);
    }

    public function scopeForDates($query, $startDate, $endDate)
    {
        return $query->whereHas('readings', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('reading_time', [$startDate, $endDate]);
        });
    }

    public function getCurrentPowerAttribute()
    {
        return $this->readings()
            ->forProfile($this->profile_id)
            ->latest()
            ->first()
            ?->power;
    }

    public function getCurrentPowerFactorAttribute()
    {
        return $this->readings()
            ->forProfile($this->profile_id)
            ->latest()
            ->first()
            ->power_factor ?? null;
    }


}
