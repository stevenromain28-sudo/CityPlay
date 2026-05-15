<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enigme extends Model
{
   public function lieu()
{
    return $this->belongsTo(Lieu::class);
}

public function indices()
{
    return $this->hasMany(Indice::class);
}

public function tentatives()
{
    return $this->hasMany(TentativeEnigme::class);
}
}
