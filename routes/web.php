<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\VilleController;
use App\Http\Controllers\Admin\LieuController;
use App\Http\Controllers\Admin\EnigmeController;
use App\Http\Controllers\Admin\ContenuCulturelController;
use App\Http\Controllers\Player\GameController;
use App\Models\Ville;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // CRUD Villes
    Route::get('/villes', [VilleController::class, 'index'])->name('villes.index');
    Route::post('/villes', [VilleController::class, 'store'])->name('villes.store');
    Route::post('/villes/{ville}', [VilleController::class, 'update'])->name('villes.update');

    // CRUD Lieux
    Route::get('/lieux', [LieuController::class, 'index'])->name('lieux.index');
    Route::post('/lieux', [LieuController::class, 'store'])->name('lieux.store');
    Route::post('/lieux/{lieu}', [LieuController::class, 'update'])->name('lieux.update');
    Route::delete('/lieux/{lieu}', [LieuController::class, 'destroy'])->name('lieux.destroy');

    // CRUD Énigmes
    Route::get('/enigmes', [EnigmeController::class, 'index'])->name('enigmes.index');
    Route::post('/enigmes', [EnigmeController::class, 'store'])->name('enigmes.store');
    Route::post('/enigmes/{enigme}', [EnigmeController::class, 'update'])->name('enigmes.update');
    Route::delete('/enigmes/{enigme}', [EnigmeController::class, 'destroy'])->name('enigmes.destroy');

    // CRUD Contenus Culturels
    Route::get('/contenus-culturels', [ContenuCulturelController::class, 'index'])->name('contenus-culturels.index');
    Route::post('/contenus-culturels', [ContenuCulturelController::class, 'store'])->name('contenus-culturels.store');
    Route::post('/contenus-culturels/{contenuCulturel}', [ContenuCulturelController::class, 'update'])->name('contenus-culturels.update');
    Route::delete('/contenus-culturels/{contenuCulturel}', [ContenuCulturelController::class, 'destroy'])->name('contenus-culturels.destroy');
});

// Fallback debug
Route::get('/debug-routes', function() {
    return Route::getRoutes()->getRoutesByName();
});

// Player Routes
Route::middleware(['auth', 'role:player'])->name('player.')->group(function () {
    Route::get('/play', function () {
        return Inertia::render('Player/Dashboard', [
            'villes' => Ville::where('actif', true)->get()
        ]);
    })->name('dashboard');

    Route::post('/enigmes/{enigme}/validate-location', [GameController::class, 'validateLocation'])->name('enigmes.validate-location');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
