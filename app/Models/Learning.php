<?php

namespace App\Models;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Learning extends Model
{
    protected $fillable = ['name', 'slug', 'content', 'type', 'status', 'cover_image'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($learningResource) {
            if (empty($learningResource->slug)) {
                $slug = Str::slug($learningResource->name);
                $originalSlug = $slug;
                $count = 1;

                while (static::whereSlug($slug)->exists()) {
                    $slug = $originalSlug . '-' . $count++;
                }
                $learningResource->slug = $slug;
            }
        });

        

        static::updating(function ($learningResource) {
            // Optionally, regenerate slug on update if name changes and slug is not manually set
            if ($learningResource->isDirty('name') && !$learningResource->isDirty('slug')) {
                 $slug = Str::slug($learningResource->name);
                 $originalSlug = $slug;
                 $count = 1;

                 // Ensure uniqueness, excluding the current model instance
                 while (static::whereSlug($slug)->where('id', '!=', $learningResource->id)->exists()) {
                     $slug = $originalSlug . '-' . $count++;
                 }
                 $learningResource->slug = $slug;
            }
        });

        static::updated(function ($learningResource) {
            if($learningResource->status == 'published') {
                Notification::make()
                    ->title($learningResource->name . ' has been published')    
                    ->body('Check out the new learning resource that has been published to save more energy and cost.')
                    ->success()
                    ->sendToDatabase(User::all());
            }
        });
    }

    public function getDescriptionAttribute()
    {
        return Str::limit(strip_tags($this->content), 150);
    }

    public function getImageUrlAttribute()
    {
        return $this->cover_image ? Storage::url($this->cover_image) : null;
    }

    
}
