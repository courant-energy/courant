<?php

<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Socket;
use App\Models\User;
use App\Models\IotDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class SocketSchedulingTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_determines_correct_on_state_during_schedule()
    {
        $user = User::factory()->create();
        
        // Create an IotDevice manually since no factory exists
        $device = IotDevice::create([
            'name' => 'Test Device',
            'device_id' => 'test-device-001',
            'api_key' => 'test-key',
            'user_id' => $user->id
        ]);
        
        $profile = Profile::create([
            'name' => 'Test Profile',
            'expected_power' => 100,
            'max_power_threshold' => 120,
            'min_power_threshold' => 80,
            'user_id' => $user->id,
            'scheduling_enabled' => true,
            'turn_on_time' => '08:00',
            'turn_off_time' => '18:00',
            'schedule_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']
        ]);

        // Test during on hours (10 AM)
        Carbon::setTestNow(Carbon::parse('2025-01-06 10:00:00')); // Monday
        $this->assertTrue($profile->shouldBeOn());

        // Test during off hours (20:00 PM)
        Carbon::setTestNow(Carbon::parse('2025-01-06 20:00:00')); // Monday
        $this->assertFalse($profile->shouldBeOn());

        // Test on weekend (not in schedule_days)
        Carbon::setTestNow(Carbon::parse('2025-01-05 10:00:00')); // Sunday
        $this->assertFalse($profile->shouldBeOn());
    }

    public function test_profile_applies_schedule_to_sockets()
    {
        $user = User::factory()->create();
        
        $device = IotDevice::create([
            'name' => 'Test Device',
            'device_id' => 'test-device-002',
            'api_key' => 'test-key',
            'user_id' => $user->id
        ]);
        
        $profile = Profile::create([
            'name' => 'Test Profile',
            'expected_power' => 100,
            'max_power_threshold' => 120,
            'min_power_threshold' => 80,
            'user_id' => $user->id,
            'scheduling_enabled' => true,
            'turn_on_time' => '08:00',
            'turn_off_time' => '18:00',
            'schedule_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']
        ]);

        $socket = Socket::create([
            'name' => 'Test Socket',
            'iot_device_id' => $device->id,
            'profile_id' => $profile->id,
            'assigned_id' => 1,
            'state' => false
        ]);

        // Set time to during schedule (should turn on)
        Carbon::setTestNow(Carbon::parse('2025-01-06 10:00:00')); // Monday 10 AM
        
        $updatedCount = $profile->applySchedule();
        
        $this->assertEquals(1, $updatedCount);
        $this->assertTrue($socket->fresh()->state);
    }

    public function test_socket_scheduling_helper_methods()
    {
        $user = User::factory()->create();
        
        $device = IotDevice::create([
            'name' => 'Test Device',
            'device_id' => 'test-device-003',
            'api_key' => 'test-key',
            'user_id' => $user->id
        ]);
        
        $profile = Profile::create([
            'name' => 'Test Profile',
            'expected_power' => 100,
            'max_power_threshold' => 120,
            'min_power_threshold' => 80,
            'user_id' => $user->id,
            'scheduling_enabled' => true,
            'turn_on_time' => '08:00',
            'turn_off_time' => '18:00',
            'schedule_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']
        ]);

        $socket = Socket::create([
            'name' => 'Test Socket',
            'iot_device_id' => $device->id,
            'profile_id' => $profile->id,
            'assigned_id' => 1,
            'state' => false
        ]);

        Carbon::setTestNow(Carbon::parse('2025-01-06 10:00:00')); // Monday 10 AM

        $this->assertTrue($socket->isScheduled());
        $this->assertTrue($socket->getScheduledState());
        $this->assertFalse($socket->isInSync()); // Socket is off but should be on
        
        $nextChange = $socket->getNextScheduleChange();
        $this->assertNotNull($nextChange);
        $this->assertEquals('turn_off', $nextChange['action']);
    }

    public function test_schedule_spanning_midnight()
    {
        $user = User::factory()->create();
        
        $profile = Profile::create([
            'name' => 'Night Profile',
            'expected_power' => 100,
            'max_power_threshold' => 120,
            'min_power_threshold' => 80,
            'user_id' => $user->id,
            'scheduling_enabled' => true,
            'turn_on_time' => '22:00',  // 10 PM
            'turn_off_time' => '06:00', // 6 AM (next day)
            'schedule_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
        ]);

        // Test at midnight (should be on)
        Carbon::setTestNow(Carbon::parse('2025-01-06 00:30:00'));
        $this->assertTrue($profile->shouldBeOn());

        // Test at 23:00 (should be on)
        Carbon::setTestNow(Carbon::parse('2025-01-06 23:00:00'));
        $this->assertTrue($profile->shouldBeOn());

        // Test at 10:00 AM (should be off)
        Carbon::setTestNow(Carbon::parse('2025-01-06 10:00:00'));
        $this->assertFalse($profile->shouldBeOn());
    }
}
