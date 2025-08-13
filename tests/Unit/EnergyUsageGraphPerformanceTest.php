<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Profile;
use App\Models\Socket;
use App\Models\IotDevice;
use App\Models\User;
use App\Models\Reading;
use App\Services\EnergyCalculationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EnergyUsageGraphPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $profile;
    private $socket;
    private $device;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->user = User::factory()->create();
        
        $this->device = IotDevice::factory()->create([
            'user_id' => $this->user->id,
        ]);
        
        $this->profile = Profile::factory()->create([
            'user_id' => $this->user->id,
        ]);
        
        $this->socket = Socket::factory()->create([
            'iot_device_id' => $this->device->id,
            'profile_id' => $this->profile->id,
        ]);
    }

    public function test_energy_calculation_service_performance()
    {
        // Create test readings
        $startTime = Carbon::now()->subHours(2);
        
        for ($i = 0; $i < 100; $i++) {
            Reading::create([
                'socket_id' => $this->socket->id,
                'profile_id' => $this->profile->id,
                'voltage' => 230 + rand(-10, 10),
                'current' => 1.5 + rand(-50, 50) / 100,
                'power' => 300 + rand(-50, 50),
                'energy' => 0.1,
                'frequency' => 50,
                'power_factor' => 0.9 + rand(-10, 10) / 100,
                'reading_time' => $startTime->copy()->addMinutes($i),
            ]);
        }

        $service = new EnergyCalculationService();
        
        // Test single calculation
        $start = microtime(true);
        $energy = $service->calculateEnergyConsumption(
            $this->profile,
            $startTime,
            Carbon::now()
        );
        $singleTime = microtime(true) - $start;

        // Test batch calculation
        $intervals = [];
        for ($i = 0; $i < 10; $i++) {
            $intervals[] = [
                'start' => $startTime->copy()->addMinutes($i * 10),
                'end' => $startTime->copy()->addMinutes(($i + 1) * 10),
            ];
        }

        $start = microtime(true);
        $energyResults = $service->calculateEnergyConsumptionForIntervals($this->profile, $intervals);
        $batchTime = microtime(true) - $start;

        $this->assertGreaterThan(0, $energy);
        $this->assertCount(10, $energyResults);
        
        // Batch should be more efficient than 10 individual calls
        $this->assertLessThan($singleTime * 10, $batchTime * 2);
        
        echo "\nPerformance Results:\n";
        echo "Single calculation: " . round($singleTime * 1000, 2) . "ms\n";
        echo "Batch calculation (10 intervals): " . round($batchTime * 1000, 2) . "ms\n";
        echo "Estimated individual calls time: " . round($singleTime * 10 * 1000, 2) . "ms\n";
        echo "Performance improvement: " . round((($singleTime * 10) - $batchTime) / ($singleTime * 10) * 100, 1) . "%\n";
    }
}
