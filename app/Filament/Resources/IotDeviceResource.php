<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IotDeviceResource\Pages;
use App\Filament\Resources\IotDeviceResource\RelationManagers;
use App\Models\IotDevice;
use App\Services\IotDeviceService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IotDeviceResource extends Resource
{
    protected static ?string $model = IotDevice::class;

    protected static ?string $modelLabel = 'IoT Device';

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationGroup = 'Device Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('IoT Device Information')
                    ->aside()
                    ->description('Manage the details of the IoT device.')
                    ->columns(1)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->helperText('Enter the name of the IoT device'),

                        Forms\Components\TextInput::make('device_uuid')
                            ->label('UUID')
                            ->disabled()
                            ->required()
                            ->helperText('Enter the UUID of the IoT device'),

                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->helperText('Enter the password of the IoT device')
                            ->password()
                            ->live(debounce: 500)
                            ->confirmed()
                            ->revealable()
                            ->rules([
                                'min:12',
                                'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/',
                            ])
                            ->validationMessages([
                                'min' => 'Password must be at least 12 characters long',
                                'regex' => 'Password must include at least one uppercase letter, one number, and one special character',
                            ]),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Confirm Password')
                            ->required()
                            ->helperText('Confirm the password for the IoT device')
                            ->visible(fn(Get $get) => filled($get('password')))
                            ->password()
                            ->revealable()
                            ->same('password'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('My Connected IoT Devices')
            ->description('View and manage the smart devices connected to your account. ')
            ->emptyStateIcon('heroicon-o-cpu-chip')
            ->emptyStateHeading('No IoT Devices Found')
            ->emptyStateDescription('Start by registering your IoT device to monitor and manage it through this platform.')
            ->columns([
                Stack::make([
                    Tables\Columns\TextColumn::make('name')
                        ->weight(FontWeight::Bold)
                        ->size(TextColumnSize::Large),
                    Tables\Columns\TextColumn::make('device_uuid')
                        ->label('Device UUID')
                        ->color('gray')
                        ->prefix('UUID: ')
                        ->size(TextColumnSize::ExtraSmall)
                        ->weight(FontWeight::ExtraLight)
                        ->searchable(),
                    Tables\Columns\TextColumn::make('socket_count')
                        ->label('Sockets')
                        ->color('gray')
                        ->prefix('Total Sockets: ')
                        ->size(TextColumnSize::ExtraSmall)
                        ->weight(FontWeight::ExtraLight)
                        ->searchable(),
                ])
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Manage'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk-unlink')
                        ->label('Unlink')
                        ->requiresConfirmation()
                        ->color('danger')
                        ->action(function (Collection $records, IotDeviceService $iotDeviceService) {
                            $records->each(function (IotDevice $record) use ($iotDeviceService) {
                                $iotDeviceService->unlinkDevice($record->device_uuid);
                            });

                            Notification::make()
                                ->success()
                                ->title('IoT Devices Unlinked')
                                ->body('The selected IoT devices have been unlinked from your account.')
                                ->send();
                        }),
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $query->where('user_id', auth()->id());
            })
            ->contentGrid([
                'md' => 2,

            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Get Started'),
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
            'create' => Pages\RegisterIotDevice::route('/register'),
            'edit' => Pages\EditIotDevice::route('/{record}/edit'),
        ];
    }
}
