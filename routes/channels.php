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
        return $user->equipe_id === $session->equipe_id;
    } else {
        // Session individuelle : vérifier que l'utilisateur est le propriétaire
        return $user->id === $session->proprietaire_id;
    }
});
