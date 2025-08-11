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
        'schedule_days'
    ];

    protected $casts = [
        'notify_on_anomaly' => 'boolean',
        'notification_channels' => 'array',
        'anomaly_detection_enabled' => 'boolean',
        'scheduling_enabled' => 'boolean',
        'schedule_days' => 'array',
        'turn_on_time' => 'datetime:H:i',
        'turn_off_time' => 'datetime:H:i',
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
     * Apply schedule to all sockets linked to this profile
     *
     * @return int Number of sockets updated
     */
    public function applySchedule()
    {
        if (!$this->scheduling_enabled) {
            return 0;
        }

        $shouldBeOn = $this->shouldBeOn();
        $updatedCount = 0;

        foreach ($this->sockets as $socket) {
            if ($socket->state != $shouldBeOn) {
                $socket->update(['state' => $shouldBeOn]);
                $updatedCount++;
            }
        }

        return $updatedCount;
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
