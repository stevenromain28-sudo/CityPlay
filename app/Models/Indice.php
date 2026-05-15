<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Indice extends Model
{
    protected $fillable = [
        'enigme_id',
        'contenu',
        'penalite'
    ];

    public function enigme()
    {
        return $this->belongsTo(Enigme::class);
    }
}
