<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Profile extends Model
{
    protected $fillable = [
        'name',
        'expected_power',
        'max_power_threshold',
        'min_power_threshold',
        'user_id',
        'notify_on_anomaly',        // Boolean flag for notifications
        'alert_sensitivity',         // enum: 'low', 'medium', 'high'
        'notification_channels',     // JSON field for channels (app, email, sms)
        'anomaly_detection_enabled',
        'location',
        'scheduling_enabled',
        'turn_on_time',
        'turn_off_time',
        'schedule_days',
        'last_turn_on_applied',
        'last_turn_off_applied'
    ];

    protected $casts = [
        'notify_on_anomaly' => 'boolean',
        'notification_channels' => 'array',
        'anomaly_detection_enabled' => 'boolean',
        'scheduling_enabled' => 'boolean',
        'schedule_days' => 'array',
        'turn_on_time' => 'datetime:H:i',
        'turn_off_time' => 'datetime:H:i',
        'last_turn_on_applied' => 'datetime',
        'last_turn_off_applied' => 'datetime',
    ];

    // Default values for monitoring thresholds if not set
    protected $attributes = [
        'notify_on_anomaly' => true,
        'alert_sensitivity' => 'medium',
        'notification_channels' => '["app"]',
    ];

    public function sockets()
    {
        return $this->hasMany(Socket::class);
    }

    public function readings()
    {
        return $this->hasMany(Reading::class);
    }

    /**
     * Get available appliance presets from JSON file
     *
     * @return array
     */
    public static function getAppliancePresets()
    {
        $jsonPath = resource_path('json/appliances.json');
        if (!file_exists($jsonPath)) {
            return [];
        }
        
        $appliances = json_decode(file_get_contents($jsonPath), true);
        return $appliances['appliances'] ?? [];
    }

    public function getIsActiveAttribute()
    {
        return $this->sockets()->exists();
    }

    public function scopeUnlinked($query)
    {
        return $query->whereDoesntHave('sockets');
    }

    /**
     * Check if a reading is outside the configured thresholds for this profile
     *
     * @param Reading $reading The reading to check
     * @return boolean|array False if within thresholds, or array of detected anomalies
     */
    public function isOutsideThresholds(Reading $reading)
    {
        $anomalies = [];

        // Check power thresholds
        if ($reading->power > $this->max_power_threshold) {
            $anomalies['power_high'] = [
                'value' => $reading->power,
                'threshold' => $this->max_power_threshold,
                'percentage' => round(($reading->power - $this->max_power_threshold) / $this->max_power_threshold * 100, 1)
            ];
        } elseif ($reading->power < $this->min_power_threshold && $reading->power > 0) {
            $anomalies['power_low'] = [
                'value' => $reading->power,
                'threshold' => $this->min_power_threshold,
                'percentage' => round(($this->min_power_threshold - $reading->power) / $this->min_power_threshold * 100, 1)
            ];
        }

        // Check voltage thresholds if configured
        if ($this->voltage_threshold_high && $reading->voltage > $this->voltage_threshold_high) {
            $anomalies['voltage_high'] = [
                'value' => $reading->voltage,
                'threshold' => $this->voltage_threshold_high,
                'percentage' => round(($reading->voltage - $this->voltage_threshold_high) / $this->voltage_threshold_high * 100, 1)
            ];
        } elseif ($this->voltage_threshold_low && $reading->voltage < $this->voltage_threshold_low) {
            $anomalies['voltage_low'] = [
                'value' => $reading->voltage,
                'threshold' => $this->voltage_threshold_low,
                'percentage' => round(($this->voltage_threshold_low - $reading->voltage) / $this->voltage_threshold_low * 100, 1)
            ];
        }

        // Check power factor if configured
        if (
            $this->power_factor_threshold &&
            $reading->power_factor &&
            $reading->power_factor < $this->power_factor_threshold
        ) {
            $anomalies['power_factor_low'] = [
                'value' => $reading->power_factor,
                'threshold' => $this->power_factor_threshold,
                'percentage' => round(($this->power_factor_threshold - $reading->power_factor) / $this->power_factor_threshold * 100, 1)
            ];
        }

        // Check current threshold if configured
        if (
            $this->current_threshold_high &&
            $reading->current &&
            $reading->current > $this->current_threshold_high
        ) {
            $anomalies['current_high'] = [
                'value' => $reading->current,
                'threshold' => $this->current_threshold_high,
                'percentage' => round(($reading->current - $this->current_threshold_high) / $this->current_threshold_high * 100, 1)
            ];
        }

        return empty($anomalies) ? false : $anomalies;
    }

    /**
     * Get the severity level based on alert sensitivity and deviation percentage
     *
     * @param float $deviationPercentage The percentage deviation from threshold
     * @return string The severity level (info, warning, critical)
     */
    public function getSeverityLevel($deviationPercentage)
    {
        // Adjust thresholds based on sensitivity
        switch ($this->alert_sensitivity) {
            case 'low':
                $warningThreshold = 20;
                $criticalThreshold = 40;
                break;
            case 'high':
                $warningThreshold = 5;
                $criticalThreshold = 15;
                break;
            case 'medium':
            default:
                $warningThreshold = 10;
                $criticalThreshold = 25;
                break;
        }

        if ($deviationPercentage >= $criticalThreshold) {
            return 'critical';
        } elseif ($deviationPercentage >= $warningThreshold) {
            return 'warning';
        } else {
            return 'info';
        }
    }

    /**
     * Return collection of readings in hourly intervals averaged
     * 
     * @param Carbon $start Start date in Y-m-d H:i:s format
     * @param Carbon $end End date in Y-m-d H:i:s format
     * 
     * @return Collection
     * 
     */
    public function getReadingAverageInHours($start, $end)
    {
        return $this->readings()
            ->select([
                DB::raw('DATE_FORMAT(reading_time, "%Y-%m-%d %H:00:00") as hour'),
                DB::raw('AVG(voltage) as avg_voltage'),
                DB::raw('AVG(current) as avg_current'),
                DB::raw('AVG(power) as avg_power'),
                DB::raw('AVG(power_factor) as avg_power_factor'),
                DB::raw('AVG(frequency) as avg_frequency'),
                DB::raw('SUM(energy) as total_energy'),
                DB::raw('COUNT(*) as reading_count')
            ])
            ->whereBetween('reading_time', [$start, $end])
            ->groupBy(DB::raw('DATE_FORMAT(reading_time, "%Y-%m-%d %H:00:00")'))
            ->orderBy('hour')
            ->get();
    }

    /**
     * Check if the profile should be turned on at the current time
     *
     * @return bool
     */
    public function shouldBeOn()
    {
        if (!$this->scheduling_enabled || !$this->turn_on_time || !$this->turn_off_time) {
            return false;
        }

        $now = now();
        $currentTime = $now->format('H:i');
        $currentDay = strtolower($now->format('l'));

        // Check if today is in the scheduled days
        if ($this->schedule_days && !in_array($currentDay, $this->schedule_days)) {
            return false;
        }

        $turnOnTime = $this->turn_on_time->format('H:i');
        $turnOffTime = $this->turn_off_time->format('H:i');

        // Handle schedules that span midnight
        if ($turnOnTime > $turnOffTime) {
            // Schedule spans midnight (e.g., 22:00 to 06:00)
            return $currentTime >= $turnOnTime || $currentTime < $turnOffTime;
        } else {
            // Normal schedule (e.g., 08:00 to 18:00)
            return $currentTime >= $turnOnTime && $currentTime < $turnOffTime;
        }
    }

    /**
     * Check if we need to apply a turn ON schedule now
     * 
     * @param int $toleranceMinutes How many minutes after scheduled time to still allow application
     * @return bool
     */
    public function shouldApplyTurnOn($toleranceMinutes = 5)
    {
        if (!$this->scheduling_enabled || !$this->turn_on_time) {
            return false;
        }

        $now = now();
        $currentDay = strtolower($now->format('l'));

        // Check if today is in the scheduled days
        if ($this->schedule_days && !in_array($currentDay, $this->schedule_days)) {
            return false;
        }

        // Calculate today's turn on time
        $todayTurnOn = $now->copy()->setTimeFromTimeString($this->turn_on_time->format('H:i:s'));
        
        // Check if we're within the tolerance window after the scheduled time
        $isWithinWindow = $now >= $todayTurnOn && $now <= $todayTurnOn->copy()->addMinutes($toleranceMinutes);
        
        if (!$isWithinWindow) {
            return false;
        }

        // Check if we already applied the schedule today
        if ($this->last_turn_on_applied && $this->last_turn_on_applied->isSameDay($now)) {
            return false;
        }

        return true;
    }

    /**
     * Check if we need to apply a turn OFF schedule now
     * 
     * @param int $toleranceMinutes How many minutes after scheduled time to still allow application
     * @return bool
     */
    public function shouldApplyTurnOff($toleranceMinutes = 5)
    {
        if (!$this->scheduling_enabled || !$this->turn_off_time) {
            return false;
        }

        $now = now();
        $currentDay = strtolower($now->format('l'));
        $turnOnTime = $this->turn_on_time->format('H:i');
        $turnOffTime = $this->turn_off_time->format('H:i');

        // For schedules spanning midnight, turn off applies to the next day
        if ($turnOnTime > $turnOffTime) {
            // Check if we should turn off on the previous day's schedule
            $yesterday = $now->copy()->subDay();
            $yesterdayDay = strtolower($yesterday->format('l'));
            
            if ($this->schedule_days && !in_array($yesterdayDay, $this->schedule_days)) {
                return false;
            }
        } else {
            // Normal schedule - check today
            if ($this->schedule_days && !in_array($currentDay, $this->schedule_days)) {
                return false;
            }
        }

        // Calculate today's turn off time
        $todayTurnOff = $now->copy()->setTimeFromTimeString($this->turn_off_time->format('H:i:s'));
        
        // Check if we're within the tolerance window after the scheduled time
        $isWithinWindow = $now >= $todayTurnOff && $now <= $todayTurnOff->copy()->addMinutes($toleranceMinutes);
        
        if (!$isWithinWindow) {
            return false;
        }

        // Check if we already applied the turn off schedule today
        if ($this->last_turn_off_applied && $this->last_turn_off_applied->isSameDay($now)) {
            return false;
        }

        return true;
    }

    /**
     * Apply schedule to all sockets linked to this profile
     * Only applies if we're at a scheduled time and haven't applied it already
     *
     * @return array Results with counts and actions taken
     */
    public function applySchedule()
    {
        if (!$this->scheduling_enabled) {
            return ['updated' => 0, 'action' => 'none', 'reason' => 'scheduling_disabled'];
        }

        $result = ['updated' => 0, 'action' => 'none', 'reason' => 'no_schedule_due'];
        $now = now();

        // Check if we should apply turn ON
        if ($this->shouldApplyTurnOn()) {
            $updatedCount = 0;
            foreach ($this->sockets as $socket) {
                if (!$socket->state) { // Only update if currently OFF
                    $socket->update(['state' => true]);
                    $updatedCount++;
                }
            }
            
            // Mark that we applied the turn on schedule
            $this->update(['last_turn_on_applied' => $now]);
            
            $result = [
                'updated' => $updatedCount,
                'action' => 'turn_on',
                'reason' => 'scheduled_turn_on',
                'scheduled_time' => $this->turn_on_time->format('H:i'),
                'applied_at' => $now->format('H:i:s')
            ];
        }
        // Check if we should apply turn OFF
        elseif ($this->shouldApplyTurnOff()) {
            $updatedCount = 0;
            foreach ($this->sockets as $socket) {
                if ($socket->state) { // Only update if currently ON
                    $socket->update(['state' => false]);
                    $updatedCount++;
                }
            }
            
            // Mark that we applied the turn off schedule
            $this->update(['last_turn_off_applied' => $now]);
            
            $result = [
                'updated' => $updatedCount,
                'action' => 'turn_off',
                'reason' => 'scheduled_turn_off',
                'scheduled_time' => $this->turn_off_time->format('H:i'),
                'applied_at' => $now->format('H:i:s')
            ];
        }

        return $result;
    }

    /**
     * Get the next scheduled state change
     *
     * @return array|null
     */
    public function getNextScheduleChange()
    {
        if (!$this->scheduling_enabled || !$this->turn_on_time || !$this->turn_off_time) {
            return null;
        }

        $now = now();
        $currentTime = $now->format('H:i');
        $turnOnTime = $this->turn_on_time->format('H:i');
        $turnOffTime = $this->turn_off_time->format('H:i');

        $nextChange = null;
        $nextAction = null;

        // Handle schedules that span midnight
        if ($turnOnTime > $turnOffTime) {
            if ($currentTime >= $turnOnTime || $currentTime < $turnOffTime) {
                // Currently in "on" period, next change is turn off
                if ($currentTime >= $turnOnTime) {
                    // Turn off tomorrow
                    $nextChange = $now->copy()->addDay()->setTimeFromTimeString($turnOffTime);
                } else {
                    // Turn off today
                    $nextChange = $now->copy()->setTimeFromTimeString($turnOffTime);
                }
                $nextAction = 'turn_off';
            } else {
                // Currently in "off" period, next change is turn on
                $nextChange = $now->copy()->setTimeFromTimeString($turnOnTime);
                $nextAction = 'turn_on';
            }
        } else {
            // Normal schedule
            if ($currentTime >= $turnOnTime && $currentTime < $turnOffTime) {
                // Currently in "on" period, next change is turn off
                $nextChange = $now->copy()->setTimeFromTimeString($turnOffTime);
                $nextAction = 'turn_off';
            } else if ($currentTime < $turnOnTime) {
                // Before turn on time today
                $nextChange = $now->copy()->setTimeFromTimeString($turnOnTime);
                $nextAction = 'turn_on';
            } else {
                // After turn off time today, next turn on is tomorrow
                $nextChange = $now->copy()->addDay()->setTimeFromTimeString($turnOnTime);
                $nextAction = 'turn_on';
            }
        }

        return [
            'time' => $nextChange,
            'action' => $nextAction,
            'human_readable' => $nextChange->diffForHumans()
        ];
    }

    
}
