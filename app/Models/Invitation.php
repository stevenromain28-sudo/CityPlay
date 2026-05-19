<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invitation extends Model
{
    protected $fillable = [
        'inviteur_id',
        'session_jeu_id',
        'equipe_id',
        'token',
        'expire_le',
        'max_utilisations',
        'utilisations',
        'type',
        'accepte',
    ];

    protected $casts = [
        'expire_le' => 'datetime',
    ];

    public static function generateToken(): string
    {
        return Str::random(32);
    }

    public function estValide(): bool
    {
        if ($this->expire_le && now()->gt($this->expire_le)) {
            return false;
        }
        if ($this->utilisations >= $this->max_utilisations) {
            return false;
        }
        return true;
    }

    public function inviteur()
    {
        return $this->belongsTo(User::class, 'inviteur_id');
    }

    public function sessionJeu()
    {
        return $this->belongsTo(SessionJeu::class);
    }

    public function equipe()
    {
        return $this->belongsTo(Equipe::class);
    }
}
