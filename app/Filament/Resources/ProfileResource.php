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
                    
                Forms\Components\Section::make('Advanced Monitoring Settings')
                    ->description('Configure anomaly detection and notification preferences')
                    ->collapsible()
                    ->aside()
                    ->schema([
                        Forms\Components\Toggle::make('notify_on_anomaly')
                            ->label('Enable Anomaly Notifications')
                            ->helperText('Receive alerts when readings fall outside thresholds')
                            ->default(true),
                            
                        Forms\Components\Select::make('alert_sensitivity')
                            ->label('Alert Sensitivity')
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
                                'sms' => 'SMS Alerts',
                            ])
                            ->default(['app'])
                            ->helperText('Choose how you want to be notified about anomalies'),
                            
                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('voltage_threshold_high')
                                ->label('High Voltage Threshold')
                                ->numeric()
                                ->minValue(220)
                                ->maxValue(270)
                                ->default(250)
                                ->suffix('V')
                                ->helperText('Maximum acceptable voltage'),
                                
                            Forms\Components\TextInput::make('voltage_threshold_low')
                                ->label('Low Voltage Threshold')
                                ->numeric()
                                ->minValue(180)
                                ->maxValue(220)
                                ->default(210)
                                ->suffix('V')
                                ->helperText('Minimum acceptable voltage'),
                        ])->columns(2),
                        
                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('current_threshold_high')
                                ->label('High Current Threshold')
                                ->numeric()
                                ->minValue(0)
                                ->suffix('A')
                                ->helperText('Maximum acceptable current'),
                                
                            Forms\Components\TextInput::make('power_factor_threshold')
                                ->label('Power Factor Threshold')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(1)
                                ->default(0.85)
                                ->step(0.01)
                                ->helperText('Minimum acceptable power factor (0-1)'),
                        ])->columns(2),
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
