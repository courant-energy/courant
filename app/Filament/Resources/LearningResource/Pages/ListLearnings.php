<?php

namespace App\Filament\Resources\LearningResource\Pages;

use App\Filament\Resources\LearningResource;
use Filament\Resources\Pages\Page;

class ListLearnings extends Page
{
    protected static string $resource = LearningResource::class;

    protected static string $view = 'filament.resources.learning-resource.pages.list-learnings';

    protected static ?string $title = 'Learning Resources';

    public $records;

    public function mount()
    {
        $this->records = LearningResource::getModel()::where('status', 'published')->get();
    }

    public function showRoute($slug)
    {
        return ShowLearning::getUrl(['slug' => $slug]);
    }

}
