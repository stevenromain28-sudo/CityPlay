<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndiceDebloque extends Model
{
    protected $table = 'indice_debloques';

    protected $fillable = [
        'user_id',
        'session_jeu_id',
        'indice_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sessionJeu()
    {
        return $this->belongsTo(SessionJeu::class);
    }

    public function indice()
    {
        return $this->belongsTo(Indice::class);
    }
}
