<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnomalyAlert extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public $severity,
        public $type,
        public $deviceName,
        public $socketName,
        public $message,
    )    
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->greeting('Hello!')
            ->subject('Anomaly Alert: ' . $this->type.' detected on '.$this->deviceName)
            ->line('An anomaly has been detected on your device.')
            ->line('Device: ' . $this->deviceName)
            ->line('Socket: ' . $this->socketName)
            ->line('Severity: ' . $this->severity)
            ->line('Alert Info: ' . $this->message)
            ->when($this->severity === 'critical', function ($message) {
                return $message->line('The socket has been turned off to prevent damage.');
            })
            ->line('Please check the device and take necessary actions.')
            ->line('You can view the details in your dashboard.')
            ->action('View Dashboard', url('/app/dashboard'))
            ->line('Thank you for using Courant!')
            ->salutation('Best regards, Courant Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
