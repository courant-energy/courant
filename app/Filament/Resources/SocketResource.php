<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocketResource\Pages;
use App\Filament\Resources\SocketResource\RelationManagers;
use App\Models\Socket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SocketResource extends Resource
{
    protected static ?string $model = Socket::class;

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?string $navigationGroup = 'Device Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'default' => 1,
                'lg' => 2,
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('iotDevice.name')
                        ->label('IoT Device Name')
                        ->searchable()
                        ->sortable()
                        ->weight(FontWeight::Bold),
                    Tables\Columns\TextColumn::make('name')
                        ->label('Socket Name')
                        ->searchable()
                        ->sortable(),
                    Tables\Columns\TextColumn::make('profile.name')
                        ->label('Profile Name')
                        ->default('No Profile Selected')
                        ->searchable()
                        ->sortable()
                        ->color(fn ($state) => $state === 'No Profile Selected' ? 'gray' : null),
                ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('iot_device')
                    ->relationship('iotDevice', 'name')
                    ->label('IoT Device')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
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
            'index' => Pages\ListSockets::route('/'),
            // 'create' => Pages\CreateSocket::route('/create'),
            'edit' => Pages\EditSocket::route('/{record}/edit'),
        ];
    }
}
