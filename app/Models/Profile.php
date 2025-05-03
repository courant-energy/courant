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

    ];

    protected $casts = [
        'notify_on_anomaly' => 'boolean',
        'notification_channels' => 'array',
    ];

    // Default values for monitoring thresholds if not set
    protected $attributes = [
        'notify_on_anomaly' => true,
        'alert_sensitivity' => 'medium',
        'notification_channels' => '["app"]',
        'voltage_threshold_high' => 250,
        'voltage_threshold_low' => 210,
        'power_factor_threshold' => 0.85
    ];

    public function sockets()
    {
        return $this->hasMany(Socket::class);
    }

    public function readings()
    {
        return $this->hasMany(Reading::class);
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

    
}
