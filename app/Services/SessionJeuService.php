<?php

namespace App\Services;

use App\Models\SessionJeu;
use App\Models\JoueurSession;
use App\Models\Ville;
use App\Models\User;
use App\Models\Equipe;
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
        $dureeMinutes = $data['duree'] ?? 45;
        
        if ($dureeMinutes < 45) {
            throw new Exception("La durée minimale d'une session est de 45 minutes.");
        }

        return DB::transaction(function () use ($owner, $data, $dureeMinutes) {
            $session = SessionJeu::create([
                'ville_id' => $data['ville_id'],
                'proprietaire_id' => $owner->id,
                'mode' => $data['mode'], // cooperatif ou mercenaire
                'moyen_transport' => $data['moyen_transport'] ?? 'pied',
                'current_enigme_id' => $data['enigme_id'] ?? null,
                'statut' => 'en_attente',
                'duree_initiale' => $dureeMinutes,
                'temps_restant' => $dureeMinutes * 60,
                'score' => 0,
                'progression' => 0,
                'equipe_id' => $data['equipe_id'] ?? null,
            ]);

            // Si c'est une session d'équipe, ajouter TOUS les membres de l'équipe
            if (isset($data['equipe_id'])) {
                $equipe = Equipe::findOrFail($data['equipe_id']);
                $membres = $equipe->membres;
                
                foreach ($membres as $membre) {
                    JoueurSession::firstOrCreate([
                        'session_jeu_id' => $session->id,
                        'user_id' => $membre->id,
                    ], [
                        'type' => $membre->id === $equipe->chef_id ? 'proprietaire' : 'partenaire',
                        'score' => 0,
                        'progression' => 0,
                    ]);
                }
            } else {
                // Sinon, ajouter seulement le propriétaire
                JoueurSession::create([
                    'session_jeu_id' => $session->id,
                    'user_id' => $owner->id,
                    'type' => 'proprietaire',
                    'score' => 0,
                    'progression' => 0,
                ]);
            }

            return $session;
        });
    }

    /**
     * Ajouter un membre d'équipe à une session existante.
     */
    public function ajouterMembreEquipeASession(User $user, SessionJeu $session, string $type = 'partenaire'): JoueurSession
    {
        if ($session->joueurs()->count() >= 10) {
            throw new Exception("La session est complète (max 10 joueurs).");
        }

        return JoueurSession::firstOrCreate([
            'session_jeu_id' => $session->id,
            'user_id' => $user->id,
        ], [
            'type' => $type,
            'score' => 0,
            'progression' => 0,
        ]);
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
            'dernier_calcul_at' => now(),
        ]);

        event(new SessionCommencee($session));

        return true;
    }

    /**
     * Mettre en pause la session : le frontend est le maître du temps, on ne calcule pas.
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
        if ($session->statut !== 'pause' && $session->statut !== 'temps_epuise') {
            return false;
        }

        return $session->update([
            'statut' => 'actif',
            'dernier_calcul_at' => now(),
        ]);
    }

    /**
     * Calculer et mettre à jour le temps restant d'une session.
     */
    public function calculerTempsRestant(SessionJeu $session): int
    {
        if ($session->statut !== 'actif') {
            return $session->temps_restant;
        }

        if (!$session->dernier_calcul_at) {
            $session->update(['dernier_calcul_at' => now()]);
            return $session->temps_restant;
        }

        $maintenant = now();
        $ecoule = $maintenant->diffInSeconds($session->dernier_calcul_at);
        
        $nouveauTempsRestant = max(0, $session->temps_restant - $ecoule);
        
        $data = [
            'temps_restant' => $nouveauTempsRestant,
            'dernier_calcul_at' => $maintenant,
        ];

        if ($nouveauTempsRestant <= 0) {
            $data['statut'] = 'temps_epuise';
        }

        $session->update($data);

        return $nouveauTempsRestant;
    }

    /**
     * Ajouter du temps supplémentaire à une session.
     */
    public function ajouterTemps(SessionJeu $session, int $minutes): bool
    {
        if ($session->statut === 'termine') {
            return false;
        }

        $session->increment('temps_restant', $minutes * 60);
        
        $data = [];
        // Si la session était en temps épuisé, on la repousse directement à actif pour reprendre le jeu immédiatement
        if ($session->statut === 'temps_epuise') {
            $data['statut'] = 'actif';
        }

        // On réinitialise le dernier calcul pour que le nouveau temps soit pris en compte proprement
        $data['dernier_calcul_at'] = now();
        
        $session->update($data);

        return true;
    }

    /**
     * Terminer la session.
     */
    public function terminerSession(SessionJeu $session): bool
    {
        return $session->update([
            'statut' => 'termine',
            'termine_le' => now(),
        ]);
    }
}
