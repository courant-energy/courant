<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Question;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestQuestionsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Question::query()->latest()->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            ImageColumn::make('user.profile')
                ->getStateUsing(function ($record) {
                    // Use the profile if it exists, otherwise fallback to a default avatar
                    return $record->user->profile ?? "https://ui-avatars.com/api/?name=" . urlencode($record->user->name) . "&color=7F9CF5&background=EBF4FF";
                })
                ->label('')
                ->circular(),
                
            TextColumn::make('user.name')
                ->label('User')
                ,
                
            TextColumn::make('subject')
                ->label('Subject')
                ->limit(50),
                
            TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'resolved' => 'success',
                    'rejected' => 'danger',
                    default => 'gray',
                })
                ->icon(function ($record) {
                    return match ($record->status) {
                        'pending' => 'heroicon-o-clock',
                        'resolved' => 'heroicon-o-check-circle',
                        'rejected' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    };
                }),
                
            TextColumn::make('created_at')
                ->label('Submitted')
                ->dateTime()
                ->sortable(),
        ];
    }
    
    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
    
    public function getTableHeading(): ?string
    {
        return 'Latest Questions';
    }
}