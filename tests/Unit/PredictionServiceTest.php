<?php

use App\Models\IotDevice;
use App\Models\Profile;
use App\Models\Reading;
use App\Models\Socket;
use App\Services\PredictionService;
use Carbon\Carbon;

beforeEach(function () {
    // Reset the database for each test
    $this->artisan('migrate:fresh');

    // Create a test user, device, profile, and socket
    $user = \App\Models\User::factory()->create();

    $device = IotDevice::create([
        'user_id' => $user->id,
        'name' => 'Test Device',
        'device_uuid' => 'test-device-'.uniqid(),
        'password' => 'test-password',
        'is_active' => true,
    ]);

    $profile = Profile::create([
        'name' => 'Test Profile',
        'user_id' => $user->id,
        'expected_power' => 300,
        'max_power_threshold' => 400,
        'min_power_threshold' => 200,
        'notify_on_anomaly' => true,
        'alert_sensitivity' => 'medium',
        'voltage_threshold_high' => 250,
        'voltage_threshold_low' => 210,
        'power_factor_threshold' => 0.85,
    ]);

    $socket = Socket::create([
        'name' => 'Test Socket',
        'iot_device_id' => $device->id,
        'profile_id' => $profile->id,
        'assigned_id' => 1,
    ]);

    // Store these for use in tests
    $this->user = $user;
    $this->device = $device;
    $this->profile = $profile;
    $this->socket = $socket;

    // Create our prediction service
    $this->predictionService = new PredictionService;
});

test('it detects voltage instability', function () {
    // Create stable voltage readings for the first half
    createStableVoltageReadings($this->socket, 20);

    // Create increasingly unstable voltage readings for second half
    createUnstableVoltageReadings($this->socket, 20);

    // Run the prediction service
    $predictions = $this->predictionService->predictFailuresForSocket($this->socket);

    // Assert we found predictions
    expect($predictions)->not->toBeNull();

    // Check if we found voltage instability
    $foundVoltageInstability = false;
    foreach ($predictions as $prediction) {
        if ($prediction['type'] === 'voltage_instability') {
            $foundVoltageInstability = true;
            break;
        }
    }

    expect($foundVoltageInstability)->toBeTrue();
});

test('it detects power factor degradation', function () {
    // Create readings with gradually degrading power factor
    createPowerFactorDegradationReadings($this->socket, 40);

    // Run the prediction service
    $predictions = $this->predictionService->predictFailuresForSocket($this->socket);

    // Assert we found predictions
    expect($predictions)->not->toBeNull();

    // Check if we found power factor degradation
    $foundPowerFactorDegradation = false;
    foreach ($predictions as $prediction) {
        if ($prediction['type'] === 'power_factor_degradation') {
            $foundPowerFactorDegradation = true;
            break;
        }
    }

    expect($foundPowerFactorDegradation)->toBeTrue();
});

test('it detects increasing power consumption', function () {
    // Create readings with gradually increasing power consumption
    createIncreasingPowerReadings($this->socket, 40);

    // Run the prediction service
    $predictions = $this->predictionService->predictFailuresForSocket($this->socket);

    // Assert we found predictions
    expect($predictions)->not->toBeNull();

    // Check if we found increasing power consumption
    $foundIncreasingPower = false;
    foreach ($predictions as $prediction) {
        if ($prediction['type'] === 'increasing_power_consumption') {
            $foundIncreasingPower = true;
            break;
        }
    }

    expect($foundIncreasingPower)->toBeTrue();
});

test('it detects current spikes', function () {
    // Create readings with current spikes
    createCurrentSpikeReadings($this->socket, 40);

    // Run the prediction service
    $predictions = $this->predictionService->predictFailuresForSocket($this->socket);

    // Assert we found predictions
    expect($predictions)->not->toBeNull();

    // Check if we found current spikes
    $foundCurrentSpikes = false;
    foreach ($predictions as $prediction) {
        if ($prediction['type'] === 'current_spikes') {
            $foundCurrentSpikes = true;
            break;
        }
    }

    expect($foundCurrentSpikes)->toBeTrue();
});

test('it handles empty readings gracefully', function () {
    // No readings created

    // Run the prediction service (should return null, not error)
    $predictions = $this->predictionService->predictFailuresForSocket($this->socket);

    expect($predictions)->toBeNull();
});

// Helper functions to create test readings

function createStableVoltageReadings(Socket $socket, int $count = 20)
{
    $now = Carbon::now();

    for ($i = 0; $i < $count; $i++) {
        createReading([
            'socket_id' => $socket->id,
            'profile_id' => $socket->profile_id,
            'voltage' => 230 + mt_rand(-2, 2), // Small stable fluctuation
            'reading_time' => $now->copy()->subMinutes(($count * 2) - $i),
        ]);
    }
}

function createUnstableVoltageReadings(Socket $socket, int $count = 20)
{
    $now = Carbon::now();

    for ($i = 0; $i < $count; $i++) {
        $volatility = ($i / $count) * 30; // Gradually increasing volatility
        createReading([
            'socket_id' => $socket->id,
            'profile_id' => $socket->profile_id,
            'voltage' => 230 + mt_rand(-$volatility - 2, $volatility + 2),
            'reading_time' => $now->copy()->subMinutes($count - $i),
        ]);
    }
}

function createPowerFactorDegradationReadings(Socket $socket, int $count = 40)
{
    $now = Carbon::now();

    for ($i = 0; $i < $count; $i++) {
        $powerFactor = 0.95 - ($i / $count * 0.3); // Gradually decreasing power factor
        createReading([
            'socket_id' => $socket->id,
            'profile_id' => $socket->profile_id,
            'power_factor' => $powerFactor,
            'reading_time' => $now->copy()->subMinutes($count - $i),
        ]);
    }
}

function createIncreasingPowerReadings(Socket $socket, int $count = 40)
{
    $now = Carbon::now();
    $basePower = 300;

    for ($i = 0; $i < $count; $i++) {
        $powerIncrease = ($i / $count) * 0.5; // Up to 50% increase
        $power = $basePower * (1 + $powerIncrease);
        createReading([
            'socket_id' => $socket->id,
            'profile_id' => $socket->profile_id,
            'power' => $power,
            'current' => $power / 230, // Current based on power at 230V
            'reading_time' => $now->copy()->subMinutes($count - $i),
        ]);
    }
}

function createCurrentSpikeReadings(Socket $socket, int $count = 40)
{
    $now = Carbon::now();
    $baseCurrent = 1.0;
    $spikesCreated = 0;

    for ($i = 0; $i < $count; $i++) {
        // Generate spikes in about 20% of readings
        $isSpike = (mt_rand(1, 100) <= 20 && $spikesCreated < ($count * 0.2));

        if ($isSpike) {
            $current = $baseCurrent * (mt_rand(150, 300) / 100); // 1.5x to 3x normal
            $spikesCreated++;
        } else {
            $current = $baseCurrent * (mt_rand(90, 110) / 100); // Normal variation
        }

        createReading([
            'socket_id' => $socket->id,
            'profile_id' => $socket->profile_id,
            'current' => $current,
            'power' => 230 * $current, // Power based on current at 230V
            'reading_time' => $now->copy()->subMinutes($count - $i),
        ]);
    }
}

function createReading(array $data)
{
    // Default values for readings
    $defaults = [
        'voltage' => 230,
        'current' => 1.0,
        'power' => 230,
        'energy' => 0.1,
        'frequency' => 50,
        'power_factor' => 0.95,
        'reading_time' => Carbon::now(),
    ];

    // Merge defaults with provided data
    $data = array_merge($defaults, $data);

    // Create the reading
    return Reading::create($data);
}
