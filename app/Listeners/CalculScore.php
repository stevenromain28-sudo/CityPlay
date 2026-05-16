<?php

namespace App\Listeners;

use App\Events\EnigmeResolue;
use App\Services\ScoreService;
use App\Models\JoueurSession;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CalculScore
{
    protected $scoreService;

    /**
     * Create the event listener.
     */
    public function __construct(ScoreService $scoreService)
    {
        $this->scoreService = $scoreService;
    }

    /**
     * Handle the event.
     */
    public function handle(EnigmeResolue $event): void
    {
        // Calcul du score (pour l'instant on simule temps et indices)
        $scoreGagne = $this->scoreService->calculerScoreEnigme(
            $event->enigme,
            300, // 5 minutes simulées
            0    // 0 indices utilisés
        );

        // Mettre à jour le score du joueur dans cette session
        $joueurSession = JoueurSession::where('session_jeu_id', $event->session->id)
            ->where('user_id', $event->user->id)
            ->first();

        if ($joueurSession) {
            $joueurSession->increment('score', $scoreGagne);
            $joueurSession->increment('progression');
        }

        // Si mode coopératif, on met aussi à jour le score global de la session
        if ($event->session->mode === 'cooperatif') {
            $event->session->increment('score', $scoreGagne);
            $event->session->increment('progression');
        }
    }
}
