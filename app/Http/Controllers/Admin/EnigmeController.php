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
        $ville = Ville::where('user_id', auth()->id())->first();
        if (!$ville) {
            return Inertia::render('Admin/Enigmes/Index', ['enigmes' => [], 'lieux' => []]);
        }

        $lieux = Lieu::where('ville_id', $ville->id)->get();
        $enigmes = Enigme::whereIn('lieu_id', $lieux->pluck('id'))
            ->with(['lieu', 'indices'])
            ->get();

        return Inertia::render('Admin/Enigmes/Index', [
            'enigmes' => $enigmes,
            'lieux' => $lieux
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
            'is_bonus' => 'nullable|boolean',
            'indices' => 'nullable|array',
            'indices.*.contenu' => 'required|string',
            'indices.*.penalite' => 'required|integer|min:0',
        ]);

        $data = $request->except('indices');

        // Validation personnalisée : Unicité du niveau pour les énigmes principales d'un lieu
        if (!($data['is_bonus'] ?? false)) {
            $exists = Enigme::where('lieu_id', $data['lieu_id'])
                ->where('niveau', $data['niveau'])
                ->where('is_bonus', false)
                ->exists();

            if ($exists) {
                return redirect()->back()->withErrors(['niveau' => 'Une énigme principale de niveau ' . $data['niveau'] . ' existe déjà pour ce lieu. Chaque énigme doit avoir un niveau unique (1, 2 ou 3).']);
            }

            // Optionnel : Vérifier qu'il n'y a pas plus de 3 énigmes principales
            $count = Enigme::where('lieu_id', $data['lieu_id'])
                ->where('is_bonus', false)
                ->count();
            
            if ($count >= 3) {
                return redirect()->back()->withErrors(['lieu_id' => 'Ce lieu possède déjà 3 énigmes principales. Vous ne pouvez ajouter que des énigmes bonus.']);
            }
        }
        
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
            'is_bonus' => 'nullable|boolean',
        ]);

        $data = $request->all();

        // Validation personnalisée : Unicité du niveau pour les énigmes principales d'un lieu
        if (!($data['is_bonus'] ?? false)) {
            $exists = Enigme::where('lieu_id', $enigme->lieu_id)
                ->where('niveau', $data['niveau'])
                ->where('is_bonus', false)
                ->where('id', '!=', $enigme->id)
                ->exists();

            if ($exists) {
                return redirect()->back()->withErrors(['niveau' => 'Une énigme principale de niveau ' . $data['niveau'] . ' existe déjà pour ce lieu.']);
            }
        }

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
