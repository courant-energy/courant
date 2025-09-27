<?php

namespace App\Filament\Resources\SocketResource\Pages;

use App\Filament\Resources\SocketResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSocket extends EditRecord
{
    protected static string $resource = SocketResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\Action::make('activeProfile')
                ->hidden(fn () => ! $this->record->profile)
                ->color('gray')
                ->label($this->record->profile ? 'Profile: '.$this->record->profile->name : 'No Profile Assigned')
                ->icon('heroicon-o-identification')
                ->url(fn () => route('filament.app.resources.profiles.edit', $this->record->profile)),

        ];
    }
}
