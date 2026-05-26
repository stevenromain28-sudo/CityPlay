<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enigme extends Model
{
    protected $fillable = [
        'lieu_id',
        'titre',
        'contenu',
        'image',
        'audio',
        'niveau',
        'ordre',
        'reponse',
        'options',
        'latitude',
        'longitude',
        'rayon',
        'verification_gps',
        'is_bonus',
    ];

    protected $casts = [
        'options' => 'array',
        'verification_gps' => 'boolean',
        'is_bonus' => 'boolean',
    ];

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
