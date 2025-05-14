<?php

namespace App\Filament\Resources\LearningResource\Pages;

use App\Filament\Resources\LearningResource;
use Filament\Resources\Pages\Page;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class ListLearnings extends Page
{
    use WithPagination;
    
    protected static string $resource = LearningResource::class;

    protected static string $view = 'filament.resources.learning-resource.pages.list-learnings';

    protected static ?string $title = 'Learning Resources';

    public $records;
    
    #[Url(keep: true)]
    public $search = '';

    public function mount()
    {
        $this->loadResources();
    }

    public function loadResources()
    {
        $query = LearningResource::getModel()::where('status', 'published')->orderBy('created_at', 'desc');
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('content', 'like', "%{$this->search}%")
                  ->orWhere('type', 'like', "%{$this->search}%");
            });
        }
        
        $this->records = $query->get();
    }
    
    public function performSearch()
    {
        $this->loadResources();
    }

    public function updatedSearch()
    {
        $this->loadResources();
    }

    public function clearSearch()
    {
        $this->reset('search');
        $this->loadResources();
    }

    public function showRoute($slug)
    {
        return ShowLearning::getUrl(['slug' => $slug]);
    }
}
