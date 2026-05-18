<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ville;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class VilleController extends Controller
{
    public function index()
    {
        $isSuperAdmin = auth()->user()->hasRole('super_admin');
        if ($isSuperAdmin) {
            return Inertia::render('Admin/Villes/Index', [
                'villes' => Ville::withCount('lieux')->with('user')->get(),
                'admins' => \App\Models\User::role('admin')->get(),
                'isSuperAdmin' => true
            ]);
        }

        $ville = Ville::withCount('lieux')->where('user_id', auth()->id())->first();
        return Inertia::render('Admin/Villes/Index', [
            'ville' => $ville,
            'isSuperAdmin' => false
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'required|string',
            'history' => 'nullable|string',
            'pays' => 'required|string|max:255',
            'population' => 'nullable|integer',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'rayon_action' => 'nullable|integer',
            'banniere' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $data = $request->only(['nom', 'description', 'history', 'pays', 'population', 'latitude', 'longitude', 'rayon_action', 'user_id']);
        if (!auth()->user()->hasRole('super_admin')) {
            $data['user_id'] = auth()->id();
        }
        $data['slug'] = Str::slug($request->nom);

        if ($request->hasFile('banniere')) {
            $path = $request->file('banniere')->store('villes/bannieres', 'public');
            $data['banniere'] = Storage::url($path);
        }

        Ville::create($data);

        return redirect()->back()->with('success', 'Ville créée avec succès !');
    }

    public function update(Request $request, Ville $ville)
    {
        // $this->authorize('update', $ville);

        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'required|string',
            'history' => 'nullable|string',
            'pays' => 'required|string|max:255',
            'population' => 'nullable|integer',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'rayon_action' => 'nullable|integer',
            'banniere' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $data = $request->only(['nom', 'description', 'history', 'pays', 'population', 'latitude', 'longitude', 'rayon_action', 'user_id']);
        if (!auth()->user()->hasRole('super_admin')) {
            unset($data['user_id']); // regular admin cannot reassign
        }
        $data['slug'] = Str::slug($request->nom);

        if ($request->hasFile('banniere')) {
            if ($ville->banniere && !str_contains($ville->banniere, 'backgrounds')) {
                $oldPath = str_replace('/storage/', '', $ville->banniere);
                Storage::disk('public')->delete($oldPath);
            }
            
            $path = $request->file('banniere')->store('villes/bannieres', 'public');
            $data['banniere'] = Storage::url($path);
        }

        $ville->update($data);

        return redirect()->back()->with('success', 'Ville mise à jour avec succès !');
    }

    public function destroy(Ville $ville)
    {
        if (!auth()->user()->hasRole('super_admin')) {
            abort(403, 'Action non autorisée');
        }

        if ($ville->banniere && !str_contains($ville->banniere, 'backgrounds')) {
            $oldPath = str_replace('/storage/', '', $ville->banniere);
            Storage::disk('public')->delete($oldPath);
        }

        $ville->delete();

        return redirect()->back()->with('success', 'Ville supprimée avec succès !');
    }
}
