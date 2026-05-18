<?php

namespace App\Events;

use App\Models\Enigme;
use App\Models\SessionJeu;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EnigmeResolue implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $session;
    public $enigme;
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct(SessionJeu $session, Enigme $enigme, User $user)
    {
        $this->session = $session;
        $this->enigme = $enigme;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('session.' . $this->session->id),
        ];
    }

    /**
     * Data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'user_name' => $this->user->name,
            'enigme_titre' => $this->enigme->titre,
            'nouveau_score' => $this->session->score,
        ];
    }
}
