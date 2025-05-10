<?php

namespace App\Models;

use App\Notifications\AnomalyAlert;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class Alert extends Model
{
    protected $fillable = [
        'iot_device_id',
        'socket_id',
        'profile_id',
        'type',
        'message',
        'data',
        'severity',
        'resolved',
        'resolved_at',
        'resolution_notes',
        'reading_id',
    ];

    protected $casts = [
        'data' => 'array',
        'resolved' => 'boolean',
        'resolved_at' => 'datetime'
    ];

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

    public function reading()
    {
        return $this->belongsTo(Reading::class);
    }


    /**
     * Scope for unresolved alerts
     */
    public function scopePending($query)
    {
        return $query->where('resolved', false);
    }

    /**
     * Scope for resolved alerts
     */
    public function scopeResolved($query)
    {
        return $query->where('resolved', true);
    }

    /**
     * Scope for filtering anomaly alerts (real-time issues)
     */
    public function scopeAnomalies($query)
    {
        return $query->where('type', 'like', 'anomaly_%');
    }

    /**
     * Scope for filtering prediction alerts (future potential issues)
     */
    public function scopePredictions($query)
    {
        return $query->where('type', 'like', 'prediction_%');
    }

    /**
     * Scope for filtering by severity
     */
    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Mark this alert as resolved
     */
    public function markAsResolved($notes = null)
    {
        $this->update([
            'resolved' => true,
            'resolved_at' => Carbon::now(),
            'resolution_notes' => $notes
        ]);

        return $this;
    }

    /**
     * Check if this alert is a prediction rather than a real-time anomaly
     */
    public function isPrediction()
    {
        return strpos($this->type, 'prediction_') === 0;
    }

    /**
     * Check if this alert is a real-time anomaly
     */
    public function isAnomaly()
    {
        return strpos($this->type, 'anomaly_') === 0;
    }

    /**
     * Get a human-readable alert type
     */
    public function getReadableTypeAttribute()
    {
        $baseType = str_replace(['anomaly_', 'prediction_'], '', $this->type);

        switch ($baseType) {
            case 'power_high':
                return 'High Power';
            case 'power_low':
                return 'Low Power';
            case 'voltage_high':
                return 'High Voltage';
            case 'voltage_low':
                return 'Low Voltage';
            case 'current_high':
                return 'High Current';
            case 'power_factor_low':
                return 'Poor Power Factor';
            case 'voltage_instability':
                return 'Voltage Instability';
            case 'power_factor_degradation':
                return 'Power Factor Degradation';
            case 'increasing_power_consumption':
                return 'Increasing Power Consumption';
            case 'current_spikes':
                return 'Current Spikes';
            default:
                return ucwords(str_replace('_', ' ', $baseType));
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($alert) {
            // find the device and socket then turn off the socket
            Log::info('Alert created: ', $alert->toArray());
            Log::info('Boot method called for Alert model');
            $device = $alert->iotDevice;
            $socket = $device->sockets()->where('id', $alert->socket_id)->first();
            if ($socket) {
                Log::info('Socket found: ', $socket->toArray());
                $socket->update(['state' => false]);
            }
            // Send a detailed notification to the user about the alert
            $severity = ucfirst($alert->severity);
            $type = $alert->getReadableTypeAttribute();
            $deviceName = $device->name ?? $device->device_uuid;
            $socketName = $socket ? "Socket #{$socket->assigned_id}" : 'Unknown Socket';

            if ($socket->profile->notification_channels->contains('app')) {

                Notification::make()
                    ->title("{$severity} Alert: {$type}")
                    ->body("A {$alert->severity} alert has been detected for device '{$deviceName}' on {$socketName}. " .
                        "The socket has been automatically turned off for safety. Please check the device status.")
                    ->icon('heroicon-o-exclamation-triangle')
                    ->danger()
                    ->sendToDatabase(User::find($device->user_id));
            }
            if ($socket->profile->notification_channels->contains('email')) {
                $user = User::find($device->user_id);
                $user->notify(new AnomalyAlert($severity, $type, $deviceName, $socketName, $alert->message));
              
            }
        });
    }
}
