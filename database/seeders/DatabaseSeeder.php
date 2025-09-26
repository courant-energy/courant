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
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Follows strict sequential workflow to ensure proper relational integrity.
     */
    public function run(): void
    {
        DB::beginTransaction();
        
        try {
            $this->command->info('Starting comprehensive data seeding...');
            
            // Step 1: Create User
            $user = $this->createUser();
            $this->command->info("✓ Created User: {$user->first_name} {$user->last_name} (ID: {$user->id})");
            
            // Step 2: Create IoT Device linked to User
            $iotDevice = $this->createIotDevice($user);
            $this->command->info("✓ Created IoT Device: {$iotDevice->name} (ID: {$iotDevice->id}) linked to User {$user->id}");
            
            // Step 3: Create exactly two Sockets linked to IoT Device
            $sockets = $this->createSockets($iotDevice);
            $this->command->info("✓ Created {$sockets->count()} Sockets linked to IoT Device {$iotDevice->id}");
            
            // Step 4: Create a single Profile
            $profile = $this->createProfile($user);
            $this->command->info("✓ Created Profile: {$profile->name} (ID: {$profile->id}) for User {$user->id}");
            
            // Step 5: Assign Profile to only ONE Socket (respecting 1:1 constraint)
            $assignedSocket = $this->assignProfileToSocket($profile, $sockets->first());
            $this->command->info("✓ Assigned Profile {$profile->id} to Socket {$assignedSocket->id} (Socket 1 only)");
            
            // Step 6: Generate multiple Readings linked to the Profile
            $readingsCount = $this->generateReadings($assignedSocket, $profile);
            $this->command->info("✓ Generated {$readingsCount} readings for Profile {$profile->id}");
            
            // Final validation
            $this->validateSeederResults($user, $iotDevice, $sockets, $profile, $assignedSocket);
            
            DB::commit();
            
            $this->command->info('✓ Comprehensive data seeding completed successfully!');
            $this->displaySummary($user, $iotDevice, $sockets, $profile, $assignedSocket, $readingsCount);
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('✗ Seeding failed: ' . $e->getMessage());
            Log::error('DatabaseSeeder failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    /**
     * Step 1: Create a User
     */
    private function createUser(): User
    {
        return User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'email_verified_at' => now(),
            'password' => 'password123',
            'is_onboarded' => true,
        ]);
    }

    /**
     * Step 2: Create an IoT Device linked to the User
     */
    private function createIotDevice(User $user): IotDevice
    {
        return IotDevice::create([
            'name' => 'Smart Energy Monitor - Living Room',
            'device_uuid' => 'SEM-' . strtoupper(uniqid()),
            'user_id' => $user->id,
            'is_active' => true,
            'password' => 'device_password_' . rand(1000, 9999),
            'socket_count' => 2,
            'last_seen_at' => now(),
            'status' => 'online',
        ]);
    }

    /**
     * Step 3: Create exactly two Sockets linked to the IoT Device
     */
    private function createSockets(IotDevice $iotDevice)
    {
        $socket1 = Socket::create([
            'name' => 'Socket 1',
            'iot_device_id' => $iotDevice->id,
            'assigned_id' => 1,
            'state' => false,
            'profile_id' => null, // Will be assigned in step 5
        ]);

        $socket2 = Socket::create([
            'name' => 'Socket 2',
            'iot_device_id' => $iotDevice->id,
            'assigned_id' => 2,
            'state' => false,
            'profile_id' => null, // Will remain unassigned per business requirement
        ]);

        return collect([$socket1, $socket2]);
    }

    /**
     * Step 4: Create a single Profile
     */
    private function createProfile(User $user): Profile
    {
        return Profile::create([
            'name' => 'Coffee Machine Profile',
            'expected_power' => 1200,
            'max_power_threshold' => 1500,
            'min_power_threshold' => 800,
            'user_id' => $user->id,
            'notify_on_anomaly' => true,
            'alert_sensitivity' => 'medium',
            'notification_channels' => ['app', 'email'],
            'anomaly_detection_enabled' => true,
            'location' => 'Kitchen',
            'scheduling_enabled' => true,
            'turn_on_time' => Carbon::createFromTime(7, 0),
            'turn_off_time' => Carbon::createFromTime(22, 0),
            'schedule_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        ]);
    }

    /**
     * Step 5: Assign Profile to ONE Socket only (respecting 1:1 constraint)
     */
    private function assignProfileToSocket(Profile $profile, Socket $socket): Socket
    {
        $socket->update([
            'profile_id' => $profile->id,
        ]);

        return $socket->fresh();
    }

    /**
     * Step 6: Generate multiple Readings linked to the Profile
     */
    private function generateReadings(Socket $socket, Profile $profile): int
    {
        $readingsCount = 0;
        $baseTime = now()->subDays(7); // Start 7 days ago
        
        // Generate readings for the past 7 days, every 15 minutes
        for ($i = 0; $i < 672; $i++) { // 7 days * 24 hours * 4 readings per hour = 672 readings
            $readingTime = $baseTime->copy()->addMinutes($i * 15);
            
            // Simulate realistic coffee machine usage patterns
            $isBusinessHours = $readingTime->hour >= 7 && $readingTime->hour <= 21;
            $isWeekday = $readingTime->isWeekday();
            
            // Higher usage during business hours on weekdays
            $usageProbability = ($isBusinessHours && $isWeekday) ? 0.4 : 0.1;
            $isInUse = (rand(1, 100) / 100) <= $usageProbability;
            
            if ($isInUse) {
                // Active usage - higher power consumption
                $power = rand(1100, 1400); // Within expected range with some variation
                $voltage = rand(220, 240);
                $current = round($power / $voltage, 2);
                $powerFactor = rand(85, 95) / 100;
            } else {
                // Standby mode - very low power
                $power = rand(5, 15);
                $voltage = rand(220, 240);
                $current = round($power / $voltage, 2);
                $powerFactor = rand(30, 60) / 100;
            }
            
            Reading::create([
                'socket_id' => $socket->id,
                'profile_id' => $profile->id,
                'voltage' => $voltage,
                'current' => $current,
                'power' => $power,
                'energy' => round($power * 0.25, 2), // 15 minutes = 0.25 hours
                'frequency' => rand(49, 51),
                'power_factor' => $powerFactor,
                'reading_time' => $readingTime,
            ]);
            
            $readingsCount++;
        }
        
        return $readingsCount;
    }

    /**
     * Validate that all seeding requirements are met
     */
    private function validateSeederResults(
        User $user,
        IotDevice $iotDevice,
        $sockets,
        Profile $profile,
        Socket $assignedSocket
    ): void {
        // Validate User exists and is linked to IoT Device
        if (!$user->iotDevices()->where('id', $iotDevice->id)->exists()) {
            throw new \Exception('User is not properly linked to IoT Device');
        }

        // Validate IoT Device has exactly 2 sockets
        if ($iotDevice->sockets()->count() !== 2) {
            throw new \Exception('IoT Device does not have exactly 2 sockets');
        }

        // Validate only one socket is linked to the profile
        $socketsWithProfile = $sockets->filter(fn($socket) => $socket->fresh()->profile_id !== null);
        if ($socketsWithProfile->count() !== 1) {
            throw new \Exception('More than one socket is linked to a profile - violates 1:1 constraint');
        }

        // Validate the assigned socket has the correct profile
        if ($assignedSocket->fresh()->profile_id !== $profile->id) {
            throw new \Exception('Assigned socket does not have the correct profile ID');
        }

        // Validate profile has readings
        if ($profile->readings()->count() === 0) {
            throw new \Exception('Profile has no readings linked to it');
        }

        // Validate no orphaned readings
        $orphanedReadings = Reading::whereNull('profile_id')->orWhereNull('socket_id')->count();
        if ($orphanedReadings > 0) {
            throw new \Exception("Found {$orphanedReadings} orphaned readings");
        }

        $this->command->info('✓ All validation checks passed');
    }

    /**
     * Display a summary of the seeded data
     */
    private function displaySummary(
        User $user,
        IotDevice $iotDevice,
        $sockets,
        Profile $profile,
        Socket $assignedSocket,
        int $readingsCount
    ): void {
        $this->command->line('');
        $this->command->line('=== SEEDING SUMMARY ===');
        $this->command->line("User: {$user->first_name} {$user->last_name} ({$user->email})");
        $this->command->line("IoT Device: {$iotDevice->name} ({$iotDevice->device_uuid})");
        $this->command->line("Sockets: {$sockets->count()} total");
        $this->command->line("  - Socket 1: ASSIGNED to Profile '{$profile->name}'");
        $this->command->line("  - Socket 2: UNASSIGNED (available for future profiles)");
        $this->command->line("Profile: {$profile->name} (Expected Power: {$profile->expected_power}W)");
        $this->command->line("Readings: {$readingsCount} generated for the past 7 days");
        $this->command->line('');
        $this->command->line('✓ All business constraints satisfied');
        $this->command->line('✓ 1:1 Profile-to-Socket relationship maintained');
        $this->command->line('✓ No orphaned records created');
    }
}
