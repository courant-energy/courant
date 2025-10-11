<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserArchivingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_archived(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'archived_at' => null,
        ]);

        $this->assertFalse($user->isArchived());
        $this->assertTrue($user->canAccessPanel(app(\Filament\Panel::class)));

        $user->archive();

        $this->assertTrue($user->isArchived());
        $this->assertFalse($user->canAccessPanel(app(\Filament\Panel::class)));
    }

    public function test_user_can_be_unarchived(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'archived_at' => now(),
        ]);

        $this->assertTrue($user->isArchived());
        $this->assertFalse($user->canAccessPanel(app(\Filament\Panel::class)));

        $user->unarchive();

        $this->assertFalse($user->isArchived());
        $this->assertTrue($user->canAccessPanel(app(\Filament\Panel::class)));
    }

    public function test_archived_user_cannot_access_panel(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'archived_at' => now(),
        ]);

        $this->assertFalse($user->canAccessPanel(app(\Filament\Panel::class)));
    }

    public function test_active_user_can_access_panel(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'archived_at' => null,
        ]);

        $this->assertTrue($user->canAccessPanel(app(\Filament\Panel::class)));
    }
}