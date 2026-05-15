<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JoueurSession extends Model
{
    protected $table = 'joueur_sessions';

    protected $fillable = [
        'session_jeu_id',
        'user_id',
        'type',
        'score',
        'progression',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    // Une participation appartient à une session
    public function sessionJeu()
    {
        return $this->belongsTo(SessionJeu::class);
    }

    // Une participation appartient à un utilisateur
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
