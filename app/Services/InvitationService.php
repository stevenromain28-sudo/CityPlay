<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Equipe;
use App\Models\SessionJeu;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;

class InvitationService
{
    /**
     * Créer une invitation avec lien à durée limitée pour rejoindre une équipe ou jouer en solo.
     */
    public function creerInvitationEquipe(User $inviteur, int $dureeMinutes = 60, int $maxUtilisations = 10): Invitation
    {
        return DB::transaction(function () use ($inviteur, $dureeMinutes, $maxUtilisations) {
            // On ne crée pas d'équipe ici - on attend que quelqu'un accepte
            return Invitation::create([
                'inviteur_id' => $inviteur->id,
                'equipe_id' => null, // Pas d'équipe pour l'instant
                'token' => Invitation::generateToken(),
                'expire_le' => now()->addMinutes($dureeMinutes),
                'max_utilisations' => $maxUtilisations,
                'utilisations' => 0,
                'type' => 'solo_equipe',
            ]);
        });
    }

    /**
     * Accepter une invitation et choisir entre solo ou équipe.
     */
    public function accepterInvitation(string $token, User $user, string $choix): array
    {
        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation) {
            throw new Exception("Invitation introuvable.");
        }

        if (!$invitation->estValide()) {
            throw new Exception("Cette invitation n'est plus valide (expirée ou utilisée trop de fois).");
        }

        $result = DB::transaction(function () use ($invitation, $user, $choix) {
            // Incrémenter le compteur d'utilisations
            $invitation->increment('utilisations');

            if ($choix === 'equipe') {
                $equipe = $invitation->equipe;
                
                if (!$equipe) {
                    // Si pas d'équipe, c'est la première personne à accepter en équipe : l'inviteur devient chef !
                    $equipe = Equipe::create([
                        'chef_id' => $invitation->inviteur_id, // L'inviteur est le chef !
                        'nom' => 'Équipe de ' . $invitation->inviteur->name,
                    ]);
                    
                    // Mettre à jour l'invitation avec l'équipe
                    $invitation->update(['equipe_id' => $equipe->id]);
                    
                    // Ajouter l'inviteur à l'équipe en tant que chef
                    $inviteur = $invitation->inviteur;
                    if ($inviteur) {
                        $inviteur->update([
                            'equipe_id' => $equipe->id,
                            'role_equipe' => 'chef',
                        ]);
                    }
                    
                    // Ajouter l'utilisateur qui a accepté en tant que membre
                    $user->update([
                        'equipe_id' => $equipe->id,
                        'role_equipe' => 'membre',
                    ]);
                    
                    return [
                        'type' => 'equipe',
                        'equipe' => $equipe,
                        'message' => "Vous avez rejoint l'équipe de {$inviteur->name} !",
                    ];
                } else {
                    // Si équipe existe déjà : ajouter en tant que membre
                    $user->update([
                        'equipe_id' => $equipe->id,
                        'role_equipe' => 'membre',
                    ]);
                    return [
                        'type' => 'equipe',
                        'equipe' => $equipe,
                        'message' => "Vous avez rejoint l'équipe de {$equipe->chef->name} !",
                    ];
                }
            } else {
                // Jouer en solo (ne pas rejoindre l'équipe)
                return [
                    'type' => 'solo',
                    'message' => "Vous avez choisi de jouer en solo !",
                ];
            }
        });

        return $result;
    }

    /**
     * Générer le lien d'invitation.
     */
    public function genererLienInvitation(Invitation $invitation): string
    {
        return url('/invitation/' . $invitation->token);
    }
}
