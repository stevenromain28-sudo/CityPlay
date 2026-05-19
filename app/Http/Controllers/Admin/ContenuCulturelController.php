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
        $isSuperAdmin = auth()->user()->hasRole('super_admin');
        if ($isSuperAdmin) {
            $villes = Ville::all();
            $lieux = Lieu::with('ville')->get();
            $contenus = ContenuCulturel::with('lieu.ville')->get();

            return Inertia::render('Admin/ContenusCulturels/Index', [
                'contenus' => $contenus,
                'lieux' => $lieux,
                'villes' => $villes,
                'isSuperAdmin' => true
            ]);
        }

        $ville = Ville::where('user_id', auth()->id())->first();
        if (!$ville) {
            return Inertia::render('Admin/ContenusCulturels/Index', [
                'contenus' => [],
                'lieux' => [],
                'villes' => [],
                'isSuperAdmin' => false
            ]);
        }

        $lieux = Lieu::where('ville_id', $ville->id)->get();
        $contenus = ContenuCulturel::whereIn('lieu_id', $lieux->pluck('id'))
            ->with('lieu.ville')
            ->get();

        return Inertia::render('Admin/ContenusCulturels/Index', [
            'contenus' => $contenus,
            'lieux' => $lieux,
            'villes' => [],
            'isSuperAdmin' => false
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'lieu_id' => 'required|exists:lieux,id|unique:contenus_culturels,lieu_id',
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg|max:10240',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['lieu_id', 'titre', 'description']);

        if ($request->hasFile('audio')) {
            $path = $request->file('audio')->store('culturel/audio', 'public');
            $data['audio'] = Storage::url($path);
        }

        $imagesPaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('culturel/images', 'public');
                $imagesPaths[] = Storage::url($path);
            }
        }
        $data['images'] = $imagesPaths;

        ContenuCulturel::create($data);

        return redirect()->back()->with('success', 'Contenu culturel ajouté !');
    }

    public function update(Request $request, ContenuCulturel $contenuCulturel)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'audio' => 'nullable|file|mimes:mp3,wav,ogg|max:10240',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'existing_images' => 'nullable|array',
        ]);

        $data = $request->only(['titre', 'description']);

        if ($request->hasFile('audio')) {
            if ($contenuCulturel->audio) {
                $oldPath = str_replace('/storage/', '', $contenuCulturel->audio);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('audio')->store('culturel/audio', 'public');
            $data['audio'] = Storage::url($path);
        }

        // Handle existing images remaining
        $existingImages = $request->input('existing_images', []);
        $currentImages = $contenuCulturel->images ?? [];

        // Delete removed images from storage
        foreach ($currentImages as $imgUrl) {
            if (!in_array($imgUrl, $existingImages)) {
                $oldImgPath = str_replace('/storage/', '', $imgUrl);
                Storage::disk('public')->delete($oldImgPath);
            }
        }

        // Upload new images
        $newImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('culturel/images', 'public');
                $newImages[] = Storage::url($path);
            }
        }

        $data['images'] = array_merge($existingImages, $newImages);

        $contenuCulturel->update($data);

        return redirect()->back()->with('success', 'Contenu culturel mis à jour !');
    }

    public function destroy(ContenuCulturel $contenuCulturel)
    {
        if ($contenuCulturel->audio) {
            $oldPath = str_replace('/storage/', '', $contenuCulturel->audio);
            Storage::disk('public')->delete($oldPath);
        }

        // Delete all images from storage
        $images = $contenuCulturel->images ?? [];
        foreach ($images as $imgUrl) {
            $oldImgPath = str_replace('/storage/', '', $imgUrl);
            Storage::disk('public')->delete($oldImgPath);
        }

        $contenuCulturel->delete();
        return redirect()->back()->with('success', 'Contenu culturel supprimé !');
    }
}
