<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lieu extends Model
{
    protected $table = 'lieux';

    public function ville()
{
    return $this->belongsTo(Ville::class);
}

public function enigmes()
{
    return $this->hasMany(Enigme::class);
}

public function contenuCulturel()
{
    return $this->hasOne(ContenuCulturel::class);
}
}
