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

                \Filament\Forms\Components\Grid::make()
                    ->columns(2)
                    ->schema([
                        \Filament\Forms\Components\DatePicker::make('date_from')
                            ->label('Start Date')
                            ->default(now()->subDays(7)->toDateString())
                            ->required()
                            ->native(false)
                            ->maxDate(fn(Get $get) => $get('date_to'))
                            ->live(),
                        \Filament\Forms\Components\DatePicker::make('date_to')
                            ->label('End Date')
                            ->default(now()->toDateString())
                            ->required()
                            ->native(false)
                            ->minDate(fn(Get $get) => $get('date_from'))
                            ->live(),
                    ]),
            ]);
    }

    public function mount(): void
    {
        $this->form->fill();
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
