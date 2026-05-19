<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipe extends Model
{
    protected $fillable = [
        'chef_id',
        'nom',
        'score_total',
    ];

    public function chef()
    {
        return $this->belongsTo(User::class, 'chef_id');
    }

    public function membres()
    {
        return $this->hasMany(User::class);
    }

    public function joueurSessions()
    {
        return $this->hasMany(JoueurSession::class);
    }

    public function progressionEnigmes()
    {
        return $this->hasMany(ProgressionEnigme::class);
    }
}
