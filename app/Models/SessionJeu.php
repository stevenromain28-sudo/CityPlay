<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionJeu extends Model
{
    protected $table = 'sessions_jeu';
    public function joueurs()
    {
        return $this->hasMany(JoueurSession::class);
    }

    public function ville()
    {
        return $this->belongsTo(Ville::class);
    }
}
