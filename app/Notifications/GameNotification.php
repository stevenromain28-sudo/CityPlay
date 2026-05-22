<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GameNotification extends Notification
{
    use Queueable;

    public $message;
    public $type;
    public $enigmeTitre;

    /**
     * Create a new notification instance.
     */
    public function __construct($message, $type = 'info', $enigmeTitre = null)
    {
        $this->message = $message;
        $this->type = $type;
        $this->enigmeTitre = $enigmeTitre;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'type' => $this->type,
            'enigme_titre' => $this->enigmeTitre,
        ];
    }
}
