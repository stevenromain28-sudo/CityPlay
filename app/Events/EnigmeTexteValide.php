<?php

namespace App\Events;

use App\Models\SessionJeu;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EnigmeTexteValide implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $joueur;
    public $enigme;
    public $session;

    /**
     * Create a new event instance.
     */
    public function __construct($joueur, $enigme, $session)
    {
        $this->joueur = $joueur;
        $this->enigme = $enigme;
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
