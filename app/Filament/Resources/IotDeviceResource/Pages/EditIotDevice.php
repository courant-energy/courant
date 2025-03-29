<?php

namespace App\Filament\Resources\IotDeviceResource\Pages;

use App\Filament\Resources\IotDeviceResource;
use App\Models\IotDevice;
use App\Services\IotDeviceService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditIotDevice extends EditRecord
{
    protected static string $resource = IotDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('unlink')
                ->color('danger')
                ->action(function (IotDevice|Model $record, IotDeviceService $iotDeviceService) {
                    $iotDeviceService->unlinkDevice($record->device_uuid);
                    Notification::make()
                        ->success()
                        ->title('IoT Device Unlinked')
                        ->body('The IoT device has been unlinked from your account.')
                        ->send();
                    
                    return redirect()->route('filament.app.resources.iot-devices.index');
                })
                ->label('Remove device')
                ->requiresConfirmation()
                ->modalDescription('Are you sure you want to unlink this IoT device? This will remove the device from your account and reset its password to factory default. This action cannot be undone.'),
        ];
    }
}
