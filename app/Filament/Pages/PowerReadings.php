<?php

namespace App\Filament\Pages;

use App\Models\IotDevice;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;

class PowerReadings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'Data & Insights';

    protected static ?string $title = 'Power Monitoring';

    protected static string $view = 'filament.pages.power-readings';

    public ?array $data = [];

    public Collection $sockets;

    public bool $hasSelectedDevice = false;
    public IotDevice $device;

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                \Filament\Forms\Components\Select::make('device_id')
                    ->label('IoT Device')
                    ->options(function () {
                        return \App\Models\IotDevice::where('user_id', auth()->user()->id)
                            ->pluck('name', 'id');
                    })
                    ->required(),
            ]);
    }

    public function mount(): void
    {
        if (!auth()->user()->iotDevices()->exists()) {
            Notification::make()
                ->title('No devices found')
                ->body('Please add a device to your account first in the IoT Devices section.')
                ->danger()
                ->send();
            return;
        }

        // Set the initial state of the form
        $this->form->fill([
            'device_id' => auth()->user()->iotDevices()
                ->first()?->id,
        ]);

        $this->loadData();
    }

    public function loadData()
    {
        $this->validate();

        $this->device = auth()->user()->iotDevices()
            ->where('id', $this->data['device_id'])
            ->first();

        if (!$this->device) {
            $this->hasSelectedDevice = false;

            Notification::make()
                ->title('Device not found')
                ->danger()
                ->send();
            return;
        }

        $this->hasSelectedDevice = true;
        $this->sockets = $this->device->sockets;
        if ($this->sockets->isEmpty()) {
            Notification::make()
                ->title('No data found')
                ->danger()
                ->send();
            return;
        }

        Notification::make()
            ->title('Data loaded')
            ->success()
            ->send();
    }
}
