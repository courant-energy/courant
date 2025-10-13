<?php

namespace App\Filament\Resources\LearningResource\Pages;

use App\Filament\Resources\LearningResource;
use Filament\Resources\Pages\Page;

class ShowLearning extends Page
{
    protected static string $resource = LearningResource::class;

    // protected static ?string $title = $record->title ?? 'Learning Resource';

    protected static string $view = 'filament.resources.learning-resource.pages.show-learning';

    public $record;

    public function mount($slug)
    {
        $this->record = LearningResource::getModel()::where('slug', $slug)->firstOrFail();
    }

    public function getTitle(): string
    {
        return $this->record->name ?? 'Learning Resource';
    }
}
