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
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\Textarea;

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
                            // ->limit(50)
                            ->tooltip(fn($state) => $state)
                            ->weight(FontWeight::Bold)
                            ->icon('heroicon-o-chat-bubble-left-ellipsis'),

                        Infolists\Components\TextEntry::make('body')
                            ->label('Body')
                            ->formatStateUsing(fn(string $state): View => view(
                                'filament.infolists.components.question-detail',
                                ['state' => $state]
                            ))
                            // ->html()
                            ->icon('heroicon-o-document-text'),

                        Infolists\Components\TextEntry::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->visible(fn($record) => $record->status === 'rejected' && !empty($record->rejection_reason))
                            ->color('danger')
                            ->icon('heroicon-o-x-circle'),
                    ]),



                Infolists\Components\Section::make("Timestamps")
                    ->description('information about when the question was created and last updated.')
                    ->aside()
                    ->columnSpanFull()
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
            ->columns(2);
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
                TextColumn::make('rejection_reason')
                    ->label('Rejection Reason')
                    ->limit(50)
                    ->toggleable()
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Submitted At')
                    ->dateTime('F j, Y, g:i a')
                    ->sortable()
                    ->toggleable()


            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                
                Tables\Actions\Action::make('resolve')
                    ->label('Resolve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Resolve Question')
                    ->modalDescription('Are you sure you want to mark this question as resolved?')
                    ->action(fn($record) => $record->markAsResolved())
                    ->visible(fn($record) => $record->status === 'pending')
                    ->successNotificationTitle('Question marked as resolved'),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->placeholder('Please provide a reason for rejecting this question...')
                            ->rows(4)
                    ])
                    ->modalHeading('Reject Question')
                    ->modalDescription('Please provide a reason for rejecting this question.')
                    ->action(function ($record, array $data) {
                        $record->markAsRejected($data['rejection_reason']);
                    })
                    ->visible(fn($record) => $record->status === 'pending')
                    ->successNotificationTitle('Question marked as rejected'),

                Tables\Actions\Action::make('reopen')
                    ->label('Reopen')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reopen Question')
                    ->modalDescription('Are you sure you want to reopen this question?')
                    ->action(fn($record) => $record->markAsPending())
                    ->visible(fn($record) => in_array($record->status, ['resolved', 'rejected']))
                    ->successNotificationTitle('Question reopened'),

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
