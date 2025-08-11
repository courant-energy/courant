<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Profile;
use App\Models\IotDevice;
use App\Models\Socket;
use Illuminate\Console\Command;

class SetupSchedulingDemo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:scheduling-setup {--clean : Clean up existing demo data first}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up demo data for socket scheduling feature';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('clean')) {
            $this->info('Cleaning up existing demo data...');
            Profile::where('name', 'LIKE', 'Demo %')->delete();
            IotDevice::where('name', 'LIKE', 'Demo %')->delete();
        }

        $this->info('Setting up socket scheduling demo...');

        // Get or create first user
        $user = User::first();
        if (!$user) {
            $this->error('No users found. Please create a user first.');
            return;
        }

        // Create demo IoT device
        $device = IotDevice::create([
            'name' => 'Demo Smart Socket',
            'device_uuid' => \Illuminate\Support\Str::uuid(),
            'password' => 'demo-password',
            'user_id' => $user->id,
            'is_active' => true
        ]);

        // Create demo profile with scheduling
        $profile = Profile::create([
            'name' => 'Demo Office Equipment',
            'expected_power' => 150,
            'max_power_threshold' => 200,
            'min_power_threshold' => 50,
            'user_id' => $user->id,
            'location' => 'Office',
            'scheduling_enabled' => true,
            'turn_on_time' => '08:00',
            'turn_off_time' => '18:00',
            'schedule_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'notify_on_anomaly' => true,
            'alert_sensitivity' => 'medium',
            'notification_channels' => ['app'],
            'anomaly_detection_enabled' => true
        ]);

        // Create demo socket linked to profile
        $socket = Socket::create([
            'name' => 'Office Desktop Computer',
            'iot_device_id' => $device->id,
            'profile_id' => $profile->id,
            'assigned_id' => 1,
            'state' => false
        ]);

        // Create another profile for night security lighting
        $nightProfile = Profile::create([
            'name' => 'Demo Security Lighting',
            'expected_power' => 60,
            'max_power_threshold' => 80,
            'min_power_threshold' => 40,
            'user_id' => $user->id,
            'location' => 'Outdoor',
            'scheduling_enabled' => true,
            'turn_on_time' => '22:00',
            'turn_off_time' => '06:00',
            'schedule_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            'notify_on_anomaly' => true,
            'alert_sensitivity' => 'high',
            'notification_channels' => ['app'],
            'anomaly_detection_enabled' => true
        ]);

        $this->info('Demo data created successfully!');
        $this->newLine();
        
        $this->info('Created profiles:');
        $this->line("1. {$profile->name} - Weekdays 8AM to 6PM");
        $this->line("2. {$nightProfile->name} - Every night 10PM to 6AM");
        
        $this->newLine();
        $this->info('You can now:');
        $this->line('1. Run "php artisan schedules:process --dry-run" to see what would happen');
        $this->line('2. Run "php artisan schedules:process" to apply schedules');
        $this->line('3. View the profiles in the Filament admin panel');
        
        $this->newLine();
        $this->info('Current schedule status:');
        
        foreach ([$profile, $nightProfile] as $p) {
            $shouldBeOn = $p->shouldBeOn();
            $nextChange = $p->getNextScheduleChange();
            
            $this->line("- {$p->name}: Should be " . ($shouldBeOn ? 'ON' : 'OFF'));
            if ($nextChange) {
                $this->line("  Next change: {$nextChange['action']} at {$nextChange['time']->format('Y-m-d H:i')} ({$nextChange['human_readable']})");
            }
        }
    }
}
