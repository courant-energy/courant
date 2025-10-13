<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfileResource\Pages;
use App\Models\Profile;
use Filament\Forms;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                        ViewField::make('legend')
                            ->view('filament.customs.legend'),
                        Forms\Components\Select::make('appliance_preset')
                            ->label('Appliance Preset')
                            ->searchable()
                            ->placeholder('Select a common appliance or leave blank for custom')
                            ->options(function () {
                                $appliances = Profile::getAppliancePresets();
                                $options = ['custom' => 'Custom Profile'];

                                foreach ($appliances as $appliance) {
                                    $options[json_encode($appliance)] = $appliance['name'].' ('.$appliance['expected_power'].'W)';
                                }

                                return $options;
                            })
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($state && $state !== 'custom') {
                                    $appliance = json_decode($state, true);
                                    $set('name', $appliance['name']);
                                    $set('expected_power', $appliance['expected_power']);
                                    $set('max_power_threshold', $appliance['max_power_threshold']);
                                    $set('min_power_threshold', $appliance['min_power_threshold']);
                                }
                            })
                            ->helperText('Select a preset to auto-fill power values, or choose Custom to enter your own')
                            ->hiddenOn('edit'),
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
                                ->minValue(fn (Get $get) => (int) $get('expected_power'))
                                ->placeholder('e.g., 120')
                                ->suffix('W')
                                ->helperText('Maximum acceptable power threshold in watts')
                                ->live(),
                            Forms\Components\TextInput::make('min_power_threshold')
                                ->required()
                                ->integer()
                                ->maxValue(fn (Get $get) => (int) $get('expected_power'))
                                ->minValue(0)
                                ->default(0)
                                ->placeholder('e.g., 80')
                                ->suffix('W')
                                ->helperText('Minimum acceptable power threshold in watts')
                                ->live(),
                            Forms\Components\TextInput::make('location')
                                ->nullable()
                                ->maxLength(255)
                                ->placeholder('e.g., Kitchen, Living Room')
                                ->helperText('Optional location for this device profile'),
                        ])->columns(3),
                    ])->aside(),

                Forms\Components\Section::make('Socket Scheduling')
                    ->description('Automatically turn sockets on/off at specific times')
                    ->collapsible()
                    ->aside()
                    ->schema([
                        Forms\Components\Toggle::make('scheduling_enabled')
                            ->label('Enable Scheduling')
                            ->helperText('Automatically control socket state based on time schedule')
                            ->default(false)
                            ->live(),

                        Forms\Components\Group::make([
                            Forms\Components\TimePicker::make('turn_on_time')
                                ->label('Turn On Time')
                                ->helperText('Time to automatically turn on the socket')
                                ->visible(fn (Get $get) => $get('scheduling_enabled'))
                                ->required(fn (Get $get) => $get('scheduling_enabled')),

                            Forms\Components\TimePicker::make('turn_off_time')
                                ->label('Turn Off Time')
                                ->helperText('Time to automatically turn off the socket')
                                ->visible(fn (Get $get) => $get('scheduling_enabled'))
                                ->required(fn (Get $get) => $get('scheduling_enabled')),
                        ])->columns(2),

                        Forms\Components\CheckboxList::make('schedule_days')
                            ->label('Schedule Days')
                            ->options([
                                'monday' => 'Monday',
                                'tuesday' => 'Tuesday',
                                'wednesday' => 'Wednesday',
                                'thursday' => 'Thursday',
                                'friday' => 'Friday',
                                'saturday' => 'Saturday',
                                'sunday' => 'Sunday',
                            ])
                            ->default(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])
                            ->helperText('Select which days the schedule should be active. Leave empty for all days.')
                            ->visible(fn (Get $get) => $get('scheduling_enabled'))
                            ->columns(3),

                        Forms\Components\Placeholder::make('schedule_preview')
                            ->label('Schedule Preview')
                            ->content(function (Get $get) {
                                if (! $get('scheduling_enabled') || ! $get('turn_on_time') || ! $get('turn_off_time')) {
                                    return 'Enable scheduling and set times to see preview';
                                }

                                $days = $get('schedule_days') ?: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                $daysText = count($days) === 7 ? 'Every day' : implode(', ', array_map('ucfirst', $days));

                                return "Socket will turn ON at {$get('turn_on_time')} and OFF at {$get('turn_off_time')} on {$daysText}";
                            })
                            ->visible(fn (Get $get) => $get('scheduling_enabled')),
                    ]),

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
                    Tables\Columns\TextColumn::make('location')
                        ->label('Location')
                        ->sortable()
                        ->searchable()
                        ->color('gray')
                        ->size('sm'),
                    Tables\Columns\TextColumn::make('status')
                        ->badge()
                        ->getStateUsing(function (Profile $record) {
                            return $record->is_active ? 'Active' : 'Inactive';
                        })
                        ->color(fn ($state) => $state === 'Active' ? 'success' : 'gray')
                        ->label('Status'),
                    Tables\Columns\TextColumn::make('scheduling_status')
                        ->badge()
                        ->getStateUsing(function (Profile $record) {
                            if (! $record->scheduling_enabled) {
                                return 'No Schedule';
                            }

                            $shouldBeOn = $record->shouldBeOn();
                            $nextChange = $record->getNextScheduleChange();

                            if ($nextChange) {
                                return $shouldBeOn ? 'Scheduled ON' : 'Scheduled OFF';
                            }

                            return 'Schedule Inactive';
                        })
                        ->color(function ($state) {
                            return match ($state) {
                                'Scheduled ON' => 'success',
                                'Scheduled OFF' => 'warning',
                                'Schedule Inactive' => 'gray',
                                'No Schedule' => 'gray',
                                default => 'gray'
                            };
                        })
                        ->label('Schedule'),
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
