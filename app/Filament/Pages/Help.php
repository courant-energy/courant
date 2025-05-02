<?php

namespace App\Filament\Pages;

use App\Models\Question;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
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
                        TextInput::make('subject')
                            ->minLength(5)
                            ->maxLength(255)
                            ->required(),
                        RichEditor::make('body')
                            ->minLength(10)
                            ->maxLength(5000)
                            ->required(),
                    ])

            ])
            ->statePath('data');
    }

    public function mount(): void
    {
        $this->form->fill([
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
            'subject' => '',
            'body' => '',
        ]);
    }
}
