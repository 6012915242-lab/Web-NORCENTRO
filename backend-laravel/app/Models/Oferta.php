<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Oferta extends Model
{
    protected $table = 'ofertas';

    public $timestamps = false;

    protected $fillable = [
        'moto_id', 'titulo', 'descripcion', 'precio_normal', 'precio_oferta',
        'fecha_inicio', 'fecha_fin',
    ];

    public function moto(): BelongsTo
    {
        return $this->belongsTo(Moto::class, 'moto_id');
    }
}
