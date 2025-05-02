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
        return $this->belongsTo(IotDevice::class);
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
        // Define the threshold for considering the device offline (e.g., 5 minutes)
        $offlineThreshold = now()->subMinutes(5);

        // Get the latest reading for the current profile
        $latestReading = $this->readings()
            // ->forProfile($this->profile_id) // Assuming this scope exists on Reading model if needed
            ->latest('reading_time') // Order by reading time explicitly
            ->first();

        // Check if a reading exists and if it's recent enough
        // Assumes 'reading_time' is cast to datetime/Carbon instance in Reading model
        if ($latestReading && $latestReading->reading_time >= $offlineThreshold) {
            // Device is online and has a recent reading
            return $latestReading->power;
        } else {
            // Device is offline (no recent reading or no reading at all)
            return 0; // Return 0 if offline or no reading found
        }
    }

    public function getCurrentPowerFactorAttribute()
    {
        // Define the threshold for considering the device offline (e.g., 5 minutes)
        $offlineThreshold = now()->subMinutes(5);

        // Get the latest reading for the current profile
        $latestReading = $this->readings()
            // ->forProfile($this->profile_id) // Assuming this scope exists on Reading model if needed
            ->latest('reading_time') // Order by reading time explicitly
            ->first();

        // Check if a reading exists and if it's recent enough
        // Assumes 'reading_time' is cast to datetime/Carbon instance in Reading model
        if ($latestReading && $latestReading->reading_time >= $offlineThreshold) {
            // Device is online and has a recent reading
            return $latestReading->power_factor;
        } else {
            // Device is offline (no recent reading or no reading at all)
            return 0; // Return 0 if offline or no reading found
        }
    }


}
