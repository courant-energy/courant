<?php

namespace App\Filament\Widgets;

use App\Models\Alert;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class AlertsTableWidget extends BaseWidget implements HasTable
{
    use InteractsWithTable;


    protected int|string|array $columnSpan = '1';

    protected static ?int $sort = 2;



    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->query(
                Alert::query()
                    ->latest()
                    ->take(5)
            )
            ->heading('Recent Alerts')
            ->description('Recent alerts from devices and profiles.')
            ->columns([
                \Filament\Tables\Columns\Layout\Split::make([
                    \Filament\Tables\Columns\Layout\Stack::make([
                        TextColumn::make('profile.name')
                            ->weight(FontWeight::Bold)
                            ->label('Profile'),

                        TextColumn::make('iotDevice.name')
                            ->weight(FontWeight::Light)
                            ->label('Device'),
                        TextColumn::make('created_at')
                            ->weight(FontWeight::Light)
                            ->size(TextColumnSize::ExtraSmall)
                            ->color(Color::Gray)
                            ->label('Time')
                            ->dateTime()
                            ->sortable(),
                    ]),

                    \Filament\Tables\Columns\Layout\Stack::make([

                        TextColumn::make('readable_type')
                            ->weight(FontWeight::Bold)
                            ->label('Type'),

                        TextColumn::make('message')
                            ->label('Message')
                            ->weight(FontWeight::Light)
                            ->color(Color::Gray)
                            ->size(TextColumnSize::ExtraSmall)
                            ->limit(50)
                            ->tooltip(function (TextColumn $column): ?string {
                                $state = $column->getState();
                                if (strlen($state) <= 20) {
                                    return null;
                                }
                                return $state;
                            }),
                    ]),
                    TextColumn::make('severity')
                        ->badge()
                        ->label('Severity')
                        ->colors([
                            'secondary' => 'info',
                            'warning' => 'warning',
                            'danger' => 'critical',
                        ]),
                ]),

            ]);
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
