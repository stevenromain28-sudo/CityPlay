<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContenuCulturel extends Model
{
    protected $table = 'contenus_culturels';

    protected $fillable = [
        'lieu_id',
        'titre',
        'description',
        'audio',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function lieu()
    {
        return $this->belongsTo(Lieu::class);
    }
}
