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
        'duree_initiale',
        'temps_restant',
        'dernier_calcul_at',
        'score',
        'progression',
        'current_enigme_id',
        'commence_le',
        'termine_le',
    ];

    public function currentEnigme()
    {
        return $this->belongsTo(Enigme::class, 'current_enigme_id');
    }

    public function joueurs()
    {
        return $this->hasMany(JoueurSession::class);
    }

    public function ville()
    {
        return $this->belongsTo(Ville::class);
    }
}
