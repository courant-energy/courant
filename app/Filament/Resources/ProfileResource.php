<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfileResource\Pages;
use App\Filament\Resources\ProfileResource\RelationManagers;
use App\Models\Profile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProfileResource extends Resource
{
    protected static ?string $model = Profile::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Device Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Device Profile Details')
                    ->description('Configure the device profile settings')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Refrigerator, TV, or Washing Machine')
                            ->helperText('Enter a unique name for this device profile'),
                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('expected_power')
                                ->required()
                                ->integer()
                                ->maxValue(10000)
                                ->minValue(0)
                                ->placeholder('e.g., 100')
                                ->suffix('W')
                                ->helperText('Normal operating power in watts')
                                ->live()
                                ->afterStateUpdated(
                                    fn($state, Set $set) =>
                                    $set('min_power_threshold', min((int)($state * 0.9), $state - 10))
                                ),
                            Forms\Components\TextInput::make('max_power_threshold')
                                ->required()
                                ->integer()
                                ->maxValue(10000)
                                ->minValue(fn(Get $get) => (int) $get('expected_power'))
                                ->placeholder('e.g., 120')
                                ->suffix('W')
                                ->helperText('Maximum acceptable power threshold in watts')
                                ->live(),
                            Forms\Components\TextInput::make('min_power_threshold')
                                ->required()
                                ->integer()
                                ->maxValue(fn(Get $get) => (int) $get('expected_power'))
                                ->minValue(0)
                                ->placeholder('e.g., 80')
                                ->suffix('W')
                                ->helperText('Minimum acceptable power threshold in watts')
                                ->live(),
                        ])->columns(3),
                    ])->aside(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Appliance Profiles')
            ->description('Create and manage profiles for your smart appliances to define their power usage characteristics. These profiles can be assigned to smart sockets for automated monitoring and alerts.')
            ->emptyStateHeading('No profiles created yet')
            ->emptyStateDescription('Define power profiles for your appliances like refrigerators, washing machines, or computers to help the system identify normal and abnormal power consumption patterns.')
            ->emptyStateIcon('heroicon-o-identification')
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('name')
                        ->label('Profile Name')
                        ->searchable()
                        ->sortable()
                        ->weight('bold')
                        ->size('lg'),
                    Tables\Columns\TextColumn::make('expected_power')
                        ->label('Expected Power')
                        ->suffix('W')
                        ->sortable()
                        ->searchable()
                        ->color('gray')
                        ->size('sm'),
                ]),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('user_id', auth()->id());
            })
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProfiles::route('/'),
            'create' => Pages\CreateProfile::route('/create'),
            'edit' => Pages\EditProfile::route('/{record}/edit'),
        ];
    }
}
