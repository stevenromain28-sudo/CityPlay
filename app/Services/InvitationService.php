<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\SessionJeu;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class InvitationService
{
    /**
     * Créer une invitation pour un joueur.
     */
    public function envoyerInvitation(SessionJeu $session, string $email): Invitation
    {
        return Invitation::create([
            'session_jeu_id' => $session->id,
            'email' => $email,
            'token' => Str::random(32),
            'statut' => 'en_attente',
        ]);
        
        // Ici on pourrait ajouter l'envoi de mail réel
    }

    /**
     * Accepter une invitation.
     */
    public function accepterInvitation(string $token, User $user): bool
    {
        $invitation = Invitation::where('token', $token)
            ->where('statut', 'en_attente')
            ->first();

        if (!$invitation) {
            return false;
        }

        $sessionJeuService = new SessionJeuService();
        $sessionJeuService->rejoindreSession($user, $invitation->sessionJeu);

        $invitation->update([
            'statut' => 'accepte',
            'user_id' => $user->id
        ]);

        return true;
    }
}
