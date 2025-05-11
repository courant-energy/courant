<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\QuestionResource\Pages;
use App\Filament\Admin\Resources\QuestionResource\RelationManagers;
use App\Models\Question;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Components\Actions\Action;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $modelLabel = 'User Query';

    protected static ?string $navigationGroup = 'Help & Learn';

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Question')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->minLength(5)
                            ->maxLength(255)
                            ->required(),
                        Forms\Components\RichEditor::make('body')
                            ->minLength(10)
                            ->maxLength(5000)
                            ->required(),
                    ])
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Group::make()
                    ->columnSpan(2)
                    ->schema([
                        Infolists\Components\Section::make('Sender Information')
                            ->description('This information is based on the user who submitted the question.')
                            ->icon('heroicon-o-user-circle')
                            ->aside()
                            ->schema([
                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('User')
                                    ->copyable()
                                    ->icon('heroicon-o-user'),
                                Infolists\Components\TextEntry::make('user.email')
                                    ->label('Email')
                                    ->copyable()
                                    ->suffixAction(
                                        Action::make('send-email')
                                            ->label('Send Email')
                                            ->url(fn($record) => 'mailto:' . $record->user->email)
                                            ->icon('heroicon-o-envelope')
                                    )
                                    ->icon('heroicon-o-at-symbol'),

                            ]),
                        Infolists\Components\Section::make('Question Metadata')
                            ->description('This information are information about the question.')
                            ->icon('heroicon-o-question-mark-circle')
                            ->aside()
                            ->schema([
                                Infolists\Components\TextEntry::make('ticket_id')
                                    ->label('Ticket ID')
                                    ->icon('heroicon-o-ticket')
                                    ->copyable(),
                                   
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(function ($record) {
                                        return match ($record->status) {
                                            'pending' => 'warning',
                                            'resolved' => 'success',
                                            'rejected' => 'danger',
                                        };
                                    })
                                    ->icon(function ($record) {
                                        return match ($record->status) {
                                            'pending' => 'heroicon-o-clock',
                                            'resolved' => 'heroicon-o-check-circle',
                                            'rejected' => 'heroicon-o-x-circle',
                                        };
                                    }),
                            ]),

                        Infolists\Components\Section::make('Question Details')
                            ->description('This information are information about the question.')
                            ->icon('heroicon-o-document-text')
                            ->aside()
                            ->schema([
                                Infolists\Components\TextEntry::make('subject')
                                    ->label('Subject')
                                    ->weight(FontWeight::Bold)
                                    ->icon('heroicon-o-chat-bubble-left-ellipsis'),
                                Infolists\Components\TextEntry::make('body')
                                    ->label('Body')
                                    ->prose()
                                    ->icon('heroicon-o-document-text'),


                            ]),
                    ]),


                Infolists\Components\Section::make()
                    ->columnSpan(1)
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Submitted At')
                            ->dateTime('F j, Y, g:i a')
                            ->icon('heroicon-o-clock'),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('F j, Y, g:i a')
                            ->icon('heroicon-o-clock'),
                    ]),


            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('user.profile')
                    ->getStateUsing(function ($record) {
                        // Use the profile if it exists, otherwise fallback to a default avatar
                        return $record->user->profile ?? "https://ui-avatars.com/api/?name=" . urlencode($record->user->name) . "&color=7F9CF5&background=EBF4FF";
                    })
                    ->label('')
                    ->grow(false)
                    ->circular(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('ticket_id')
                    ->label('Ticket ID')
                    ->sortable()
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-ticket'),
                TextColumn::make('subject')
                    ->label('Subject')
                    ->sortable()
                    ->limit(50)
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->color(function ($record) {
                        return match ($record->status) {
                            'pending' => 'warning',
                            'resolved' => 'success',
                            'rejected' => 'danger',
                        };
                    })
                    ->badge()
                    ->icon(function ($record) {
                        return match ($record->status) {
                            'pending' => 'heroicon-o-clock',
                            'resolved' => 'heroicon-o-check-circle',
                            'rejected' => 'heroicon-o-x-circle',
                        };
                    }),
                TextColumn::make('created_at')
                    ->label('Submitted At')
                    ->dateTime('F j, Y, g:i a')
                    ->sortable()
                    ->toggleable()
                    

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                
            ])
            ->bulkActions([]);
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
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            // 'edit' => Pages\EditQuestion::route('/{record}/edit'),
            'view' => Pages\ViewQuestion::route('/{record}'),
        ];
    }
}
