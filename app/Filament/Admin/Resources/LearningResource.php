<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LearningResource\Pages;
use App\Filament\Admin\Resources\LearningResource\RelationManagers;
use App\Models\Learning;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class LearningResource extends Resource
{
    protected static ?string $model = Learning::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Help & Learn';

    protected static ?string $modelLabel = 'Learning Resource';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Title')
                            ->placeholder('Enter the resource title')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => 
                                $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                            
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(table: Learning::class, ignoreRecord: true)
                            ->placeholder('URL-friendly slug')
                            ->helperText('This will be automatically generated from the title, but you can modify it if needed.'),
                            
                        Forms\Components\Select::make('type')
                            ->options([
                                'article' => 'Article',
                                'tutorial' => 'Tutorial',
                                'guide' => 'Guide',
                                'tip' => 'Quick Tip',
                                'infographic' => 'Infographic',
                                'video' => 'Video',
                                'best-practice' => 'Best Practice',
                            ])
                            ->required()
                            ->label('Resource Type')
                            ->placeholder('Select the type of content'),
                            
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'archived' => 'Archived',
                            ])
                            ->default('draft')
                            ->required()
                            ->label('Publication Status'),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Content')
                    ->schema([
                        Forms\Components\FileUpload::make('cover_image')
                            ->image()
                            ->label('Cover Image')
                            ->helperText('Recommended size: 1200x600px. Max size: 5MB.')
                            ->maxSize(5120)
                            ->columnSpanFull(),
                            
                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->columnSpanFull()
                            ->placeholder('Write your content here...')
                            ->label('Article Content'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('No Learning Resources Found')
            ->emptyStateDescription('You can create a new learning resource by clicking the button below.')
            ->emptyStateIcon('heroicon-o-book-open')
            ->emptyStateActions([
                Tables\Actions\Action::make('create_learning')
                    ->label('Create Learning Resource')
                    ->url(route('filament.admin.resources.learnings.create')),
            ])
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('Cover')
                    ->circular(false)
                    ->square()
                    ->defaultImageUrl(fn () => asset('images/placeholder-resource.jpg'))
                    ->width(80)
                    ->height(80),

                Tables\Columns\TextColumn::make('name')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'article',
                        'success' => 'tutorial',
                        'warning' => 'guide',
                        'danger' => 'tip',
                        'info' => 'infographic',
                        'secondary' => 'video',
                    ])
                    ->sortable()
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'draft',
                        'success' => 'published',
                        'danger' => 'archived',
                    ])
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'article' => 'Article',
                        'tutorial' => 'Tutorial',
                        'guide' => 'Guide',
                        'tip' => 'Quick Tip',
                        'infographic' => 'Infographic',
                        'video' => 'Video',
                    ])
                    ->label('Resource Type'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ])
                    ->label('Status'),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Created from ' . $data['created_from'];
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Created until ' . $data['created_until'];
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('updateStatus')
                        ->label('Update Status')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->options([
                                    'draft' => 'Draft',
                                    'published' => 'Published',
                                    'archived' => 'Archived',
                                ])
                                ->required()
                                ->label('Status'),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    protected function getCreatedNotificationTitle($record): ?string
    {
        switch ($record->status) {
            case 'published':
                return 'Learning Resource Published Successfully';
            case 'archived':
                return 'Learning Resource Archived Successfully';
            default:
                return 'Learning Resource Created Successfully';
        }
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLearnings::route('/'),
            'create' => Pages\CreateLearning::route('/create'),
            'edit' => Pages\EditLearning::route('/{record}/edit'),
        ];
    }
}
