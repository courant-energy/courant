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
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements FilamentUser, HasName, HasAvatar, MustVerifyEmail
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
        'email',
        'password',
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
        ];
    }

    public function getFilamentName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function  getFilamentAvatarUrl(): ?string
    {
        return $this->profile ? Storage::url($this->profile) : null;
    }

    public function iotDevices()
    {
        return $this->hasMany(IotDevice::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            try {
                // Delete related IotDevices
                $user->iotDevices()->each(function ($device) {
                    try {
                        // Use forceDelete() if IotDevice uses SoftDeletes and you want permanent deletion
                        $device->delete();
                    } catch (\Exception $e) {
                        // Log or handle the error for individual device deletion
                        \Log::error("Error deleting IotDevice ID {$device->id} for User ID {$user->id}: " . $e->getMessage());
                        // Optionally rethrow or handle differently depending on requirements
                    }
                });
            } catch (\Exception $e) {
                // Log or handle the error for fetching/iterating devices
                \Log::error("Error processing IotDevices for User ID {$user->id} during deletion: " . $e->getMessage());
                // Depending on the desired behavior, you might want to stop the user deletion
                // throw $e; // Re-throw to potentially stop the user deletion process
            }

            // Delete the user's profile picture from storage if it exists
            if ($user->profile) {
                try {
                    if (Storage::disk('public')->exists($user->profile)) {
                        Storage::disk('public')->delete($user->profile);
                    }
                } catch (\Exception $e) {
                    // Log or handle the error for profile picture deletion
                    \Log::error("Error deleting profile picture {$user->profile} for User ID {$user->id}: " . $e->getMessage());
                    // Decide if this error should prevent user deletion. Usually, it shouldn't.
                }
            }
        });
    }
}
