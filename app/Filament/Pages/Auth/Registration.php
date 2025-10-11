<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Pages\Auth\Register as BaseRegister;

class Registration extends BaseRegister
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Split::make([
                    $this->getFirstNameFormComponent(),
                    $this->getLastNameFormComponent(),
                ])->columns(2),
                $this->getCompanyNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent()
                    ->helperText('Your password must be at least 8 characters long.'),
                $this->getPasswordConfirmationFormComponent(),
                ViewField::make('legend')
                    ->view('filament.customs.legend'),
            ]);
    }

    protected function getCompanyNameFormComponent(): Component
    {
        return TextInput::make('company_name')
            ->label('Company Name')
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected function getFirstNameFormComponent(): Component
    {
        return TextInput::make('first_name')
            ->label('First Name')
            ->regex('/^[a-zA-Z0-9\-]+$/')
            ->required()
            ->maxLength(255)
            ->autofocus();
    }
    protected function getLastNameFormComponent(): Component
    {
        return TextInput::make('last_name')
            ->label('Last Name')
            ->regex('/^[a-zA-Z0-9\-]+$/')
            ->required()
            ->maxLength(255)
            ->autofocus();
    }
}
