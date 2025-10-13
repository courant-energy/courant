<?php

namespace App\Filament\Widgets;

use App\Models\Alert;
use Filament\Infolists;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class AlertsTableWidget extends BaseWidget implements HasTable
{
    use InteractsWithTable;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            // ->paginated(false)
            ->query(
                Alert::query()
                    ->where('resolved', false)
                    ->whereHas('iotDevice', function (Builder $query) {
                        $query->where('user_id', auth()->id());
                    })
                    ->latest()
                // ->take(5)
            )
            ->heading('Recent Alerts')
            ->description('Smart Alerts from your devices. Our AI learns and gets better at spotting things the more you use your device. Occasionally, you might get a false notification.')
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

                        TextColumn::make('message')
                            ->label('Message')
                            ->weight(FontWeight::Light)
                            ->color(Color::Gray)
                            ->size(TextColumnSize::ExtraSmall)
                            ->limit(200)
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
                            'secondary' => 'low',
                            'info' => 'medium',
                            'warning' => 'high',
                            'danger' => 'critical',
                        ]),
                ])->from('md'),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('severity')
                    ->options([
                        'critical' => 'Critical',
                        'high' => 'High',
                        'medium' => 'Medium',
                        'low' => 'Low',
                    ]),

            ])
            ->actions([
                ActionGroup::make([
                    Action::make('resolved')
                        ->label('Resolve Alert')
                        ->action(function (Alert $record) {
                            $record->update(['resolved' => true]);
                        })
                        ->color('success')
                        ->icon('heroicon-o-check'),

                    Action::make('dismiss')
                        ->label('Dismiss Alert')
                        ->action(function (Alert $record) {
                            $record->delete();
                        })
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalDescription('Dismissing an alert will remove it from our system and will affect the AI insights.')
                        ->icon('heroicon-o-x-mark'),
                    Action::make('falseAlert')
                        ->label('Discard Error Data')
                        ->action(function (Alert $record) {
                            // delete reading if reading_id is not null
                            if ($record->reading_id) {
                                $record->reading()->delete();
                            }
                            $record->delete();
                        })
                        ->color('danger')
                        ->icon('heroicon-o-trash')
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
                                ->label('Message'),

                        ]),

                ])
                    ->label('Actions')
                    ->size(ActionSize::Small)
                    ->button(),
            ]);
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
