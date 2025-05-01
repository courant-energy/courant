<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        'resolution_notes'
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
    
    /**
     * Get the Reading(s) associated with this alert via an accessor.
     *
     * This retrieves Reading models based on IDs stored within the JSON 'data' column.
     * It mimics a relationship but uses an accessor due to the non-standard
     * way the related IDs are stored. Accessed via `$alert->readings`.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getReadingsAttribute()
    {
        $readingIds = [];
        $data = $this->data ?? []; // Use null coalescing for safety

        // Check for a single ID under 'reading_id' key
        if (isset($data['reading_id'])) {
            $readingIds[] = $data['reading_id'];
        }
        // Check for multiple IDs under 'reading_ids' key (ensure it's an array)
        elseif (isset($data['reading_ids']) && is_array($data['reading_ids'])) {
            $readingIds = $data['reading_ids'];
        }

        // Filter out any invalid entries (e.g., null, non-numeric, zero)
        $validIds = array_filter($readingIds, function ($id) {
            return is_numeric($id) && $id > 0;
        });

        // Cast valid IDs to integers for the query
        $validIds = array_map('intval', $validIds);

        if (!empty($validIds)) {
            // Assuming Reading model exists and is properly namespaced (e.g., App\Models\Reading)
            // Use whereIn to fetch all matching readings
            // Ensure the Reading model is imported at the top of the file: use App\Models\Reading;
            return Reading::whereIn('id', $validIds)->get();
        }

        // Return an empty Eloquent collection if no valid IDs are found
        // Ensure Eloquent Collection is imported: use Illuminate\Database\Eloquent\Collection as EloquentCollection;
        return new \Illuminate\Database\Eloquent\Collection();
        // Alternative: return Reading::whereIn('id', [])->get();
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
}
