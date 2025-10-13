<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $navigationGroup = 'User Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\FileUpload::make('profile')
                            ->image()
                            ->directory('user-profiles')
                            ->avatar()
                            ->nullable(),
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('company_name')
                            ->maxLength(255)
                            ->placeholder('Company Name'),
                    ]),
                // Forms\Components\Section::make('Password')
                //     ->schema([
                //         Forms\Components\TextInput::make('password')
                //             ->password()
                //             ->revealable()
                //             ->dehydrated(fn ($state) => filled($state))
                //             ->required(fn (string $operation): bool => $operation === 'create')
                //             ->minLength(8)
                //             ->same('password_confirmation'),
                //         Forms\Components\TextInput::make('password_confirmation')
                //             ->password()
                //             ->revealable()
                //             ->required(fn (string $operation): bool => $operation === 'create')
                //             ->minLength(8)
                //             ->dehydrated(false),
                //     ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile')
                    ->getStateUsing(fn (User $record) => $record->getFilamentAvatarUrl())
                    ->circular(),
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'warning')
                    ->label('Verified')
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
                Tables\Columns\TextColumn::make('archived_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color(fn ($state) => $state ? 'danger' : 'success')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state ? 'Archived' : 'Active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('verified')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at')),
                Tables\Filters\Filter::make('unverified')
                    ->query(fn (Builder $query): Builder => $query->whereNull('email_verified_at')),
                Tables\Filters\Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->whereNull('archived_at'))
                    ->default(),
                Tables\Filters\Filter::make('archived')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('archived_at')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('archive')
                    ->label('Archive User')
                    ->icon('heroicon-o-archive-box')
                    ->requiresConfirmation()
                    ->color('warning')
                    ->hidden(fn (User $record): bool => $record->isArchived())
                    ->action(function (User $record) {
                        $record->archive();
                    })
                    ->successNotificationTitle('User archived successfully'),
                Tables\Actions\Action::make('unarchive')
                    ->label('Restore User')
                    ->icon('heroicon-o-archive-box-x-mark')
                    ->requiresConfirmation()
                    ->color('success')
                    ->visible(fn (User $record): bool => $record->isArchived())
                    ->action(function (User $record) {
                        $record->unarchive();
                    })
                    ->successNotificationTitle('User restored successfully'),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('archive')
                        ->label('Archive Selected')
                        ->icon('heroicon-o-archive-box')
                        ->requiresConfirmation()
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each(function (User $record) {
                                if (! $record->isArchived()) {
                                    $record->archive();
                                }
                            });
                        })
                        ->successNotificationTitle('Selected users archived successfully'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
