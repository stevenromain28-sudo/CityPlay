<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TentativeEnigme extends Model
{
    protected $table = 'tentatives_enigmes';

    protected $fillable = [
        'enigme_id',
        'user_id',
        'latitude',
        'longitude',
        'succes',
        'tente_le',
    ];
}
