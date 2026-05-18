<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\JoueurSession;
use App\Models\Lieu;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'roles' => $request->user()->getRoleNames(),
                    'permissions' => $request->user()->getAllPermissions()->pluck('name'),
                    'score' => $request->user()->hasRole('player') 
                        ? JoueurSession::where('user_id', $request->user()->id)->sum('score') 
                        : 0,
                ] : null,
            ],
            'ville' => $this->resolveVille($request),
            'lieu' => $this->resolveLieu($request),
        ];
    }

    protected function resolveVille(Request $request)
    {
        $ville = $request->route('ville') ?? $request->query('ville');
        
        // Sécurité : si c'est un tableau (objet passé en query string), on prend l'ID
        if (is_array($ville) && isset($ville['id'])) {
            $ville = $ville['id'];
        }

        if (!$ville) {
            $lieuInput = $request->route('lieu') ?? $request->query('lieu');
            
            // Si le lieu est un tableau, on extrait l'ID
            $lieuId = is_array($lieuInput) ? ($lieuInput['id'] ?? null) : $lieuInput;

            if ($lieuId) {
                // On s'assure de ne pas passer un tableau à find() pour éviter de récupérer une Collection
                $id = $lieuId instanceof \App\Models\Lieu ? $lieuId->id : $id = $lieuId;
                $lieu = \App\Models\Lieu::find($id);
                return $lieu ? $lieu->ville_id : null;
            }
        }

        return $ville instanceof \App\Models\Ville ? $ville->id : $ville;
    }

    protected function resolveLieu(Request $request)
    {
        $lieu = $request->route('lieu') ?? $request->query('lieu');
        
        // Sécurité : si c'est un tableau, on prend l'ID
        if (is_array($lieu) && isset($lieu['id'])) {
            return $lieu['id'];
        }

        return $lieu instanceof \App\Models\Lieu ? $lieu->id : $lieu;
    }
}
