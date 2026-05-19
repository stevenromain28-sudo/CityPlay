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
        'current_enigme_id',
        'commence_le',
        'termine_le',
        'equipe_id',
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

    public function equipe()
    {
        return $this->belongsTo(Equipe::class);
    }
}
