<?php

namespace App\Filament\Widgets;

use App\Models\Question;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Forms;
use Filament\Tables\Columns\Layout\Split;

class SubmittedQueryTable extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Submitted Questions';

    protected $listeners = ['refresh-submitted-queries' => '$refresh'];


    public function table(Table $table): Table
    {
        return $table
            ->emptyStateIcon('heroicon-o-question-mark-circle')
            ->emptyStateHeading('No Questions Found')
            ->emptyStateDescription('You have not submitted any questions yet.')
            ->query(
                Question::query()
                    ->where('user_id', auth()->id())
                    ->latest()
            )
            ->columns([
                Split::make([

                    TextColumn::make('ticket_id')
                        ->label('Ticket ID')
                        ->sortable()
                        ->searchable()
                        ->copyable()
                        ->icon('heroicon-o-ticket'),
                    TextColumn::make('subject')
                        ->label('Subject')
                        ->sortable()
                        ->limit(50)
                        ->searchable(),
                    TextColumn::make('created_at')
                        ->label('Created At')
                        ->dateTime()
                        ->color('gray')
                        ->size('xs')
                        ->sortable(),
                    TextColumn::make('status')
                        ->label('Status')
                        ->color(function ($record) {
                            return match ($record->status) {
                                'pending' => 'warning',
                                'resolved' => 'success',
                                'rejected' => 'danger',
                            };
                        })
                        ->badge()
                        ->icon(function ($record) {
                            return match ($record->status) {
                                'pending' => 'heroicon-o-clock',
                                'resolved' => 'heroicon-o-check-circle',
                                'rejected' => 'heroicon-o-x-circle',
                            };
                        }),
                   
                ])
                ->from('md'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver()
                    ->infolist([
                        \Filament\Infolists\Components\TextEntry::make('subject'),
                        \Filament\Infolists\Components\TextEntry::make('body')
                            ->html(),
                        \Filament\Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'resolved' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            }),
                        \Filament\Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                    ])
            ]);
    }
}
