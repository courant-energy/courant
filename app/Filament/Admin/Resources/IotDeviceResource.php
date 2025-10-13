<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\IotDeviceResource\Pages;
use App\Models\IotDevice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Table;

class IotDeviceResource extends Resource
{
    protected static ?string $model = IotDevice::class;

    protected static ?string $modelLabel = 'IoT Device';

    protected static ?string $navigationGroup = 'Device Management';

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('device_uuid')
                    ->label('UUID')
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->helperText('Enter the UUID of the IoT device'),

                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->helperText('Enter the password of the IoT device')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => $state)
                    ->dehydrated(fn ($state) => filled($state))
                    ->live()
                    ->confirmed()
                    ->revealable(),

                Forms\Components\TextInput::make('password_confirmation')
                    ->label('Confirm Password')
                    ->required()
                    ->helperText('Confirm the password for the IoT device')
                    ->visible(fn (Get $get) => filled($get('password')))
                    ->password()
                    ->revealable()
                    ->same('password'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('device_uuid')
                    ->label('Device UUID')
                    ->color('gray')
                    ->searchable(),
                Tables\Columns\TextColumn::make('socket_count')
                    ->label('Sockets')
                    ->color('gray')
                    ->size(TextColumnSize::ExtraSmall)
                    ->weight(FontWeight::ExtraLight)
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->color('gray')
                    ->size(TextColumnSize::ExtraSmall)
                    ->weight(FontWeight::ExtraLight)
                    ->dateTime()
                    ->sortable(),
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
            'index' => Pages\ListIotDevices::route('/'),
            'create' => Pages\CreateIotDevice::route('/create'),
            'edit' => Pages\EditIotDevice::route('/{record}/edit'),
        ];
    }
}
