<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Services\InvitationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvitationController extends Controller
{
    protected $invitationService;

    public function __construct(InvitationService $invitationService)
    {
        $this->invitationService = $invitationService;
    }

    /**
     * Afficher la page d'invitation.
     */
    public function show(string $token)
    {
        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation || !$invitation->estValide()) {
            return Inertia::render('Invitation/Expired', [
                'message' => "Cette invitation n'est plus valide."
            ]);
        }

        // If not authenticated, redirect to login which sets intended URL
        if (!auth()->check()) {
            return redirect()->guest(route('login'));
        }

        return Inertia::render('Invitation/Choix', [
            'invitation' => $invitation->load('inviteur'),
            'auth' => ['user' => auth()->user()],
        ]);
    }

    /**
     * Créer une nouvelle invitation.
     */
    public function store(Request $request)
    {
        $request->validate([
            'duree_minutes' => 'integer|min:5|max:1440', // 5 min à 24h
            'max_utilisations' => 'integer|min:1|max:100',
        ]);

        $invitation = $this->invitationService->creerInvitationEquipe(
            $request->user(),
            $request->input('duree_minutes', 60),
            $request->input('max_utilisations', 10)
        );

        return redirect()->route('player.dashboard');
    }

    /**
     * Accepter une invitation.
     */
    public function accept(Request $request, string $token)
    {
        $request->validate([
            'choix' => 'required|in:solo,equipe',
        ]);

        try {
            $resultat = $this->invitationService->accepterInvitation(
                $token,
                $request->user(),
                $request->choix
            );

            return redirect()->route('player.dashboard')
                ->with('success', $resultat['message']);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
