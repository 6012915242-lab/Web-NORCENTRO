<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function colores(): HasMany
    {
        return $this->hasMany(ColorMoto::class, 'moto_id')->orderBy('orden')->orderBy('id');
    }

    public function imagenes(): HasMany
    {
        return $this->hasMany(ImagenMoto::class, 'moto_id')->orderBy('orden')->orderBy('id');
    }

    public function unidades(): HasMany
    {
        return $this->hasMany(UnidadMoto::class, 'moto_id');
    }
}
