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
                                ->live(),
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
                                ->default(0)
                                ->placeholder('e.g., 80')
                                ->suffix('W')
                                ->helperText('Minimum acceptable power threshold in watts')
                                ->live(),
                        ])->columns(3),
                    ])->aside(),

                Forms\Components\Section::make('Advanced Monitoring Settings')
                    ->description('Configure anomaly detection and notification preferences')
                    ->collapsible()
                    ->aside()
                    ->schema([
                        Forms\Components\Toggle::make('notify_on_anomaly')
                            ->label('Enable Anomaly Notifications')
                            ->helperText('Receive alerts when readings fall outside thresholds')
                            ->default(true),
                        Forms\Components\Toggle::make('anomaly_detection_enabled')
                            ->label('Enable Anomaly Detection')
                            ->helperText('Use machine learning to detect anomalies')
                            ->default(true),

                        Forms\Components\Select::make('alert_sensitivity')
                            ->label('Alert Sensitivity')
                            ->hintIcon('heroicon-o-information-circle')
                            ->hintIconTooltip(
                                '
                                Low: Best for appliances with large power draws (like power tools).
                                Medium: Good for general appliance monitoring (like AC units).
                                High: Ideal for sensitive monitoring of most devices (including refrigerators).'
                            )
                            ->options([
                                'low' => 'Low (less alerts)',
                                'medium' => 'Medium (balanced)',
                                'high' => 'High (more alerts)',
                            ])
                            ->default('medium')
                            ->helperText('Adjust how sensitive the system is to minor threshold violations')
                            ->required(),

                        Forms\Components\CheckboxList::make('notification_channels')
                            ->label('Notification Channels')
                            ->options([
                                'app' => 'In-App Notifications',
                                'email' => 'Email Alerts',
                                // 'sms' => 'SMS Alerts',
                            ])
                            ->default(['app'])
                            ->helperText('Choose how you want to be notified about anomalies'),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('My Appliance Profiles')
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
                    Tables\Columns\TextColumn::make('status')
                        ->badge()
                        ->getStateUsing(function (Profile $record) {
                            return $record->is_active ? 'Active' : 'Inactive';
                        })
                        ->color(fn($state) => $state === 'Active' ? 'success' : 'gray')
                        ->label('Status')
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
