<?php

namespace App\Filament\Admin\Resources\LearningResource\Pages;

use App\Filament\Admin\Resources\LearningResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLearning extends EditRecord
{
    protected static string $resource = LearningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        switch ($this->record->status) {
            case 'published':
                return 'Learning Resource Published Successfully';
            case 'archived':
                return 'Learning Resource Archived Successfully';
            case 'draft':
                return 'Learning Resource Saved as Draft Successfully';
            default:
                return 'Learning Resource Updated Successfully';
        }
    }
}
