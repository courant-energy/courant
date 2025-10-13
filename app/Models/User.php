<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements FilamentUser, HasAvatar, HasName, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'profile',
        'first_name',
        'last_name',
        'company_name',
        'email',
        'password',
        'is_onboarded',
        'archived_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_onboarded' => 'boolean',
            'archived_at' => 'datetime',
        ];
    }

    public function getFilamentName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // First check if the user is archived (soft-deleted).
        if ($this->archived_at !== null) {
            return false; // Prevent login for archived users
        }

        // Add other authorization logic here (e.g., check for 'is_admin' or roles)
        return true;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->profile ? Storage::disk(env('FILESYSTEM_DISK'))->url($this->profile) : 'https://ui-avatars.com/api/?name='.urlencode($this->first_name.' '.$this->last_name).'&color=FFFFFF&background=09090b';
    }

    public function iotDevices()
    {
        return $this->hasMany(IotDevice::class);
    }

    public function getHasUnconfiguredSocketAttribute(): bool
    {
        return $this->iotDevices()->whereHas('sockets', function ($query) {
            $query->where('profile_id', null)
                ->orWhere('profile_id', '');
        })->exists();
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function profiles()
    {
        return $this->hasMany(Profile::class);
    }

    /**
     * Check if the user is archived
     */
    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    /**
     * Archive the user
     */
    public function archive(): bool
    {
        return $this->update(['archived_at' => now()]);
    }

    /**
     * Unarchive the user
     */
    public function unarchive(): bool
    {
        return $this->update(['archived_at' => null]);
    }

    /**
     * Permanently delete all user data
     */
    public function wipeAllData(): void
    {
        DB::transaction(function () {
            try {
                // Delete all notifications sent to this user
                DB::table('notifications')->where('notifiable_id', $this->id)
                    ->where('notifiable_type', self::class)->delete();

                // Delete all questions/tickets
                $this->questions()->delete();

                // Delete all profiles and their related data
                $this->profiles()->each(function ($profile) {
                    // Delete all readings for this profile
                    DB::table('readings')->where('profile_id', $profile->id)->delete();

                    // Delete all alerts for devices owned by this user
                    DB::table('alerts')->whereIn('socket_id', function ($query) {
                        $query->select('sockets.id')
                            ->from('sockets')
                            ->join('iot_devices', 'sockets.iot_device_id', '=', 'iot_devices.id')
                            ->where('iot_devices.user_id', $this->id);
                    })->delete();

                    $profile->delete();
                });

                // Delete related IotDevices and their cascading data
                $this->iotDevices()->each(function ($device) {
                    // Delete all sockets for this device (and their readings via foreign key cascade)
                    $device->sockets()->delete();

                    // Delete any downtime events
                    DB::table('downtime_events')->where('iot_device_id', $device->id)->delete();

                    $device->delete();
                });

                // Delete the user's profile picture from storage if it exists
                if ($this->profile) {
                    if (Storage::disk('public')->exists($this->profile)) {
                        Storage::disk('public')->delete($this->profile);
                    }
                }

                // Log the data wipe for audit purposes
                Log::info('User data completely wiped', [
                    'user_id' => $this->id,
                    'email' => $this->email,
                    'wiped_at' => now(),
                ]);

            } catch (\Exception $e) {
                Log::error("Error during complete user data wipe for User ID {$this->id}: ".$e->getMessage());
                throw $e;
            }
        });
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            // Call the comprehensive data wipe method
            $user->wipeAllData();
        });
    }
}
