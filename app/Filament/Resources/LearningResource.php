<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LearningResource\Pages;
use App\Models\Learning;
use Filament\Resources\Resource;

class LearningResource extends Resource
{
    protected static ?string $model = Learning::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Learning & Support';

    protected static ?int $navigationSort = 5;

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLearnings::route('/'),
            'show' => Pages\ShowLearning::route('/{slug}'),
        ];
    }
}
