<?php

namespace App\Console\Commands;

use App\Models\IotDevice;
use App\Models\Profile;
use App\Models\Reading;
use App\Models\Socket;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SeedUserDevices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:user-devices {--user-id=} {--email=} {--devices=} {--list-users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed 2-3 IoT devices with profiles and 2 months of readings for an existing user';

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
            'usage_pattern' => 'morning_evening',
        ],
        'refrigerator' => [
            'name' => 'Refrigerator',
            'expected_power' => 150,
            'max_threshold' => 250,
            'min_threshold' => 80,
            'location_options' => ['Kitchen', 'Garage', 'Basement'],
            'active_power_range' => [120, 200],
            'standby_power_range' => [80, 120],
            'usage_pattern' => 'constant_cycling',
        ],
        'computer_workstation' => [
            'name' => 'Computer Workstation',
            'expected_power' => 300,
            'max_threshold' => 450,
            'min_threshold' => 100,
            'location_options' => ['Home Office', 'Study Room', 'Living Room'],
            'active_power_range' => [250, 400],
            'standby_power_range' => [10, 30],
            'usage_pattern' => 'business_hours',
        ],
        'air_conditioner' => [
            'name' => 'Air Conditioner',
            'expected_power' => 2000,
            'max_threshold' => 2500,
            'min_threshold' => 1500,
            'location_options' => ['Living Room', 'Bedroom', 'Office'],
            'active_power_range' => [1800, 2300],
            'standby_power_range' => [15, 25],
            'usage_pattern' => 'temperature_dependent',
        ],
        'microwave' => [
            'name' => 'Microwave Oven',
            'expected_power' => 1000,
            'max_threshold' => 1200,
            'min_threshold' => 800,
            'location_options' => ['Kitchen', 'Break Room'],
            'active_power_range' => [950, 1150],
            'standby_power_range' => [3, 8],
            'usage_pattern' => 'meal_times',
        ],
        'washing_machine' => [
            'name' => 'Washing Machine',
            'expected_power' => 500,
            'max_threshold' => 800,
            'min_threshold' => 200,
            'location_options' => ['Laundry Room', 'Basement', 'Garage'],
            'active_power_range' => [400, 700],
            'standby_power_range' => [2, 5],
            'usage_pattern' => 'weekly_cycles',
        ],
        'electric_heater' => [
            'name' => 'Electric Heater',
            'expected_power' => 1500,
            'max_threshold' => 2000,
            'min_threshold' => 1000,
            'location_options' => ['Living Room', 'Bedroom', 'Office', 'Basement'],
            'active_power_range' => [1300, 1800],
            'standby_power_range' => [10, 20],
            'usage_pattern' => 'temperature_dependent',
        ],
        'television' => [
            'name' => 'LED Television',
            'expected_power' => 120,
            'max_threshold' => 200,
            'min_threshold' => 50,
            'location_options' => ['Living Room', 'Bedroom', 'Family Room'],
            'active_power_range' => [100, 180],
            'standby_power_range' => [5, 15],
            'usage_pattern' => 'evening_entertainment',
        ]
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('list-users')) {
            $this->listUsers();
            return 0;
        }

        try {
            $user = $this->findUser();
            if (!$user) {
                return 1;
            }

            $deviceCount = $this->option('devices') ?: rand(2, 3);
            
            $this->info("🚀 Starting device seeding for user: {$user->email}");
            $this->info("📋 Creating {$deviceCount} devices with 2 sockets each and 2 months of readings");
            $this->info('⏳ This may take a few minutes...');

            $startTime = now();
            
            DB::beginTransaction();
            
            $this->seedDevicesForUser($user, $deviceCount);
            
            DB::commit();
            
            $endTime = now();
            $duration = $endTime->diffInSeconds($startTime);
            
            $this->info('🎉 Device seeding completed successfully!');
            $this->displaySummary($user, $duration);
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('💥 Seeding failed: ' . $e->getMessage());
            Log::error('SeedUserDevices failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return 1;
        }
    }

    /**
     * Find user by ID or email
     */
    private function findUser(): ?User
    {
        $userId = $this->option('user-id');
        $email = $this->option('email');

        if (!$userId && !$email) {
            $this->error('Please provide either --user-id or --email option');
            $this->line('Use --list-users to see available users');
            return null;
        }

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found");
                return null;
            }
        } else {
            $user = User::where('email', $email)->first();
            if (!$user) {
                $this->error("User with email {$email} not found");
                return null;
            }
        }

        $this->info("✓ Found user: {$user->first_name} {$user->last_name} ({$user->email})");
        return $user;
    }

    /**
     * List available users
     */
    private function listUsers(): void
    {
        $users = User::select('id', 'first_name', 'last_name', 'email', 'created_at')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        if ($users->isEmpty()) {
            $this->warn('No users found in the database');
            return;
        }

        $this->info('📋 Available Users (showing latest 20):');
        $this->line('');
        
        $headers = ['ID', 'Name', 'Email', 'Created'];
        $rows = $users->map(function ($user) {
            return [
                $user->id,
                "{$user->first_name} {$user->last_name}",
                $user->email,
                $user->created_at->format('Y-m-d H:i')
            ];
        })->toArray();

        $this->table($headers, $rows);
        
        $this->line('');
        $this->info('Usage examples:');
        $this->line('  php artisan seed:user-devices --user-id=1');
        $this->line('  php artisan seed:user-devices --email=user@example.com');
        $this->line('  php artisan seed:user-devices --user-id=1 --devices=3');
    }

    /**
     * Seed devices for the selected user
     */
    private function seedDevicesForUser(User $user, int $deviceCount): void
    {
        for ($deviceIndex = 1; $deviceIndex <= $deviceCount; $deviceIndex++) {
            $this->line("Creating device {$deviceIndex}/{$deviceCount}...");
            
            $device = $this->createIotDevice($user, $deviceIndex);
            $this->totalDevices++;
            
            // Create exactly 2 sockets per device
            for ($socketIndex = 1; $socketIndex <= 2; $socketIndex++) {
                $socket = $this->createSocket($device, $socketIndex);
                $this->totalSockets++;
                
                $profile = $this->createProfile($user, $socket, $deviceIndex, $socketIndex);
                $this->totalProfiles++;
                
                $this->assignProfileToSocket($profile, $socket);
                
                $readingsCount = $this->generateReadings($socket, $profile);
                $this->totalReadings += $readingsCount;
                
                $this->line("  ✓ Socket {$socketIndex}: {$profile->name} ({$readingsCount} readings)");
            }
        }
    }

    /**
     * Create an IoT Device
     */
    private function createIotDevice(User $user, int $deviceIndex): IotDevice
    {
        $deviceTypes = [
            'Smart Energy Monitor Pro',
            'Power Management Hub',
            'Energy Tracker Elite',
            'Smart Socket Controller',
            'Energy Analytics Device',
            'Wireless Power Monitor',
            'Smart Energy Gateway'
        ];
        
        $locations = ['Living Room', 'Kitchen', 'Office', 'Bedroom', 'Garage', 'Basement', 'Utility Room'];
        
        $device = IotDevice::create([
            'name' => $deviceTypes[array_rand($deviceTypes)] . ' - ' . $locations[array_rand($locations)],
            'device_uuid' => 'SEED-' . strtoupper(uniqid()) . '-' . str_pad($deviceIndex, 3, '0', STR_PAD_LEFT),
            'user_id' => $user->id,
            'is_active' => rand(0, 100) < 95, // 95% active
            'password' => 'device_pwd_' . rand(1000, 9999),
            'socket_count' => 2,
            'last_seen_at' => now()->subMinutes(rand(0, 60)), // Last seen within 1 hour
            'status' => rand(0, 100) < 90 ? 'online' : 'offline',
        ]);

        return $device;
    }

    /**
     * Create a Socket
     */
    private function createSocket(IotDevice $device, int $socketIndex): Socket
    {
        return Socket::create([
            'name' => "Socket {$socketIndex}",
            'iot_device_id' => $device->id,
            'assigned_id' => $socketIndex,
            'state' => rand(0, 100) < 25, // 25% chance of being on
            'profile_id' => null, // Will be assigned after profile creation
        ]);
    }

    /**
     * Create a Profile with varied appliance types
     */
    private function createProfile(User $user, Socket $socket, int $deviceIndex, int $socketIndex): Profile
    {
        // Select a random appliance type
        $applianceKey = array_rand($this->applianceTypes);
        $appliance = $this->applianceTypes[$applianceKey];
        
        $location = $appliance['location_options'][array_rand($appliance['location_options'])];
        
        // Create unique profile name
        $profileName = $appliance['name'] . " - {$location} (D{$deviceIndex}S{$socketIndex})";
        
        // Scheduling settings with variation
        $hasScheduling = rand(0, 100) < 35; // 35% have scheduling enabled
        
        $profile = Profile::create([
            'name' => $profileName,
            'expected_power' => $appliance['expected_power'],
            'max_power_threshold' => $appliance['max_threshold'],
            'min_power_threshold' => $appliance['min_threshold'],
            'user_id' => $user->id,
            'notify_on_anomaly' => rand(0, 100) < 80, // 80% have notifications enabled
            'alert_sensitivity' => ['low', 'medium', 'high'][rand(0, 2)],
            'notification_channels' => $this->getRandomNotificationChannels(),
            'anomaly_detection_enabled' => rand(0, 100) < 85, // 85% have anomaly detection
            'location' => $location,
            'scheduling_enabled' => $hasScheduling,
            'turn_on_time' => $hasScheduling ? Carbon::createFromTime(rand(6, 9), rand(0, 59)) : null,
            'turn_off_time' => $hasScheduling ? Carbon::createFromTime(rand(18, 23), rand(0, 59)) : null,
            'schedule_days' => $hasScheduling ? $this->getRandomScheduleDays() : null,
        ]);

        return $profile;
    }

    /**
     * Assign Profile to Socket
     */
    private function assignProfileToSocket(Profile $profile, Socket $socket): void
    {
        $socket->update(['profile_id' => $profile->id]);
    }

    /**
     * Generate 2 months of readings with realistic patterns
     */
    private function generateReadings(Socket $socket, Profile $profile): int
    {
        $readingsCount = 0;
        $baseTime = now()->subDays(60); // Start 60 days ago
        
        // Determine appliance type from profile name
        $applianceType = $this->determineApplianceType($profile->name);
        $appliance = $this->applianceTypes[$applianceType];
        
        // Generate readings every 30 minutes for 60 days (2880 readings)
        $intervalMinutes = 30;
        $totalReadings = 60 * 24 * (60 / $intervalMinutes); // 2880 readings
        
        // Batch insert for better performance
        $batch = [];
        $batchSize = 100;
        
        for ($i = 0; $i < $totalReadings; $i++) {
            $readingTime = $baseTime->copy()->addMinutes($i * $intervalMinutes);
            
            $isInUse = $this->calculateUsageProbability($readingTime, $appliance['usage_pattern']);
            
            if ($isInUse) {
                $power = rand($appliance['active_power_range'][0], $appliance['active_power_range'][1]);
                // Add some realistic variations
                $power += rand(-50, 50);
                $power = max($power, 0); // Ensure non-negative
            } else {
                $power = rand($appliance['standby_power_range'][0], $appliance['standby_power_range'][1]);
            }
            
            $voltage = rand(220, 240) + (rand(0, 100) / 100); // More realistic voltage
            $current = round($power / $voltage, 2);
            $powerFactor = $isInUse ? rand(85, 95) / 100 : rand(30, 70) / 100;
            
            $batch[] = [
                'socket_id' => $socket->id,
                'profile_id' => $profile->id,
                'voltage' => $voltage,
                'current' => $current,
                'power' => $power,
                'energy' => round($power * ($intervalMinutes / 60), 2),
                'frequency' => rand(4900, 5100) / 100, // 49-51 Hz
                'power_factor' => $powerFactor,
                'reading_time' => $readingTime,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $readingsCount++;
            
            // Insert in batches for better performance
            if (count($batch) >= $batchSize || $i === $totalReadings - 1) {
                Reading::insert($batch);
                $batch = [];
            }
        }
        
        return $readingsCount;
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
        
        return empty($selectedChannels) ? ['app'] : $selectedChannels;
    }

    /**
     * Helper: Get random schedule days
     */
    private function getRandomScheduleDays(): array
    {
        $allDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $selectedDays = [];
        
        $isBusinessAppliance = rand(0, 100) < 60;
        
        if ($isBusinessAppliance) {
            return ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        } else {
            foreach ($allDays as $day) {
                if (rand(0, 100) < 70) {
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
                $probability = 0.3 + (sin(($hour / 24) * 2 * pi()) * 0.2);
                break;
                
            case 'business_hours':
                $probability = ($isWeekday && $hour >= 9 && $hour <= 17) ? 0.8 : 0.1;
                break;
                
            case 'temperature_dependent':
                $probability = ($hour >= 11 && $hour <= 15) ? 0.9 : (($hour >= 19 && $hour <= 23) ? 0.6 : 0.2);
                break;
                
            case 'meal_times':
                $probability = ($hour >= 7 && $hour <= 9) ? 0.5 : (($hour >= 12 && $hour <= 14) ? 0.6 : (($hour >= 18 && $hour <= 20) ? 0.7 : 0.05));
                break;
                
            case 'weekly_cycles':
                $probability = ($isWeekday && ($time->dayOfWeek == 1 || $time->dayOfWeek == 3 || $time->dayOfWeek == 6)) ? 0.3 : 0.02;
                break;
                
            case 'evening_entertainment':
                $probability = ($hour >= 18 && $hour <= 23) ? 0.8 : (($hour >= 14 && $hour <= 17) ? 0.3 : 0.05);
                break;
                
            default:
                $probability = 0.2;
        }
        
        return (rand(0, 100) / 100) <= $probability;
    }

    /**
     * Display seeding summary
     */
    private function displaySummary(User $user, int $duration): void
    {
        $this->line('');
        $this->line('🎯 === DEVICE SEEDING SUMMARY ===');
        $this->line("👤 User: {$user->first_name} {$user->last_name} ({$user->email})");
        $this->line("📱 IoT Devices Created: {$this->totalDevices}");
        $this->line("🔌 Sockets Created: {$this->totalSockets}");
        $this->line("📋 Profiles Created: {$this->totalProfiles}");
        $this->line("📊 Readings Generated: " . number_format($this->totalReadings));
        $this->line("⏱️  Processing Time: {$duration} seconds");
        $this->line('');
        $this->line('✅ Data Generated:');
        $this->line("   • Each device has 2 sockets with unique profiles");
        $this->line("   • 2 months of realistic readings (30-minute intervals)");
        $this->line("   • Varied appliance types with realistic usage patterns");
        $this->line("   • Some profiles have scheduling configured");
        $this->line('');
        
        $avgReadingsPerProfile = round($this->totalReadings / $this->totalProfiles);
        $this->line('📈 Statistics:');
        $this->line("   • Readings per profile: {$avgReadingsPerProfile}");
        $this->line("   • Total data points: " . number_format($this->totalReadings));
        $this->line("   • Data period: 60 days (2 months)");
        $this->line("   • Reading interval: 30 minutes");
    }
}
