<?php

namespace App\Filament\Widgets;

use App\Models\Alert;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists;

class AlertsTableWidget extends BaseWidget implements HasTable
{
    use InteractsWithTable;


    protected int|string|array $columnSpan = '1';

    protected static ?int $sort = 2;



    public function table(Table $table): Table
    {
        return $table
            // ->paginated(false)
            ->query(
                Alert::query()
                    ->whereHas('iotDevice', function (Builder $query) {
                        $query->where('user_id', auth()->id());
                    })
                    ->latest()
                // ->take(5)
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

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('severity')
                    ->options([
                        'critical' => 'Critical',
                        'high' => 'High',
                        'medium' => 'Medium',
                        'low' => 'Low',
                    ])
                    
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('dismiss')
                        ->label('Dismiss')
                        ->action(function (Alert $record) {
                            $record->delete();
                        })
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription('Dismissing an alert will remove it from our system and will affect the AI insights.')
                        ->icon('heroicon-o-x-mark'),
                    Action::make('falseAlert')
                        ->label('False Alert')
                        ->action(function (Alert $record) {
                            // delete reading if reading_id is not null
                            if ($record->reading_id) {
                                $record->reading()->delete();
                            }
                            $record->delete();
                        })
                        ->color('danger')
                        ->icon('heroicon-o-flag')
                        ->requiresConfirmation()
                        ->modalDescription('Do you think this alert is a false alert? We will delete the alert and the reading if it exists. This will affect the AI insights.'),
                    ViewAction::make()
                        ->infolist([
                            Infolists\Components\TextEntry::make('profile.name')
                                ->label('Profile'),
                            Infolists\Components\TextEntry::make('iotDevice.name')
                                ->label('Device'),
                            Infolists\Components\TextEntry::make('readable_type')
                                ->label('Type'),
                            Infolists\Components\TextEntry::make('created_at')
                                ->label('Time')
                                ->dateTime(),
                            Infolists\Components\TextEntry::make('severity')
                                ->label('Severity')
                                ->badge()
                                ->colors([
                                    'secondary' => 'info',
                                    'warning' => 'warning',
                                    'danger' => 'critical',
                                ]),
                            Infolists\Components\TextEntry::make('message')
                                ->label('Message')

                        ])

                ])
            ]);
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
