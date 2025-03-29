<?php

namespace App\Services;

use App\Models\IotDevice;
use App\Models\User;
use App\Models\Socket;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class IotDeviceService
{
    /**
     * @var IotDevice
     */
    protected $iotDevice;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Socket
     */
    protected $socket;

    /**
     * IotDeviceService constructor.
     *
     * @param IotDevice $iotDevice
     * @param User $user
     * @param Socket $socket
     */
    public function __construct(IotDevice $iotDevice, User $user, Socket $socket)
    {
        $this->iotDevice = $iotDevice;
        $this->user = $user;
        $this->socket = $socket;
    }

    /**
     * Link an IoT device to a user
     *
     * @param int|string $deviceId
     * @param int|string $userId
     * @return IotDevice
     * @throws ModelNotFoundException
     */
    public function linkDeviceToUser($deviceId, $userId)
    {
        return \DB::transaction(function () use ($deviceId, $userId) {
            // Find the device and user or throw an exception
            $device = $this->iotDevice->where('device_uuid', $deviceId)->firstOrFail();
            $user = $this->user->findOrFail($userId);

            // Link the device to the user
            $device->user_id = $user->id;
            $device->is_active = true; // Set the device as active
            $device->save();

            // generate the sockets 
            $sockets = $device->socket_count;
            for ($i = 1; $i <= $sockets; $i++) {
                // Create a new socket for the device
                $socket = new $this->socket();
                $socket->name = "Socket $i";
                $socket->iot_device_id = $device->id;

                $socket->save();
            }

            return $device;
        });
    }

    /**
     * Unlink an IoT device from a user
     *
     * @param int|string $deviceId
     * @return IotDevice
     * @throws ModelNotFoundException
     */
    public function unlinkDevice($deviceId)
    {
        return \DB::transaction(function () use ($deviceId) {
            $device = $this->iotDevice->where('device_uuid', $deviceId)->firstOrFail();

            $device->user_id = null;
            $device->save();

            // Unlink all sockets associated with the device
            $sockets = $this->socket->where('iot_device_id', $device->id)->get();
            foreach ($sockets as $socket) {
                $socket->delete();
            }

            // Set the device as inactive
            $device->is_active = false;
            $device->save();

            return $device;
        });
    }

    /**
     * Get all devices for a specific user
     *
     * @param int|string $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDevicesByUser($userId)
    {
        return $this->iotDevice->where('user_id', $userId)->get();
    }
}
