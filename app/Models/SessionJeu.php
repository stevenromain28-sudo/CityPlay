<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionJeu extends Model
{
    protected $table = 'sessions_jeu';

    protected $fillable = [
        'ville_id',
        'proprietaire_id',
        'mode',
        'statut',
        'score',
        'progression',
        'commence_le',
        'termine_le',
    ];

    public function joueurs()
    {
        return $this->hasMany(JoueurSession::class);
    }

    public function ville()
    {
        return $this->belongsTo(Ville::class);
    }
}
