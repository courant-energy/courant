<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IotDeviceResource\Pages;
use App\Filament\Resources\IotDeviceResource\RelationManagers;
use App\Models\IotDevice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IotDeviceResource extends Resource
{
    protected static ?string $model = IotDevice::class;

    protected static ?string $modelLabel = 'IoT Device';

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

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
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Get Started'),
            ])
            ->emptyStateIcon('heroicon-o-cpu-chip')
            ->emptyStateHeading('No IoT Devices Found')
            ->emptyStateDescription('Start by registering your IoT device to monitor and manage it through this platform.');
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
            'index' => Pages\ListIotDevices::route('/'),
            'create' => Pages\RegisterIotDevice::route('/register'),
            'edit' => Pages\EditIotDevice::route('/{record}/edit'),
        ];
    }
}
