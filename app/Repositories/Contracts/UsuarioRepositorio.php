<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Enums\Rol;
use App\Models\Usuario;
use Illuminate\Support\Collection;

/**
 * Contrato de persistencia de usuarios, agnóstico del motor (MongoDB, SQL...).
 * Los casos de uso dependen de esta interfaz, nunca de Eloquent/Mongo.
 */
interface UsuarioRepositorio
{
    public function buscarPorCorreo(string $correo): ?Usuario;

    /** @return Collection<int, Usuario> */
    public function conRol(Rol $rol): Collection;

    /**
     * @param  array{nombre: string, apellido: string, correo: string, telefono: string}  $datos
     */
    public function crearConsumidor(array $datos, string $hashContrasena): Usuario;
}
