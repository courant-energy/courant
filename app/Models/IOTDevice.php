<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class IotDevice extends Model
{
    protected $table = 'iot_devices';
    protected $fillable = ['name', 'device_uuid', 'user_id', 'is_active', 'password'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'password',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeForUser($query, $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function unlink()
    {
        $this->update([
            'user_id' => null,
            'is_active' => false,
            // TODO: Revert to the factory default password
            'password' => 'password',
        ]);

        // TODO: Delete all recorded data

    }
}
