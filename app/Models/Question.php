<?php

namespace App\Models;

use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'user_id',
        'subject',
        'body',
        'status',
        'ticket_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->ticket_id = 'TICKET-' . strtoupper(uniqid());
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsResolved()
    {
        $this->update(['status' => 'resolved']);

        Notification::make()
            ->title('Ticket'. $this->ticket_id.' marked as resolved.')
            ->success()
            ->sendToDatabase(
                $this->user,
            );
    }

    public function markAsRejected()
    {
        $this->update(['status' => 'rejected']);

        Notification::make()
            ->title('Ticket'. $this->ticket_id.' marked as rejected.')
            ->success()
            ->sendToDatabase(
                $this->user,
            );
    }

    public function markAsPending()
    {
        $this->update(['status' => 'pending']);
    }

    public function isResolved()
    {
        return $this->status === 'resolved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }
}
