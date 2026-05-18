<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enigme;
use App\Models\Lieu;
use App\Models\Indice;
use App\Models\Ville;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class EnigmeController extends Controller
{
    public function index()
    {
        $isSuperAdmin = auth()->user()->hasRole('super_admin');
        if ($isSuperAdmin) {
            $villes = Ville::all();
            $lieux = Lieu::with('ville')->get();
            $enigmes = Enigme::with(['lieu.ville', 'indices'])->get();

            return Inertia::render('Admin/Enigmes/Index', [
                'enigmes' => $enigmes,
                'lieux' => $lieux,
                'villes' => $villes,
                'isSuperAdmin' => true
            ]);
        }

        $ville = Ville::where('user_id', auth()->id())->first();
        if (!$ville) {
            return Inertia::render('Admin/Enigmes/Index', [
                'enigmes' => [],
                'lieux' => [],
                'villes' => [],
                'isSuperAdmin' => false
            ]);
        }

        $lieux = Lieu::where('ville_id', $ville->id)->get();
        $enigmes = Enigme::whereIn('lieu_id', $lieux->pluck('id'))
            ->with(['lieu.ville', 'indices'])
            ->get();

        return Inertia::render('Admin/Enigmes/Index', [
            'enigmes' => $enigmes,
            'lieux' => $lieux,
            'villes' => [],
            'isSuperAdmin' => false
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'lieu_id' => 'required|exists:lieux,id',
            'titre' => 'required|string|max:255',
            'contenu' => 'required|string',
            'reponse' => 'nullable|string',
            'niveau' => 'required|integer|min:1|max:3',
            'ordre' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg|max:5120',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'rayon' => 'nullable|integer',
            'verification_gps' => 'nullable|boolean',
            'indices' => 'nullable|array',
            'indices.*.contenu' => 'required|string',
            'indices.*.penalite' => 'required|integer|min:0',
        ]);

        $data = $request->except('indices');
        
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('enigmes/images', 'public');
            $data['image'] = Storage::url($path);
        }

        if ($request->hasFile('audio')) {
            $path = $request->file('audio')->store('enigmes/audio', 'public');
            $data['audio'] = Storage::url($path);
        }

        $enigme = Enigme::create($data);

        if ($request->has('indices')) {
            $indices = is_string($request->indices) ? json_decode($request->indices, true) : $request->indices;
            if (is_array($indices)) {
                foreach ($indices as $indiceData) {
                    $enigme->indices()->create($indiceData);
                }
            }
        }

        return redirect()->back()->with('success', 'Énigme créée !');
    }

    public function update(Request $request, Enigme $enigme)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'contenu' => 'required|string',
            'reponse' => 'nullable|string',
            'niveau' => 'required|integer|min:1|max:3',
            'ordre' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg|max:5120',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'rayon' => 'nullable|integer',
            'verification_gps' => 'nullable|boolean',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            if ($enigme->image && !str_contains($enigme->image, 'backgrounds')) {
                $oldPath = str_replace('/storage/', '', $enigme->image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('enigmes/images', 'public');
            $data['image'] = Storage::url($path);
        }

        if ($request->hasFile('audio')) {
            if ($enigme->audio) {
                $oldPath = str_replace('/storage/', '', $enigme->audio);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('audio')->store('enigmes/audio', 'public');
            $data['audio'] = Storage::url($path);
        }

        $enigme->update($data);

        // Update indices
        if ($request->has('indices')) {
            $enigme->indices()->delete();
            $indices = is_string($request->indices) ? json_decode($request->indices, true) : $request->indices;
            if (is_array($indices)) {
                foreach ($indices as $indiceData) {
                    $enigme->indices()->create($indiceData);
                }
            }
        }

        return redirect()->back()->with('success', 'Énigme mise à jour !');
    }

    public function destroy(Enigme $enigme)
    {
        if ($enigme->image && !str_contains($enigme->image, 'backgrounds')) {
            $oldPath = str_replace('/storage/', '', $enigme->image);
            Storage::disk('public')->delete($oldPath);
        }
        
        if ($enigme->audio) {
            $oldPath = str_replace('/storage/', '', $enigme->audio);
            Storage::disk('public')->delete($oldPath);
        }

        $enigme->delete();
        return redirect()->back()->with('success', 'Énigme supprimée !');
    }
}
