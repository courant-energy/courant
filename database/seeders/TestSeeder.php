<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Testing basic user creation...');

        try {
            $user = User::factory()->create();
            $this->command->info("✓ User created: {$user->first_name} {$user->last_name} (ID: {$user->id})");
        } catch (\Exception $e) {
            $this->command->error('✗ Error creating user: '.$e->getMessage());
        }
    }
}
