<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Moto extends Model
{
    protected $table = 'motos';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'marca_id',
        'modelo',
        'cilindrada',
        'color',
        'precio',
        'descripcion',
        'anio',
        'imagen',
        'estado',
    ];

    /**
     * Una moto pertenece a una marca.
     */
    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class, 'marca_id', 'id');
    }
}
