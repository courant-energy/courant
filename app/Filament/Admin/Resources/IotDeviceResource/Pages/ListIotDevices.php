<?php

namespace App\Filament\Admin\Resources\IotDeviceResource\Pages;

use App\Filament\Admin\Resources\IotDeviceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIotDevices extends ListRecords
{
    protected static string $resource = IotDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
