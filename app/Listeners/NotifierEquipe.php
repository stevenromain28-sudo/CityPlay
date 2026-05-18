<?php

namespace App\Listeners;

use App\Events\EnigmeResolue;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifierEquipe
{
    /**
     * Handle the event.
     */
    public function handle(EnigmeResolue $event): void
    {
        // Ici on pourrait envoyer des notifications réelles (push, etc.)
        Log::info("L'explorateur {$event->user->name} a résolu l'énigme : {$event->enigme->titre}");
    }
}
