<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';

    public $timestamps = false;

    protected $hidden = ['password_hash'];

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function tienePermiso(string $permiso): bool
    {
        if ($this->estado !== 'Activo') {
            return false;
        }

        return DB::table('vista_usuarios_permisos')
            ->where('usuario_id', $this->getKey())
            ->where('permiso', $permiso)
            ->exists();
    }
}
