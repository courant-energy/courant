<?php

namespace App\Filament\Admin\Resources\LearningResource\Pages;

use App\Filament\Admin\Resources\LearningResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLearning extends CreateRecord
{
    protected static string $resource = LearningResource::class;

    protected function getCreatedNotificationTitle(): ?string
    {
        switch ($this->record->status) {
            case 'published':
                return 'Learning Resource Published Successfully';
            case 'archived':
                return 'Learning Resource Archived Successfully';
            case 'draft':
                return 'Learning Resource Saved as Draft Successfully';
            default:
                return 'Learning Resource Created Successfully';
        }
    }
}
