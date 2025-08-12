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

    /**
     * Check if this socket is currently scheduled
     *
     * @return bool
     */
    public function isScheduled()
    {
        return $this->profile && $this->profile->scheduling_enabled;
    }

    /**
     * Get the next scheduled state change for this socket
     *
     * @return array|null
     */
    public function getNextScheduleChange()
    {
        if (!$this->profile || !$this->profile->scheduling_enabled) {
            return null;
        }

        return $this->profile->getNextScheduleChange();
    }

    /**
     * Get the current scheduled state (what the state should be according to schedule)
     * Note: This only indicates the theoretical state based on schedule, 
     * not whether a schedule should be applied right now
     *
     * @return bool|null Returns null if no schedule is active
     */
    public function getScheduledState()
    {
        if (!$this->profile || !$this->profile->scheduling_enabled) {
            return null;
        }

        return $this->profile->shouldBeOn();
    }

    /**
     * Check if a schedule should be applied to this socket right now
     *
     * @return array|null Returns array with action details if schedule should be applied, null otherwise
     */
    public function getPendingScheduleAction()
    {
        if (!$this->profile || !$this->profile->scheduling_enabled) {
            return null;
        }

        if ($this->profile->shouldApplyTurnOn() && !$this->state) {
            return [
                'action' => 'turn_on',
                'scheduled_time' => $this->profile->turn_on_time->format('H:i'),
                'reason' => 'Socket is OFF but should be turned ON according to schedule'
            ];
        }

        if ($this->profile->shouldApplyTurnOff() && $this->state) {
            return [
                'action' => 'turn_off',
                'scheduled_time' => $this->profile->turn_off_time->format('H:i'),
                'reason' => 'Socket is ON but should be turned OFF according to schedule'
            ];
        }

        return null;
    }

    /**
     * Check if the socket state matches the scheduled state
     * Note: This only compares current state vs theoretical scheduled state,
     * it doesn't mean the schedule should be applied right now
     *
     * @return bool|null Returns null if no schedule is active
     */
    public function isInSync()
    {
        $scheduledState = $this->getScheduledState();
        
        if ($scheduledState === null) {
            return null; // No schedule active
        }

        return $this->state === $scheduledState;
    }


}
