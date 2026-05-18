<?php

namespace App\Services;

use App\Models\SessionJeu;
use App\Models\JoueurSession;
use App\Models\Ville;
use App\Models\User;
use App\Events\SessionCommencee;
use Illuminate\Support\Facades\DB;
use Exception;

class SessionJeuService
{
    /**
     * Créer une nouvelle session de jeu.
     */
    public function creerSession(User $owner, array $data): SessionJeu
    {
        return DB::transaction(function () use ($owner, $data) {
            $session = SessionJeu::create([
                'ville_id' => $data['ville_id'],
                'proprietaire_id' => $owner->id,
                'mode' => $data['mode'], // cooperatif ou mercenaire
                'statut' => 'en_attente',
                'score' => 0,
                'progression' => 0,
            ]);

            // Ajouter le propriétaire comme premier joueur
            JoueurSession::create([
                'session_jeu_id' => $session->id,
                'user_id' => $owner->id,
                'type' => 'proprietaire',
                'score' => 0,
                'progression' => 0,
            ]);

            return $session;
        });
    }

    /**
     * Rejoindre une session existante.
     */
    public function rejoindreSession(User $user, SessionJeu $session, string $type = 'partenaire'): JoueurSession
    {
        if ($session->joueurs()->count() >= 10) {
            throw new Exception("La session est complète (max 10 joueurs).");
        }

        return JoueurSession::firstOrCreate(
            ['session_jeu_id' => $session->id, 'user_id' => $user->id],
            ['type' => $type, 'score' => 0, 'progression' => 0]
        );
    }

    /**
     * Commencer la session.
     */
    public function commencerSession(SessionJeu $session): bool
    {
        if ($session->statut !== 'en_attente') {
            return false;
        }

        $session->update([
            'statut' => 'actif',
            'commence_le' => now(),
        ]);

        event(new SessionCommencee($session));

        return true;
    }

    /**
     * Mettre en pause la session.
     */
    public function mettreEnPause(SessionJeu $session): bool
    {
        if ($session->statut !== 'actif') {
            return false;
        }

        return $session->update(['statut' => 'pause']);
    }

    /**
     * Reprendre la session.
     */
    public function reprendreSession(SessionJeu $session): bool
    {
        if ($session->statut !== 'pause') {
            return false;
        }

        return $session->update(['statut' => 'actif']);
    }

    /**
     * Abandonner la session.
     */
    public function abandonnerSession(SessionJeu $session): bool
    {
        return $session->update([
            'statut' => 'termine',
            'termine_le' => now(),
        ]);
    }
}
