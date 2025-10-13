<?php

namespace App\Filament\Pages;

use App\Models\Question;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Help extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?string $navigationGroup = 'Learning & Support';

    protected static string $view = 'filament.pages.help';

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\SubmittedQueryTable::class,
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Ask a Question')
                    ->aside()
                    ->description('Need help? Submit your question here and our support team will get back to you as soon as possible. Please provide specific details about your issue to help us assist you more effectively.')
                    ->schema([
                        Select::make('subject_category')
                            ->label('Subject Category')
                            ->options([
                                // 'Billing Inquiry' => 'Billing Inquiry',
                                'Technical Support' => 'Technical Support',
                                'Account Issue' => 'Account Issue',
                                'Device Configuration' => 'Device Configuration',
                                'Energy Monitoring' => 'Energy Monitoring',
                                'App Navigation' => 'App Navigation',
                                'Data Export' => 'Data Export',
                                'Notifications' => 'Notifications',
                                'Security & Privacy' => 'Security & Privacy',
                                'Other' => 'Other (Custom Subject)',
                            ])
                            ->placeholder('Select a category or choose "Other" for custom subject')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === 'Other') {
                                    $set('subject', '');
                                } else {
                                    $set('subject', $state ?? '');
                                }
                            }),
                        TextInput::make('subject')
                            ->minLength(5)
                            ->maxLength(255)
                            ->required()
                            ->reactive()
                            ->disabled(fn (callable $get) => ! $get('subject_category') || ($get('subject_category') && $get('subject_category') !== 'Other'))
                            ->helperText(fn (callable $get) => $get('subject_category') === 'Other'
                                ? 'Please enter your custom subject'
                                : ($get('subject_category') ? 'Subject auto-filled from category selection' : 'Please select a category above first')
                            ),
                        RichEditor::make('body')
                            ->minLength(10)
                            ->maxLength(5000)
                            ->required(),
                        ViewField::make('legend')
                            ->view('filament.customs.legend'),
                    ]),

            ])
            ->statePath('data');
    }

    public function mount(): void
    {
        $this->form->fill([
            'subject_category' => '',
            'subject' => '',
            'body' => '',
        ]);
    }

    public function submit(): void
    {
        Question::create([
            'user_id' => auth()->id(),
            'subject' => $this->data['subject'],
            'body' => $this->data['body'],
        ]);

        Notification::make()
            ->title('Question Submitted')
            ->body('Your question has been submitted successfully. Our support team will get back to you shortly.')
            ->success()
            ->send();

        $this->form->fill([
            'subject_category' => '',
            'subject' => '',
            'body' => '',
        ]);

        // Refresh the table widget
        $this->dispatch('refresh-submitted-queries');
    }
}
