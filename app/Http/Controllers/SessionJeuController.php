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
     * Créer une nouvelle session.
     */
    public function store(SessionJeuRequest $request)
    {
        $session = $this->sessionService->creerSession(
            $request->user(),
            $request->validated()
        );

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
     * Pause / Reprise / Abandon.
     */
    public function updateStatus(Request $request, SessionJeu $session)
    {
        $action = $request->input('action');

        switch ($action) {
            case 'pause':
                $this->sessionService->mettreEnPause($session);
                break;
            case 'reprendre':
                $this->sessionService->reprendreSession($session);
                break;
            case 'abandonner':
                $this->sessionService->abandonnerSession($session);
                return redirect()->route('player.dashboard');
        }

        return back();
    }
}
