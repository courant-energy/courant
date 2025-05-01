<?php

namespace App\Filament\Resources;

use App\Filament\Pages\Learn;
use App\Filament\Resources\LearningResource\Pages;
use App\Filament\Resources\LearningResource\RelationManagers;
use App\Models\Learning;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
