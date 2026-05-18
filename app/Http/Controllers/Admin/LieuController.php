<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lieu;
use App\Models\Ville;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class LieuController extends Controller
{
    public function index()
    {
        $isSuperAdmin = auth()->user()->hasRole('super_admin');
        if ($isSuperAdmin) {
            $villes = Ville::all();
            $lieux = Lieu::with('contenuCulturel', 'ville')->get();
            // Pour la carte, on peut passer la première ville comme repère par défaut s'il y en a
            $ville = $villes->first();

            return Inertia::render('Admin/Lieux/Index', [
                'lieux' => $lieux,
                'ville' => $ville,
                'villes' => $villes,
                'isSuperAdmin' => true
            ]);
        }

        $ville = Ville::where('user_id', auth()->id())->first();
        $lieux = $ville ? Lieu::where('ville_id', $ville->id)->with('contenuCulturel')->get() : [];

        return Inertia::render('Admin/Lieux/Index', [
            'lieux' => $lieux,
            'ville' => $ville,
            'villes' => [],
            'isSuperAdmin' => false
        ]);
    }

    public function store(Request $request)
    {
        $isSuperAdmin = auth()->user()->hasRole('super_admin');

        $rules = [
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'localisation' => 'nullable|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'rayon' => 'required|integer|min:1',
            'difficulte' => 'required|integer|min:1|max:3',
            'duree_estimee' => 'nullable|integer',
            'image_principale' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        if ($isSuperAdmin) {
            $rules['ville_id'] = 'required|exists:villes,id';
        }

        $request->validate($rules);

        $data = $request->all();

        if ($isSuperAdmin) {
            $data['ville_id'] = $request->ville_id;
        } else {
            $ville = Ville::where('user_id', auth()->id())->firstOrFail();
            $data['ville_id'] = $ville->id;
        }

        if ($request->hasFile('image_principale')) {
            $path = $request->file('image_principale')->store('lieux/images', 'public');
            $data['image_principale'] = Storage::url($path);
        }

        Lieu::create($data);

        return redirect()->back()->with('success', 'Lieu ajouté avec succès !');
    }

    public function update(Request $request, Lieu $lieu)
    {
        $isSuperAdmin = auth()->user()->hasRole('super_admin');

        $rules = [
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'localisation' => 'nullable|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'rayon' => 'required|integer|min:1',
            'difficulte' => 'required|integer|min:1|max:3',
            'duree_estimee' => 'nullable|integer',
            'image_principale' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        if ($isSuperAdmin) {
            $rules['ville_id'] = 'required|exists:villes,id';
        }

        $request->validate($rules);

        $data = $request->all();

        if ($isSuperAdmin) {
            $data['ville_id'] = $request->ville_id;
        }

        if ($request->hasFile('image_principale')) {
            if ($lieu->image_principale && !str_contains($lieu->image_principale, 'backgrounds')) {
                $oldPath = str_replace('/storage/', '', $lieu->image_principale);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image_principale')->store('lieux/images', 'public');
            $data['image_principale'] = Storage::url($path);
        }

        $lieu->update($data);

        return redirect()->back()->with('success', 'Lieu mis à jour !');
    }

    public function destroy(Lieu $lieu)
    {
        if ($lieu->image_principale && !str_contains($lieu->image_principale, 'backgrounds')) {
            $oldPath = str_replace('/storage/', '', $lieu->image_principale);
            Storage::disk('public')->delete($oldPath);
        }
        $lieu->delete();
        return redirect()->back()->with('success', 'Lieu supprimé !');
    }
}
