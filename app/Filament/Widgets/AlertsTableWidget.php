<?php

namespace App\Filament\Widgets;

use App\Models\Alert;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class AlertsTableWidget extends BaseWidget implements HasTable
{
    use InteractsWithTable;
    
    protected static ?string $heading = 'Latest Alerts';
    
    protected int|string|array $columnSpan = 'full';
    
    protected static ?int $sort = 2;
    
    protected function getTableQuery(): Builder
    {
        return Alert::with(['iotDevice', 'socket', 'profile'])
                    ->latest()
                    ->limit(10);
    }
    
    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('created_at')
                ->label('Time')
                ->dateTime()
                ->sortable(),
                
            TextColumn::make('readable_type')
                ->label('Type')
                ->searchable(),
                
            TextColumn::make('message')
                ->label('Message')
                ->limit(50)
                ->tooltip(function (TextColumn $column): ?string {
                    $state = $column->getState();
                    if (strlen($state) <= 20) {
                        return null;
                    }
                    return $state;
                }),
                
            TextColumn::make('severity')
                ->badge()
                ->label('Severity')
                ->colors([
                    'secondary' => 'info',
                    'warning' => 'warning',
                    'danger' => 'critical',
                ]),
                
            TextColumn::make('profile.name')
                ->label('Profile')
                ->searchable(),
                
            TextColumn::make('iotDevice.name')
                ->label('Device')
                ->searchable(),
        ];
    }
    
    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}