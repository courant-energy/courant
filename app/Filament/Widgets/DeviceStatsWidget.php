<?php

namespace App\Filament\Widgets;

use App\Models\IotDevice;
use App\Models\Profile;
use App\Models\Reading;
use App\Services\EnergyCalculationService;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\DB;

class DeviceStatsWidget extends BaseWidget implements HasForms
{
    use InteractsWithForms;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    // Form data
    public ?string $selectedProfile = null;

    // Service dependency
    protected EnergyCalculationService $energyCalculationService;

    public function boot(EnergyCalculationService $energyCalculationService): void
    {
        $this->energyCalculationService = $energyCalculationService;
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('selectedProfile')
                    ->label('Filter by Profile')
                    ->options(Profile::pluck('name', 'id'))
                    ->placeholder('All Profiles')
                    ->live()
                    ->afterStateUpdated(fn() => $this->refreshStats()),
            ]);
    }

    protected function getStats(): array
    {
        // Total connected devices - without user filtering for now
        $totalDevices = IotDevice::where('user_id', '=', auth()->id())
            ->count();

        // Online devices (seen in the last 15 minutes)
        $onlineDevices = IotDevice::where('last_seen_at', '>=', now()->subMinutes(15))
            ->where('user_id', '=', auth()->id())
            ->where('status', 'online')
            ->count();

        // Calculate energy consumption using the service
        $todayStart = Carbon::today()->format('Y-m-d H:i:s');
        $todayEnd = Carbon::today()->endOfDay()->format('Y-m-d H:i:s');
        $todayEnergy = 0;

        // If specific profile is selected, calculate for just that profile
        if ($this->selectedProfile) {
            $profile = Profile::find($this->selectedProfile);
            if ($profile) {
                $todayEnergy = $this->energyCalculationService->calculateEnergyConsumption(
                    $profile,
                    $todayStart,
                    $todayEnd
                );
            }
        } else {
            // Calculate for all profiles
            $profiles = Profile::whereHas('sockets')
            ->where('user_id', '=', auth()->id())
            ->get();
            foreach ($profiles as $profile) {
                $todayEnergy += $this->energyCalculationService->calculateEnergyConsumption(
                    $profile,
                    $todayStart,
                    $todayEnd
                );
            }
        }

        return [
            Stat::make('Connected Devices', $totalDevices)
                ->description('Total registered devices')
                ->descriptionIcon('heroicon-o-device-tablet')
                ->color('primary'),

            Stat::make('Online Devices', $onlineDevices)
                ->description('Active in last 15 minutes')
                ->descriptionIcon('heroicon-o-signal')
                ->color('success'),

            Stat::make('Energy Consumption', number_format($todayEnergy, 2) . ' kWh')
                ->description($this->selectedProfile
                    ? 'Today for selected profile'
                    : 'Today across all profiles')
                ->descriptionIcon('heroicon-o-bolt')
                ->color('warning'),
        ];
    }

    protected function refreshStats(): void
    {
        $this->updateStats();
    }

    public function getHeading(): string
    {
        return 'Device Statistics';
    }
}
