<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressionEnigme extends Model
{
    protected $table = 'progression_enigmes';

    protected $fillable = [
        'session_jeu_id',
        'user_id',
        'enigme_id',
        'text_validated_at',
        'gps_validated_at',
        'bonus_choice_made',
        'wants_bonus',
    ];

    public function sessionJeu()
    {
        return $this->belongsTo(SessionJeu::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enigme()
    {
        return $this->belongsTo(Enigme::class);
    }
}
