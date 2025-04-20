<?php

namespace App\Filament\Admin\Resources\IotDeviceResource\Pages;

use App\Filament\Admin\Resources\IotDeviceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateIotDevice extends CreateRecord
{
    protected static string $resource = IotDeviceResource::class;


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate a unique UUID for the device
        $data['name'] = $data['device_uuid'];
        return $data;
    }
    
}
