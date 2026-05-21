<?php

namespace App\Http\Controllers;

use App\Http\Requests\SessionJeuRequest;
use App\Models\SessionJeu;
use App\Services\SessionJeuService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SessionJeuController extends Controller
{
    protected $sessionService;

    public function __construct(SessionJeuService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    /**
     * Afficher le formulaire de création de session.
     */
    public function create()
    {
        return Inertia::render('Player/CreateSession');
    }

    /**
     * Créer une nouvelle session ou mettre à jour l'existante.
     */
    public function store(SessionJeuRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        // 1. Chercher si une session est déjà active pour ce joueur
        $existingSession = SessionJeu::whereHas('joueurs', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->whereIn('statut', ['actif', 'en_attente', 'pause', 'temps_epuise'])
        ->latest()
        ->first();

        if ($existingSession) {
            // Mettre à jour l'énigme courante si fournie
            if (isset($data['enigme_id'])) {
                $existingSession->update(['current_enigme_id' => $data['enigme_id']]);
            }
            
            // Si elle était en pause ou attente, on la reprend
            if ($existingSession->statut !== 'actif') {
                $this->sessionService->reprendreSession($existingSession);
            }

            return redirect()->route('player.game.jeu', $existingSession);
        }

        // 2. Sinon créer une nouvelle session
        $session = $this->sessionService->creerSession($user, $data);

        // On commence directement la session pour aller au jeu
        $this->sessionService->commencerSession($session);

        return redirect()->route('player.game.jeu', $session)
            ->with('success', 'Aventure commencée !');
    }

    /**
     * Afficher une session spécifique.
     */
    public function show(SessionJeu $session)
    {
        // $this->authorize('view', $session); // À implémenter dans les Policies

        return Inertia::render('Player/SessionDetail', [
            'session' => $session->load(['joueurs.user', 'ville']),
        ]);
    }

    /**
     * Commencer le jeu.
     */
    public function start(SessionJeu $session)
    {
        // $this->authorize('update', $session);

        $this->sessionService->commencerSession($session);

        return redirect()->route('player.jeu', $session);
    }

    /**
     * Pause / Reprise / Terminer.
     */
    public function updateStatus(Request $request, SessionJeu $session)
    {
        $action = $request->input('action');

        switch ($action) {
            case 'pause':
                // Sauvegarder le temps restant du frontend avant de mettre en pause
                if ($request->has('temps_restant')) {
                    $session->update(['temps_restant' => $request->input('temps_restant')]);
                }
                $this->sessionService->mettreEnPause($session);
                break;
            case 'reprendre':
                $this->sessionService->reprendreSession($session);
                break;
            case 'temps_epuise':
                $session->update(['statut' => 'temps_epuise', 'temps_restant' => 0]);
                break;
            case 'terminer':
                // Sauvegarder le temps restant du frontend avant de terminer
                if ($request->has('temps_restant')) {
                    $session->update(['temps_restant' => $request->input('temps_restant')]);
                }
                $this->sessionService->terminerSession($session);
                return redirect()->route('player.dashboard');
        }

        return back();
    }

    /**
     * Synchroniser le temps restant (Heartbeat) : Frontend envoie son temps, on l'enregistre.
     */
    public function heartbeat(Request $request, SessionJeu $session)
    {
        $request->validate([
            'temps_restant' => 'required|integer|min:0'
        ]);

        $session->update([
            'temps_restant' => $request->input('temps_restant'),
            'dernier_calcul_at' => now()
        ]);

        return response()->json([
            'statut' => $session->statut
        ]);
    }

    /**
     * Ajouter du temps supplémentaire.
     */
    public function addTime(Request $request, SessionJeu $session)
    {
        $request->validate([
            'minutes' => 'required|integer|min:1'
        ]);

        $this->sessionService->ajouterTemps($session, $request->minutes);

        return back()->with('success', $request->minutes . ' minutes ajoutées !');
    }
}
