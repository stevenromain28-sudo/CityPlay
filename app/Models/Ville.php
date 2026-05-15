<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ville extends Model
{
    protected $fillable = [
        'user_id', 
        'nom', 
        'slug', 
        'description', 
        'history', 
        'pays', 
        'population', 
        'latitude', 
        'longitude', 
        'banniere', 
        'actif'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lieux()
    {
        return $this->hasMany(Lieu::class);
    }
}
