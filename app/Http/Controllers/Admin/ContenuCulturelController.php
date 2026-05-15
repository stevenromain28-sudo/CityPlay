<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContenuCulturel;
use App\Models\Lieu;
use App\Models\Ville;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ContenuCulturelController extends Controller
{
    public function index()
    {
        $ville = Ville::where('user_id', auth()->id())->first();
        if (!$ville) {
            return Inertia::render('Admin/ContenusCulturels/Index', [
                'contenus' => [],
                'lieux' => []
            ]);
        }

        $lieux = Lieu::where('ville_id', $ville->id)->get();
        $contenus = ContenuCulturel::whereIn('lieu_id', $lieux->pluck('id'))
            ->with('lieu')
            ->get();

        return Inertia::render('Admin/ContenusCulturels/Index', [
            'contenus' => $contenus,
            'lieux' => $lieux
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'lieu_id' => 'required|exists:lieux,id|unique:contenus_culturels,lieu_id',
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg|max:10240',
        ]);

        $data = $request->all();

        if ($request->hasFile('audio')) {
            $path = $request->file('audio')->store('culturel/audio', 'public');
            $data['audio'] = Storage::url($path);
        }

        ContenuCulturel::create($data);

        return redirect()->back()->with('success', 'Contenu culturel ajouté !');
    }

    public function update(Request $request, ContenuCulturel $contenuCulturel)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg|max:10240',
        ]);

        $data = $request->all();

        if ($request->hasFile('audio')) {
            if ($contenuCulturel->audio) {
                $oldPath = str_replace('/storage/', '', $contenuCulturel->audio);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('audio')->store('culturel/audio', 'public');
            $data['audio'] = Storage::url($path);
        }

        $contenuCulturel->update($data);

        return redirect()->back()->with('success', 'Contenu culturel mis à jour !');
    }

    public function destroy(ContenuCulturel $contenuCulturel)
    {
        if ($contenuCulturel->audio) {
            $oldPath = str_replace('/storage/', '', $contenuCulturel->audio);
            Storage::disk('public')->delete($oldPath);
        }
        $contenuCulturel->delete();
        return redirect()->back()->with('success', 'Contenu culturel supprimé !');
    }
}
