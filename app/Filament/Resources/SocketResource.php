<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocketResource\Pages;
use App\Filament\Resources\SocketResource\RelationManagers;
use App\Models\Socket;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
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
                Forms\Components\Section::make('Socket Information')
                    ->description('Manage the details of the socket.')
                    ->columns(1)
                    ->aside()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->helperText('This field is read-only and displays the socket name')
                            ->disabled(),
                        Forms\Components\Select::make('iot_device_id')
                            ->relationship('iotDevice', 'name')
                            ->label('IoT Device')
                            ->required()
                            ->helperText('This field is read-only and shows the associated IoT device')
                            ->disabled(),
                        Forms\Components\Select::make('profile_id')
                            ->relationship('profile', 'name')
                            ->label('Profile')
                            ->suffixAction(
                                Action::make('createProfile')
                                    ->icon('heroicon-o-plus')
                                    ->tooltip('Create a new profile')
                                    ->action(function () {
                                        redirect(route('filament.app.resources.profiles.create'));
                                    })
                            )
                            ->helperText('Select the profile for this socket'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('My Active Sockets')
            ->description('Manage your sockets and their profiles.')
            ->emptyStateHeading('No Sockets Found')
            ->emptyStateDescription('No sockets have been registered yet. Sockets are automatically registered when you link your IoT device. Click the button below to link your IoT device.')
            ->emptyStateIcon('heroicon-o-light-bulb')
            ->emptyStateActions([
                Tables\Actions\Action::make('register')
                    ->label('Link your IoT Device')
                    ->url(fn() => route('filament.app.resources.iot-devices.create'))

            ])
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
                        ->color(fn($state) => $state === 'No Profile Selected' ? 'gray' : null),
                ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('iot_device')
                    ->relationship(
                        'iotDevice',
                        'name',
                        fn(Builder $query) =>
                        $query->where('user_id', auth()->id())

                    )
                    ->label('IoT Device')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
