<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EnigmeResolue implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $joueur;
    public $user;
    public $enigme;
    public $lieu;
    public $session;

    /**
     * Create a new event instance.
     */
    public function __construct($joueur, $enigme, $lieu, $session)
    {
        $this->joueur = $joueur;
        $this->user = $joueur;
        $this->enigme = $enigme;
        $this->lieu = $lieu;
        $this->session = $session;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('session.' . $this->session->id),
        ];
    }
}
