<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\SessionJeuController;
use App\Http\Controllers\GameplayController;
use App\Http\Controllers\InvitationController;
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
    $user = auth()->user();
    if ($user->hasRole('admin') || $user->hasRole('super_admin')) {
        return redirect()->route('admin.dashboard');
    }
    if ($user->hasRole('player')) {
        return redirect()->route('player.dashboard');
    }
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Invitation Routes (accessible sans auth pour l'affichage, mais acceptation nécessite auth)
Route::get('/invitation/{token}', [InvitationController::class, 'show'])->name('invitation.show');
Route::post('/invitation/{token}/accept', [InvitationController::class, 'accept'])->middleware('auth')->name('invitation.accept');

// Admin Routes
Route::middleware(['auth', 'role:admin|super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // CRUD Villes
    Route::get('/villes', [VilleController::class, 'index'])->name('villes.index');
    Route::post('/villes', [VilleController::class, 'store'])->name('villes.store');
    Route::post('/villes/{ville}', [VilleController::class, 'update'])->name('villes.update');
    Route::delete('/villes/{ville}', [VilleController::class, 'destroy'])->name('villes.destroy');

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

    // CRUD Utilisateurs Admins (Seulement pour le SuperAdmin)
    Route::middleware(['role:super_admin'])->group(function () {
        Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::post('/users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
        Route::post('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
    });
});

// Fallback debug
Route::get('/debug-routes', function() {
    return Route::getRoutes()->getRoutesByName();
});

// Player Routes
Route::middleware(['auth', 'role:player'])->prefix('play')->name('player.')->group(function () {
    Route::get('/dashboard', [PlayerController::class, 'dashboard'])->name('dashboard');
    Route::get('/ville/{ville}/lieu/{lieu}', [PlayerController::class, 'lieuDashboard'])->name('lieu.dashboard');
    Route::post('/detect-city', [PlayerController::class, 'detecterVille'])->name('detect-city');
    
    Route::post('/enigmes/{enigme}/validate-location', [GameController::class, 'validateLocation'])->name('enigmes.validate-location');
    
    // Auto-start game from dashboard
    Route::post('/game/auto-start', [PlayerController::class, 'autoStart'])->name('game.auto-start');

    // Invitations
    Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');

    // Sessions de jeu
    Route::resource('sessions', SessionJeuController::class);
    Route::post('/sessions/{session}/start', [SessionJeuController::class, 'start'])->name('sessions.start');
    Route::post('/sessions/{session}/status', [SessionJeuController::class, 'updateStatus'])->name('sessions.status');
    Route::post('/sessions/{session}/heartbeat', [SessionJeuController::class, 'heartbeat'])->name('sessions.heartbeat');
    Route::post('/sessions/{session}/add-time', [SessionJeuController::class, 'addTime'])->name('sessions.add-time');

    // Gameplay
    Route::prefix('game/{session}')->name('game.')->group(function () {
        Route::get('/', [PlayerController::class, 'jeu'])->name('jeu');
        Route::post('/start', [GameplayController::class, 'commencerSession'])->name('start');
        Route::get('/lieu/{lieu}', [PlayerController::class, 'lieuDashboard'])->name('game.lieu.dashboard');
        Route::post('/enigme/{enigme}/gps', [GameplayController::class, 'validerGPS'])->name('validate.gps');
        Route::post('/enigme/{enigme}/reponse', [GameplayController::class, 'soumettreReponse'])->name('submit.answer');
        Route::post('/enigme/{enigme}/bonus-choice', [GameplayController::class, 'faireChoixBonus'])->name('bonus.choice');
        Route::post('/enigme/{enigme}/indice/{indice}/unlock', [GameplayController::class, 'debloquerIndice'])->name('unlock.indice');
    });

    // Placeholder routes for other pages
    Route::get('/map', [PlayerController::class, 'map'])->name('map');
    Route::get('/enigme', [PlayerController::class, 'enigmes'])->name('enigme');
    Route::get('/leaderboard', [PlayerController::class, 'leaderboard'])->name('leaderboard');
    Route::get('/invitation', function () { return Inertia::render('Player/Invitation'); })->name('invitation');
    Route::get('/websocket', [PlayerController::class, 'websocket'])->name('websocket');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
