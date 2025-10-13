<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IotDevice extends Model
{
    protected $table = 'iot_devices';

    protected $fillable = ['name', 'device_uuid', 'user_id', 'is_active', 'password', 'socket_count', 'last_seen_at', 'status'];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
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

    public function readings()
    {
        return $this->hasManyThrough(
            Reading::class,
            Socket::class,
            'iot_device_id', // Foreign key on Socket table
            'socket_id',     // Foreign key on Reading table
            'id',            // Local key on IotDevice table
            'id'             // Local key on Socket table
        );
    }

    public function sockets()
    {
        return $this->hasMany(Socket::class, 'iot_device_id');
    }

    public function scopeWithSockets($query)
    {
        return $query->with('sockets');
    }
}
