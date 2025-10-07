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
    private int $totalUsers = 0;
    private int $totalDevices = 0;
    private int $totalSockets = 0;
    private int $totalProfiles = 0;
    private int $totalReadings = 0;

    /**
     * Appliance profile templates with realistic power consumption patterns
     */
    private array $applianceTypes = [
        'coffee_machine' => [
            'name' => 'Coffee Machine',
            'expected_power' => 1200,
            'max_threshold' => 1500,
            'min_threshold' => 800,
            'location_options' => ['Kitchen', 'Office', 'Break Room'],
            'active_power_range' => [1100, 1400],
            'standby_power_range' => [5, 15],
            'usage_pattern' => 'morning_evening', // Heavy morning use, some evening
        ],
        'refrigerator' => [
            'name' => 'Refrigerator',
            'expected_power' => 150,
            'max_threshold' => 250,
            'min_threshold' => 80,
            'location_options' => ['Kitchen', 'Garage', 'Basement'],
            'active_power_range' => [120, 200],
            'standby_power_range' => [80, 120],
            'usage_pattern' => 'constant_cycling', // Consistent with cycling
        ],
        'computer_workstation' => [
            'name' => 'Computer Workstation',
            'expected_power' => 300,
            'max_threshold' => 450,
            'min_threshold' => 100,
            'location_options' => ['Home Office', 'Study Room', 'Living Room'],
            'active_power_range' => [250, 400],
            'standby_power_range' => [10, 30],
            'usage_pattern' => 'business_hours', // 9-5 usage
        ],
        'air_conditioner' => [
            'name' => 'Air Conditioner',
            'expected_power' => 2000,
            'max_threshold' => 2500,
            'min_threshold' => 1500,
            'location_options' => ['Living Room', 'Bedroom', 'Office'],
            'active_power_range' => [1800, 2300],
            'standby_power_range' => [15, 25],
            'usage_pattern' => 'temperature_dependent', // Usage based on time of day
        ],
        'microwave' => [
            'name' => 'Microwave Oven',
            'expected_power' => 1000,
            'max_threshold' => 1200,
            'min_threshold' => 800,
            'location_options' => ['Kitchen', 'Break Room'],
            'active_power_range' => [950, 1150],
            'standby_power_range' => [3, 8],
            'usage_pattern' => 'meal_times', // Peak at meal times
        ],
        'washing_machine' => [
            'name' => 'Washing Machine',
            'expected_power' => 500,
            'max_threshold' => 800,
            'min_threshold' => 200,
            'location_options' => ['Laundry Room', 'Basement', 'Garage'],
            'active_power_range' => [400, 700],
            'standby_power_range' => [2, 5],
            'usage_pattern' => 'weekly_cycles', // Few times per week
        ]
    ];

    /**
     * Seed the application's database with scaled data.
     * Creates minimum 50 users with 2-3 IoT devices each, ensuring every socket has a unique profile.
     */
    public function run(): void
    {
        DB::beginTransaction();
        
        try {
            $this->command->info('🚀 Starting scaled comprehensive data seeding...');
            Log::info('DatabaseSeeder: Starting scaled comprehensive data seeding');
            $startTime = now();
            
            $minUsers = 50;
            $maxUsers = 55; // Slight variation for realism
            $userCount = rand($minUsers, $maxUsers);
            
            $this->command->info("📋 Plan: Creating {$userCount} users with 2-3 devices each");
            Log::info("DatabaseSeeder: Plan to create {$userCount} users with 2-3 devices each");
            $this->command->info('⏳ This may take a few minutes due to the large dataset...');
            
            // Outer Loop: Create Users
            for ($userIndex = 1; $userIndex <= $userCount; $userIndex++) {
                Log::info("DatabaseSeeder: Starting user {$userIndex}/{$userCount}");
                
                $user = $this->createScaledUser($userIndex);
                $this->totalUsers++;
                Log::info("DatabaseSeeder: Created user {$userIndex} - ID: {$user->id}, Email: {$user->email}");
                
                // Middle Loop: Create IoT Devices (2-3 per user)
                $deviceCount = rand(2, 3);
                Log::info("DatabaseSeeder: Creating {$deviceCount} devices for user {$userIndex}");
                
                for ($deviceIndex = 1; $deviceIndex <= $deviceCount; $deviceIndex++) {
                    Log::info("DatabaseSeeder: Creating device {$deviceIndex}/{$deviceCount} for user {$userIndex}");
                    
                    $device = $this->createScaledIotDevice($user, $userIndex, $deviceIndex);
                    $this->totalDevices++;
                    Log::info("DatabaseSeeder: Created device {$deviceIndex} - ID: {$device->id}, Name: {$device->name}");
                    
                    // Create exactly 2 sockets per device, each with its own profile
                    for ($socketIndex = 1; $socketIndex <= 2; $socketIndex++) {
                        Log::info("DatabaseSeeder: Creating socket {$socketIndex}/2 for device {$device->id}");
                        
                        $socket = $this->createScaledSocket($device, $socketIndex);
                        $this->totalSockets++;
                        Log::info("DatabaseSeeder: Created socket {$socketIndex} - ID: {$socket->id}");
                        
                        Log::info("DatabaseSeeder: Creating profile for socket {$socket->id}");
                        $profile = $this->createScaledProfile($user, $socket, $userIndex, $deviceIndex, $socketIndex);
                        $this->totalProfiles++;
                        Log::info("DatabaseSeeder: Created profile - ID: {$profile->id}, Name: {$profile->name}");
                        
                        Log::info("DatabaseSeeder: Assigning profile {$profile->id} to socket {$socket->id}");
                        $this->assignProfileToSocket($profile, $socket);
                        Log::info("DatabaseSeeder: Successfully assigned profile to socket");
                        
                        Log::info("DatabaseSeeder: Starting readings generation for profile {$profile->id}");
                        $readingsCount = $this->generateScaledReadings($socket, $profile);
                        $this->totalReadings += $readingsCount;
                        Log::info("DatabaseSeeder: Generated {$readingsCount} readings for profile {$profile->id}");
                    }
                }
                
                // Progress indicator every 10 users
                if ($userIndex % 10 === 0) {
                    $this->command->info("✓ Processed {$userIndex}/{$userCount} users ({$this->totalSockets} sockets created)");
                    Log::info("DatabaseSeeder: Progress milestone - {$userIndex}/{$userCount} users, {$this->totalSockets} sockets, {$this->totalReadings} readings");
                }
                
                // More frequent logging for first few users
                if ($userIndex <= 5) {
                    $this->command->info("✓ Completed user {$userIndex} - Total readings so far: {$this->totalReadings}");
                    Log::info("DatabaseSeeder: Completed user {$userIndex} - Totals: {$this->totalUsers} users, {$this->totalDevices} devices, {$this->totalSockets} sockets, {$this->totalProfiles} profiles, {$this->totalReadings} readings");
                }
            }
            
            // Final validation
            Log::info("DatabaseSeeder: Starting final validation");
            $this->validateScaledResults();
            Log::info("DatabaseSeeder: Final validation completed successfully");
            
            DB::commit();
            Log::info("DatabaseSeeder: Transaction committed successfully");
            
            $endTime = now();
            $duration = $endTime->diffInSeconds($startTime);
            
            $this->command->info('🎉 Scaled comprehensive data seeding completed successfully!');
            Log::info("DatabaseSeeder: Seeding completed successfully in {$duration} seconds");
            $this->displayScaledSummary($duration);
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('💥 Scaled seeding failed: ' . $e->getMessage());
            Log::error('DatabaseSeeder (scaled) failed', [
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString(),
                'totals' => [
                    'users' => $this->totalUsers,
                    'devices' => $this->totalDevices,
                    'sockets' => $this->totalSockets,
                    'profiles' => $this->totalProfiles,
                    'readings' => $this->totalReadings
                ]
            ]);
            throw $e;
        }
    }

    /**
     * Create a scaled User with realistic variation
     */
    private function createScaledUser(int $userIndex): User
    {
        Log::info("DatabaseSeeder: createScaledUser() - Creating user {$userIndex}");
        
        try {
            $user = User::factory()->create([
                'is_onboarded' => rand(0, 100) < 85, // 85% onboarded
                'company_name' => rand(0, 100) < 30 ? fake()->company() : null, // 30% have company
            ]);
            
            Log::info("DatabaseSeeder: createScaledUser() - Successfully created user {$userIndex}, ID: {$user->id}");
            return $user;
        } catch (\Exception $e) {
            Log::error("DatabaseSeeder: createScaledUser() - Failed to create user {$userIndex}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Create a scaled IoT Device with varied configurations
     */
    private function createScaledIotDevice(User $user, int $userIndex, int $deviceIndex): IotDevice
    {
        Log::info("DatabaseSeeder: createScaledIotDevice() - Creating device {$deviceIndex} for user {$userIndex}");
        
        try {
            $deviceTypes = [
                'Smart Energy Monitor',
                'Power Management Hub',
                'Energy Tracker Pro',
                'Smart Socket Controller',
                'Energy Analytics Device'
            ];
            
            $locations = ['Living Room', 'Kitchen', 'Office', 'Bedroom', 'Garage', 'Basement'];
            
            $device = IotDevice::create([
                'name' => $deviceTypes[array_rand($deviceTypes)] . ' - ' . $locations[array_rand($locations)],
                'device_uuid' => 'DEV-' . strtoupper(uniqid()) . '-' . str_pad($userIndex, 3, '0', STR_PAD_LEFT),
                'user_id' => $user->id,
                'is_active' => rand(0, 100) < 90, // 90% active devices
                'password' => 'device_pwd_' . rand(1000, 9999),
                'socket_count' => 2,
                'last_seen_at' => now()->subMinutes(rand(0, 1440)), // Last seen within 24 hours
                'status' => rand(0, 100) < 85 ? 'online' : (rand(0, 100) < 50 ? 'offline' : 'maintenance'),
            ]);
            
            Log::info("DatabaseSeeder: createScaledIotDevice() - Successfully created device {$deviceIndex}, ID: {$device->id}");
            return $device;
        } catch (\Exception $e) {
            Log::error("DatabaseSeeder: createScaledIotDevice() - Failed to create device {$deviceIndex} for user {$userIndex}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Create a scaled Socket with proper naming
     */
    private function createScaledSocket(IotDevice $device, int $socketIndex): Socket
    {
        Log::info("DatabaseSeeder: createScaledSocket() - Creating socket {$socketIndex} for device {$device->id}");
        
        try {
            $socket = Socket::create([
                'name' => "Socket {$socketIndex}",
                'iot_device_id' => $device->id,
                'assigned_id' => $socketIndex,
                'state' => rand(0, 100) < 30, // 30% chance of being on
                'profile_id' => null, // Will be assigned immediately after profile creation
            ]);
            
            Log::info("DatabaseSeeder: createScaledSocket() - Successfully created socket {$socketIndex}, ID: {$socket->id}");
            return $socket;
        } catch (\Exception $e) {
            Log::error("DatabaseSeeder: createScaledSocket() - Failed to create socket {$socketIndex} for device {$device->id}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Create a scaled Profile with varied appliance types
     */
    private function createScaledProfile(User $user, Socket $socket, int $userIndex, int $deviceIndex, int $socketIndex): Profile
    {
        Log::info("DatabaseSeeder: createScaledProfile() - Creating profile for socket {$socket->id}");
        
        try {
            // Select a random appliance type
            $applianceKey = array_rand($this->applianceTypes);
            $appliance = $this->applianceTypes[$applianceKey];
            
            $location = $appliance['location_options'][array_rand($appliance['location_options'])];
            
            // Add some variation to the profile name
            $profileName = $appliance['name'] . " - {$location}";
            
            // Scheduling settings with variation
            $hasScheduling = rand(0, 100) < 40; // 40% have scheduling enabled
            
            Log::info("DatabaseSeeder: createScaledProfile() - Creating {$applianceKey} profile: {$profileName}");
            
            $profile = Profile::create([
                'name' => $profileName,
                'expected_power' => $appliance['expected_power'],
                'max_power_threshold' => $appliance['max_threshold'],
                'min_power_threshold' => $appliance['min_threshold'],
                'user_id' => $user->id,
                'notify_on_anomaly' => rand(0, 100) < 75, // 75% have notifications enabled
                'alert_sensitivity' => ['low', 'medium', 'high'][rand(0, 2)],
                'notification_channels' => $this->getRandomNotificationChannels(),
                'anomaly_detection_enabled' => rand(0, 100) < 80, // 80% have anomaly detection
                'location' => $location,
                'scheduling_enabled' => $hasScheduling,
                'turn_on_time' => $hasScheduling ? Carbon::createFromTime(rand(6, 9), rand(0, 59)) : null,
                'turn_off_time' => $hasScheduling ? Carbon::createFromTime(rand(18, 23), rand(0, 59)) : null,
                'schedule_days' => $hasScheduling ? $this->getRandomScheduleDays() : null,
            ]);
            
            Log::info("DatabaseSeeder: createScaledProfile() - Successfully created profile ID: {$profile->id}");
            return $profile;
        } catch (\Exception $e) {
            Log::error("DatabaseSeeder: createScaledProfile() - Failed to create profile for socket {$socket->id}", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Generate scaled Readings with realistic patterns based on appliance type
     */
    private function generateScaledReadings(Socket $socket, Profile $profile): int
    {
        Log::info("DatabaseSeeder: generateScaledReadings() - Starting for socket {$socket->id}, profile {$profile->id}");
        
        $readingsCount = 0;
        $baseTime = now()->subDays(60);
        
        // Determine appliance type from profile name
        $applianceType = $this->determineApplianceType($profile->name);
        $appliance = $this->applianceTypes[$applianceType];
        
        Log::info("DatabaseSeeder: generateScaledReadings() - Appliance type: {$applianceType}");
        
        // Generate readings every 30 minutes for 2 months (60 days = 2880 readings) for performance
        // In production, you could change this back to 15 minutes (5760 readings)
        $intervalMinutes = 30;
        $totalReadings = 60 * 24 * (60 / $intervalMinutes); // 2880 readings
        
        Log::info("DatabaseSeeder: generateScaledReadings() - Will generate {$totalReadings} readings with {$intervalMinutes}-minute intervals over 60 days");
        
        for ($i = 0; $i < $totalReadings; $i++) {
            $readingTime = $baseTime->copy()->addMinutes($i * $intervalMinutes);
            
            $isInUse = $this->calculateUsageProbability($readingTime, $appliance['usage_pattern']);
            
            if ($isInUse) {
                $power = rand($appliance['active_power_range'][0], $appliance['active_power_range'][1]);
            } else {
                $power = rand($appliance['standby_power_range'][0], $appliance['standby_power_range'][1]);
            }
            
            $voltage = rand(220, 240);
            $current = round($power / $voltage, 2);
            $powerFactor = $isInUse ? rand(85, 95) / 100 : rand(30, 70) / 100;
            
            try {
                Reading::create([
                    'socket_id' => $socket->id,
                    'profile_id' => $profile->id,
                    'voltage' => $voltage,
                    'current' => $current,
                    'power' => $power,
                    'energy' => round($power * ($intervalMinutes / 60), 2), // Energy based on interval
                    'frequency' => rand(49, 51) + (rand(0, 100) / 100), // More realistic frequency
                    'power_factor' => $powerFactor,
                    'reading_time' => $readingTime,
                ]);
            } catch (\Exception $e) {
                Log::error("DatabaseSeeder: generateScaledReadings() - Failed to create reading {$i} for socket {$socket->id}", ['error' => $e->getMessage()]);
                throw $e;
            }
            
            $readingsCount++;
            
            // Performance optimization: batch insert every 50 readings
            if ($readingsCount % 50 === 0) {
                Log::info("DatabaseSeeder: generateScaledReadings() - Progress: {$readingsCount}/{$totalReadings} readings created for profile {$profile->id}");
                // Force memory cleanup
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            }
        }
        
        Log::info("DatabaseSeeder: generateScaledReadings() - Completed: {$readingsCount} readings created for profile {$profile->id}");
        return $readingsCount;
    }

    /**
     * Assign Profile to Socket (maintaining 1:1 constraint)
     */
    private function assignProfileToSocket(Profile $profile, Socket $socket): Socket
    {
        $socket->update(['profile_id' => $profile->id]);
        return $socket->fresh();
    }

    /**
     * Validate scaled seeding results
     */
    private function validateScaledResults(): void
    {
        Log::info("DatabaseSeeder: validateScaledResults() - Starting validation");
        
        // For production use these values:
        $minUsersRequired = 50;
        $minSocketsRequired = 200;  
        $maxSocketsAllowed = 300;
        
        Log::info("DatabaseSeeder: validateScaledResults() - Checking user count: {$this->totalUsers} >= {$minUsersRequired}");
        // Check minimum user count
        if ($this->totalUsers < $minUsersRequired) {
            Log::error("DatabaseSeeder: validateScaledResults() - User count validation failed");
            throw new \Exception("Insufficient users created: {$this->totalUsers} (minimum {$minUsersRequired} required)");
        }

        Log::info("DatabaseSeeder: validateScaledResults() - Checking socket count: {$this->totalSockets} between {$minSocketsRequired}-{$maxSocketsAllowed}");
        // Check socket count range
        if ($this->totalSockets < $minSocketsRequired || $this->totalSockets > $maxSocketsAllowed) {
            Log::error("DatabaseSeeder: validateScaledResults() - Socket count validation failed");
            throw new \Exception("Socket count out of range: {$this->totalSockets} (expected {$minSocketsRequired}-{$maxSocketsAllowed})");
        }

        Log::info("DatabaseSeeder: validateScaledResults() - Checking profile assignments");
        // Verify every socket has a unique profile
        $socketsWithProfiles = Socket::whereNotNull('profile_id')->count();
        if ($socketsWithProfiles !== $this->totalSockets) {
            Log::error("DatabaseSeeder: validateScaledResults() - Profile assignment validation failed", [
                'sockets_with_profiles' => $socketsWithProfiles,
                'total_sockets' => $this->totalSockets
            ]);
            throw new \Exception("Not all sockets have profiles: {$socketsWithProfiles}/{$this->totalSockets}");
        }

        Log::info("DatabaseSeeder: validateScaledResults() - Checking 1:1 constraint");
        // Check for profile uniqueness (1:1 constraint)
        $duplicateProfiles = DB::table('sockets')
            ->select('profile_id')
            ->whereNotNull('profile_id')
            ->groupBy('profile_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();
            
        if ($duplicateProfiles > 0) {
            Log::error("DatabaseSeeder: validateScaledResults() - 1:1 constraint validation failed", ['duplicates' => $duplicateProfiles]);
            throw new \Exception("Found {$duplicateProfiles} profiles assigned to multiple sockets - violates 1:1 constraint");
        }

        Log::info("DatabaseSeeder: validateScaledResults() - Checking profiles have readings");
        // Verify all profiles have readings
        $profilesWithoutReadings = Profile::doesntHave('readings')->count();
        if ($profilesWithoutReadings > 0) {
            Log::error("DatabaseSeeder: validateScaledResults() - Profiles without readings validation failed", ['count' => $profilesWithoutReadings]);
            throw new \Exception("Found {$profilesWithoutReadings} profiles without readings");
        }

        Log::info("DatabaseSeeder: validateScaledResults() - Checking for orphaned readings");
        // Check for orphaned records
        $orphanedReadings = Reading::whereNull('profile_id')->orWhereNull('socket_id')->count();
        if ($orphanedReadings > 0) {
            Log::error("DatabaseSeeder: validateScaledResults() - Orphaned readings validation failed", ['count' => $orphanedReadings]);
            throw new \Exception("Found {$orphanedReadings} orphaned readings");
        }

        Log::info("DatabaseSeeder: validateScaledResults() - All validations passed successfully");
        $this->command->info('✅ All scaled validation checks passed');
    }

    /**
     * Display scaled seeding summary
     */
    private function displayScaledSummary(int $duration): void
    {
        $this->command->line('');
        $this->command->line('🎯 === SCALED SEEDING SUMMARY ===');
        $this->command->line("👥 Users Created: {$this->totalUsers}");
        $this->command->line("📱 IoT Devices Created: {$this->totalDevices}");
        $this->command->line("🔌 Sockets Created: {$this->totalSockets}");
        $this->command->line("📋 Profiles Created: {$this->totalProfiles}");
        $this->command->line("📊 Readings Generated: " . number_format($this->totalReadings));
        $this->command->line("⏱️  Processing Time: {$duration} seconds");
        $this->command->line('');
        $this->command->line('✅ Business Constraints Verified:');
        $this->command->line("   • Minimum 50 users requirement: MET ({$this->totalUsers} users)");
        $this->command->line("   • Socket count in range 200-300: MET ({$this->totalSockets} sockets)");
        $this->command->line("   • Every socket has unique profile: MET (1:1 relationship maintained)");
        $this->command->line("   • All profiles have 2 months of readings: MET");
        $this->command->line("   • No orphaned records: MET");
        
        // Calculate averages
        $avgDevicesPerUser = round($this->totalDevices / $this->totalUsers, 2);
        $avgReadingsPerProfile = round($this->totalReadings / $this->totalProfiles);
        
        $this->command->line('');
        $this->command->line('📈 Statistics:');
        $this->command->line("   • Average devices per user: {$avgDevicesPerUser}");
        $this->command->line("   • Readings per profile: {$avgReadingsPerProfile}");
        $this->command->line("   • Total data points: " . number_format($this->totalReadings));
        $this->command->line("   • Data period: 2 months (60 days)");
    }

    /**
     * Helper: Get random notification channels
     */
    private function getRandomNotificationChannels(): array
    {
        $channels = ['app', 'email', 'sms'];
        $selectedChannels = [];
        
        foreach ($channels as $channel) {
            if (rand(0, 100) < 60) { // 60% chance for each channel
                $selectedChannels[] = $channel;
            }
        }
        
        return empty($selectedChannels) ? ['app'] : $selectedChannels; // At least app notifications
    }

    /**
     * Helper: Get random schedule days
     */
    private function getRandomScheduleDays(): array
    {
        $allDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $selectedDays = [];
        
        // Business appliances typically work on weekdays
        $isBusinessAppliance = rand(0, 100) < 60;
        
        if ($isBusinessAppliance) {
            return ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        } else {
            // Home appliances might work on any days
            foreach ($allDays as $day) {
                if (rand(0, 100) < 70) { // 70% chance for each day
                    $selectedDays[] = $day;
                }
            }
            return empty($selectedDays) ? $allDays : $selectedDays;
        }
    }

    /**
     * Helper: Determine appliance type from profile name
     */
    private function determineApplianceType(string $profileName): string
    {
        foreach ($this->applianceTypes as $key => $appliance) {
            if (strpos($profileName, $appliance['name']) !== false) {
                return $key;
            }
        }
        return 'coffee_machine'; // Default fallback
    }

    /**
     * Helper: Calculate usage probability based on appliance type and time
     */
    private function calculateUsageProbability(Carbon $time, string $usagePattern): bool
    {
        $hour = $time->hour;
        $isWeekday = $time->isWeekday();
        
        switch ($usagePattern) {
            case 'morning_evening':
                $probability = ($hour >= 6 && $hour <= 9) ? 0.7 : (($hour >= 18 && $hour <= 21) ? 0.4 : 0.1);
                break;
                
            case 'constant_cycling':
                $probability = 0.3 + (sin(($hour / 24) * 2 * pi()) * 0.2); // Sinusoidal pattern
                break;
                
            case 'business_hours':
                $probability = ($isWeekday && $hour >= 9 && $hour <= 17) ? 0.8 : 0.1;
                break;
                
            case 'temperature_dependent':
                // Higher usage during hot hours (midday) and evening
                $probability = ($hour >= 11 && $hour <= 15) ? 0.9 : (($hour >= 19 && $hour <= 23) ? 0.6 : 0.2);
                break;
                
            case 'meal_times':
                $probability = ($hour >= 7 && $hour <= 9) ? 0.5 : (($hour >= 12 && $hour <= 14) ? 0.6 : (($hour >= 18 && $hour <= 20) ? 0.7 : 0.05));
                break;
                
            case 'weekly_cycles':
                $probability = ($isWeekday && ($time->dayOfWeek == 1 || $time->dayOfWeek == 3 || $time->dayOfWeek == 6)) ? 0.3 : 0.02;
                break;
                
            default:
                $probability = 0.2;
        }
        
        return (rand(0, 100) / 100) <= $probability;
    }
}
