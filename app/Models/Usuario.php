<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\TieneReferenciasObjectId;
use App\Enums\Rol;
use MongoDB\Laravel\Auth\User as Authenticatable;

/**
 * Colección `usuarios`. `direcciones` es un arreglo de documentos embebidos.
 * Nota: un usuario Productor puede tener varias fincas (relación 1:N).
 */
class Usuario extends Authenticatable
{
    use TieneReferenciasObjectId;

    protected $connection = 'mongodb';

    protected $table = 'usuarios';

    public $timestamps = false;

    protected $fillable = ['nombre', 'apellido', 'correo', 'telefono', 'rol', 'estado', 'direcciones'];

    /** El hash nunca debe serializarse (vistas, JSON, logs). */
    protected $hidden = ['contrasena_hash', 'remember_token'];

    protected function casts(): array
    {
        return [
            'rol' => Rol::class,
        ];
    }

    public function getAuthPasswordName(): string
    {
        return 'contrasena_hash';
    }

    public function getAuthIdentifierName(): string
    {
        return '_id';
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellido}");
    }

    public function tieneRol(Rol ...$roles): bool
    {
        return in_array($this->rol, $roles, true);
    }

    /** @return Builder<Productor> */
    public function productores(): Builder
    {
        return $this->documentosReferenciados(Productor::class, 'usuario_id');
    }
}
