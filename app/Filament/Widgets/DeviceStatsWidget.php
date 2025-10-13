<?php

namespace App\Filament\Widgets;

use App\Models\IotDevice;
use App\Models\Profile;
use App\Services\EnergyCalculationService;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

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
                    ->options(Profile::where('user_id', Auth::id())->pluck('name', 'id'))
                    ->placeholder('All Profiles')
                    ->live()
                    ->afterStateUpdated(fn () => $this->refreshStats()),
            ]);
    }

    protected function getStats(): array
    {
        $userId = Auth::id();

        // Total connected devices - filter by authenticated user
        $totalDevices = IotDevice::where('user_id', $userId)
            ->count();

        // Online devices (seen in the last 15 minutes) - filter by authenticated user
        $onlineDevices = IotDevice::where('user_id', $userId)
            ->where('last_seen_at', '>=', now()->subMinutes(15))
            ->where('status', 'online')
            ->count();

        // Calculate energy consumption using the service - following the same logic as EnergyCalculationService
        $todayStart = Carbon::today()->format('Y-m-d H:i:s');
        $todayEnd = Carbon::today()->endOfDay()->format('Y-m-d H:i:s');
        $todayEnergy = 0;

        // If specific profile is selected, calculate for just that profile
        if ($this->selectedProfile) {
            $profile = Profile::where('user_id', $userId)
                ->find($this->selectedProfile);

            if ($profile) {
                // Use the same calculation method as the service
                $todayEnergy = $this->energyCalculationService->calculateEnergyConsumption(
                    $profile,
                    $todayStart,
                    $todayEnd
                );
            }
        } else {
            // Calculate for all profiles with sockets belonging to authenticated user
            // This follows the same pattern as the service: iterate through profiles and sum energy
            $profiles = Profile::where('user_id', $userId)
                ->whereHas('sockets')
                ->get();

            foreach ($profiles as $profile) {
                $todayEnergy += $this->energyCalculationService->calculateEnergyConsumption(
                    $profile,
                    $todayStart,
                    $todayEnd
                );
            }
        }

        // Round to 2 decimal places to match the service's rounding behavior
        $todayEnergy = round($todayEnergy, 2);

        return [
            Stat::make('Connected Devices', $totalDevices)
                ->description('Total registered devices')
                ->descriptionIcon('heroicon-o-device-tablet')
                ->color('primary'),

            Stat::make('Online Devices', $onlineDevices)
                ->description('Active in last 15 minutes')
                ->descriptionIcon('heroicon-o-signal')
                ->color('success'),

            Stat::make('Energy Consumption', number_format($todayEnergy, 2).' kWh')
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
