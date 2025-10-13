<?php

namespace Database\Seeders;

use App\Models\IotDevice;
use App\Models\Profile;
use App\Models\Reading;
use App\Models\Socket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DebugSeeder extends Seeder
{
    /**
     * Debug version with only 2 users for quick testing
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $this->command->info('🐛 Starting DEBUG seeder (2 users only)...');
            Log::info('DebugSeeder: Starting debug seeder with 2 users');

            for ($userIndex = 1; $userIndex <= 2; $userIndex++) {
                $this->command->info("Creating user {$userIndex}/2...");
                Log::info("DebugSeeder: Creating user {$userIndex}");

                $user = User::factory()->create();
                Log::info("DebugSeeder: Created user {$userIndex} - ID: {$user->id}, Email: {$user->email}");

                // Create 1 device per user for simplicity
                $this->command->info("Creating device for user {$userIndex}...");
                Log::info("DebugSeeder: Creating device for user {$userIndex}");

                $device = IotDevice::create([
                    'name' => "Debug Device - User {$userIndex}",
                    'device_uuid' => 'DEBUG-'.strtoupper(uniqid()),
                    'user_id' => $user->id,
                    'is_active' => true,
                    'password' => 'debug_password',
                    'socket_count' => 2,
                    'last_seen_at' => now(),
                    'status' => 'online',
                ]);
                Log::info("DebugSeeder: Created device - ID: {$device->id}, Name: {$device->name}");

                // Create 2 sockets
                for ($socketIndex = 1; $socketIndex <= 2; $socketIndex++) {
                    $this->command->info("Creating socket {$socketIndex}/2 for device {$device->id}...");
                    Log::info("DebugSeeder: Creating socket {$socketIndex} for device {$device->id}");

                    $socket = Socket::create([
                        'name' => "Debug Socket {$socketIndex}",
                        'iot_device_id' => $device->id,
                        'assigned_id' => $socketIndex,
                        'state' => false,
                    ]);
                    Log::info("DebugSeeder: Created socket - ID: {$socket->id}");

                    $this->command->info("Creating profile for socket {$socket->id}...");
                    Log::info("DebugSeeder: Creating profile for socket {$socket->id}");

                    $profile = Profile::create([
                        'name' => "Debug Profile - Socket {$socketIndex}",
                        'expected_power' => 1000,
                        'max_power_threshold' => 1200,
                        'min_power_threshold' => 500,
                        'user_id' => $user->id,
                        'notify_on_anomaly' => true,
                        'alert_sensitivity' => 'medium',
                        'notification_channels' => ['app'],
                        'anomaly_detection_enabled' => true,
                        'location' => 'Debug Location',
                    ]);
                    Log::info("DebugSeeder: Created profile - ID: {$profile->id}");

                    $socket->update(['profile_id' => $profile->id]);
                    Log::info("DebugSeeder: Assigned profile {$profile->id} to socket {$socket->id}");

                    $this->command->info("Creating 24 readings for profile {$profile->id}...");
                    Log::info("DebugSeeder: Starting readings creation for profile {$profile->id}");

                    // Create only 24 readings (1 per hour for 1 day) for quick testing
                    for ($i = 0; $i < 24; $i++) {
                        Reading::create([
                            'socket_id' => $socket->id,
                            'profile_id' => $profile->id,
                            'voltage' => rand(220, 240),
                            'current' => rand(1, 5),
                            'power' => rand(800, 1100),
                            'energy' => rand(50, 100),
                            'frequency' => 50.0,
                            'power_factor' => 0.9,
                            'reading_time' => now()->subHours(24 - $i),
                        ]);

                        if ($i % 5 === 0) {
                            Log::info("DebugSeeder: Created {$i}/24 readings for profile {$profile->id}");
                        }
                    }

                    Log::info("DebugSeeder: Completed 24 readings for profile {$profile->id}");
                }
            }

            DB::commit();

            $userCount = User::count();
            $deviceCount = IotDevice::count();
            $socketCount = Socket::count();
            $profileCount = Profile::count();
            $readingCount = Reading::count();

            $this->command->info('✅ Debug seeder completed successfully!');
            $this->command->info("📊 Created: {$userCount} users, {$deviceCount} devices, {$socketCount} sockets, {$profileCount} profiles, {$readingCount} readings");

            Log::info('DebugSeeder: Completed successfully', [
                'users' => $userCount,
                'devices' => $deviceCount,
                'sockets' => $socketCount,
                'profiles' => $profileCount,
                'readings' => $readingCount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Debug seeder failed: '.$e->getMessage());
            Log::error('DebugSeeder failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
