<?php

namespace App\Http\Controllers;

use App\Models\Ville;
use App\Models\Lieu;
use App\Models\Enigme;
use App\Models\User;
use App\Models\SessionJeu;
use Inertia\Inertia;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super_admin');
        
        if ($isSuperAdmin) {
            return Inertia::render('Admin/Dashboard', [
                'stats' => [
                    'villes_count' => Ville::count(),
                    'lieux_count' => Lieu::count(),
                    'enigmes_count' => Enigme::count(),
                    'users_count' => User::count(),
                    'sessions_count' => SessionJeu::count(),
                ],
                'ma_ville' => null,
                'villes' => Ville::where('actif', true)->orderBy('nom')->get(),
                'recent_lieux' => Lieu::latest()->take(4)->get(),
            ]);
        }

        // On récupère la ville associée à cet admin (une ville par admin)
        $maVille = Ville::withCount('lieux')
            ->where('user_id', $user->id)
            ->first();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'villes_count' => Ville::count(),
                'lieux_count' => $maVille ? $maVille->lieux_count : 0,
                'enigmes_count' => $maVille ? Enigme::whereIn('lieu_id', $maVille->lieux->pluck('id'))->count() : 0,
                'users_count' => User::count(),
                'sessions_count' => SessionJeu::count(),
            ],
            'ma_ville' => $maVille,
            'villes' => [],
            'recent_lieux' => $maVille 
                ? Lieu::where('ville_id', $maVille->id)->latest()->take(4)->get() 
                : [],
        ]);
    }
}
