<?php

namespace App\Filament\Admin\Widgets;

use App\Models\IotDevice;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected int|string|array $columnSpan = 'full';
    
    protected function getStats(): array
    {
        // Count all registered devices
        $totalDevices = IotDevice::count();
        
        // Count devices active in last 15 minutes
        $recentlyActiveDevices = IotDevice::where('last_seen_at', '>=', now()->subMinutes(15))
            ->where('status', 'online')
            ->count();
        
        // Count all users
        $totalUsers = User::count();
        
        // Count users registered in last 30 days
        $newUsers = User::where('created_at', '>=', now()->subDays(30))->count();
        
        return [
            Stat::make('Total Registered Devices', $totalDevices)
                ->description('All IoT devices in system')
                ->descriptionIcon('heroicon-o-cpu-chip')
                ->color('primary'),
            
            Stat::make('Recently Active Devices', $recentlyActiveDevices)
                ->description('Active in last 15 minutes')
                ->descriptionIcon('heroicon-o-signal')
                ->color('success'),
            
            Stat::make('Total Users', $totalUsers)
                ->description($newUsers . ' new in last 30 days')
                ->descriptionIcon('heroicon-o-users')
                ->color('info'),
        ];
    }
}