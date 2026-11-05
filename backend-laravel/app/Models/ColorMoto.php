<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ColorMoto extends Model
{
    protected $table = 'moto_colores';

    public $timestamps = false;

    public function imagenes(): HasMany
    {
        return $this->hasMany(ImagenColorMoto::class, 'color_id')->orderBy('orden')->orderBy('id');
    }
}
