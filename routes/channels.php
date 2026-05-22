<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\SessionJeu;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('session.{sessionId}', function ($user, $sessionId) {
    // Vérifier que l'utilisateur fait bien partie de cette session
    $session = SessionJeu::find($sessionId);
    if (!$session) {
        return false;
    }

    // Vérifier si c'est une session individuelle ou d'équipe
    if ($session->equipe_id) {
        // Session d'équipe : vérifier que l'utilisateur est dans l'équipe
        if ($user->equipe_id === $session->equipe_id) {
            return ['id' => $user->id, 'name' => $user->name];
        }
    } else {
        // Session individuelle : vérifier que l'utilisateur est le propriétaire
        if ($user->id === $session->proprietaire_id) {
            return ['id' => $user->id, 'name' => $user->name];
        }
    }

    return false;
});
