<?php

namespace Tests\Feature;

use App\Models\IotDevice;
use App\Models\Profile;
use App\Models\Socket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
            'user_id' => $user->id,
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
            'schedule_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
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

    public function test_profile_applies_schedule_only_at_scheduled_times()
    {
        $user = User::factory()->create();

        $device = IotDevice::create([
            'name' => 'Test Device',
            'device_uuid' => \Illuminate\Support\Str::uuid(),
            'password' => 'test-password',
            'user_id' => $user->id,
            'is_active' => true,
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
            'schedule_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        ]);

        $socket = Socket::create([
            'name' => 'Test Socket',
            'iot_device_id' => $device->id,
            'profile_id' => $profile->id,
            'assigned_id' => 1,
            'state' => false,
        ]);

        // Set time to just before schedule (should not apply)
        Carbon::setTestNow(Carbon::parse('2025-01-06 07:59:00')); // Monday 7:59 AM
        $result = $profile->applySchedule();
        $this->assertEquals('no_schedule_due', $result['reason']);
        $this->assertFalse($socket->fresh()->state);

        // Set time to exactly at schedule (should apply)
        Carbon::setTestNow(Carbon::parse('2025-01-06 08:00:00')); // Monday 8:00 AM
        $result = $profile->applySchedule();
        $this->assertEquals('turn_on', $result['action']);
        $this->assertEquals(1, $result['updated']);
        $this->assertTrue($socket->fresh()->state);

        // Try to apply again immediately (should not apply again)
        $result = $profile->applySchedule();
        $this->assertEquals('no_schedule_due', $result['reason']);
        $this->assertTrue($socket->fresh()->state); // State unchanged

        // User manually turns off socket - schedule should not override
        $socket->update(['state' => false]);
        $result = $profile->applySchedule();
        $this->assertEquals('no_schedule_due', $result['reason']);
        $this->assertFalse($socket->fresh()->state); // User's manual change preserved
    }

    public function test_socket_scheduling_helper_methods()
    {
        $user = User::factory()->create();

        $device = IotDevice::create([
            'name' => 'Test Device',
            'device_uuid' => \Illuminate\Support\Str::uuid(),
            'password' => 'test-password',
            'user_id' => $user->id,
            'is_active' => true,
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
            'schedule_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        ]);

        $socket = Socket::create([
            'name' => 'Test Socket',
            'iot_device_id' => $device->id,
            'profile_id' => $profile->id,
            'assigned_id' => 1,
            'state' => false,
        ]);

        Carbon::setTestNow(Carbon::parse('2025-01-06 10:00:00')); // Monday 10 AM

        $this->assertTrue($socket->isScheduled());
        $this->assertTrue($socket->getScheduledState()); // Should be on during work hours
        $this->assertFalse($socket->isInSync()); // Socket is off but schedule says it should be on

        // Check if there's a pending schedule action
        $pendingAction = $socket->getPendingScheduleAction();
        $this->assertNull($pendingAction); // No action pending since it's not exactly at scheduled time

        // Test at exact schedule time
        Carbon::setTestNow(Carbon::parse('2025-01-06 08:00:00')); // Monday 8:00 AM exactly
        $pendingAction = $socket->getPendingScheduleAction();
        $this->assertNotNull($pendingAction);
        $this->assertEquals('turn_on', $pendingAction['action']);

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
            'schedule_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
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

    public function test_schedule_tolerance_window()
    {
        $user = User::factory()->create();

        $profile = Profile::create([
            'name' => 'Test Profile',
            'expected_power' => 100,
            'max_power_threshold' => 120,
            'min_power_threshold' => 80,
            'user_id' => $user->id,
            'scheduling_enabled' => true,
            'turn_on_time' => '08:00',
            'turn_off_time' => '18:00',
            'schedule_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        ]);

        // Test at exactly scheduled time
        Carbon::setTestNow(Carbon::parse('2025-01-06 08:00:00')); // Monday 8:00 AM
        $this->assertTrue($profile->shouldApplyTurnOn());

        // Test within tolerance (3 minutes after)
        Carbon::setTestNow(Carbon::parse('2025-01-06 08:03:00')); // Monday 8:03 AM
        $this->assertTrue($profile->shouldApplyTurnOn());

        // Test beyond tolerance (6 minutes after)
        Carbon::setTestNow(Carbon::parse('2025-01-06 08:06:00')); // Monday 8:06 AM
        $this->assertFalse($profile->shouldApplyTurnOn());

        // Test before scheduled time
        Carbon::setTestNow(Carbon::parse('2025-01-06 07:59:00')); // Monday 7:59 AM
        $this->assertFalse($profile->shouldApplyTurnOn());
    }

    public function test_schedule_prevents_duplicate_application()
    {
        $user = User::factory()->create();

        $device = IotDevice::create([
            'name' => 'Test Device',
            'device_uuid' => \Illuminate\Support\Str::uuid(),
            'password' => 'test-password',
            'user_id' => $user->id,
            'is_active' => true,
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
            'schedule_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        ]);

        $socket = Socket::create([
            'name' => 'Test Socket',
            'iot_device_id' => $device->id,
            'profile_id' => $profile->id,
            'assigned_id' => 1,
            'state' => false,
        ]);

        // Set time to scheduled time
        Carbon::setTestNow(Carbon::parse('2025-01-06 08:00:00')); // Monday 8:00 AM

        // First application should work
        $result = $profile->applySchedule();
        $this->assertEquals('turn_on', $result['action']);
        $this->assertEquals(1, $result['updated']);

        // Second application should not apply again
        $result = $profile->applySchedule();
        $this->assertEquals('no_schedule_due', $result['reason']);
        $this->assertEquals(0, $result['updated']);
    }

    public function test_appliance_presets_can_be_loaded()
    {
        $presets = Profile::getAppliancePresets();

        $this->assertIsArray($presets);
        $this->assertNotEmpty($presets);

        // Check that each preset has required fields
        foreach ($presets as $preset) {
            $this->assertArrayHasKey('name', $preset);
            $this->assertArrayHasKey('expected_power', $preset);
            $this->assertArrayHasKey('max_power_threshold', $preset);
            $this->assertArrayHasKey('min_power_threshold', $preset);

            // Validate power values are reasonable
            $this->assertIsInt($preset['expected_power']);
            $this->assertGreaterThan(0, $preset['expected_power']);
            $this->assertGreaterThanOrEqual($preset['expected_power'], $preset['max_power_threshold']);
            $this->assertLessThanOrEqual($preset['expected_power'], $preset['min_power_threshold']);
        }
    }

    public function test_appliance_presets_include_common_appliances()
    {
        $presets = Profile::getAppliancePresets();
        $names = array_column($presets, 'name');

        // Check for some common appliances
        $this->assertContains('Refrigerator', $names);
        $this->assertContains('Washing Machine', $names);
        $this->assertContains('Microwave Oven', $names);
        $this->assertContains('Television (LED 32")', $names);
    }
}
