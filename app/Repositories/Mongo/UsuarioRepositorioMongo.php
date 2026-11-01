<?php

declare(strict_types=1);

namespace App\Repositories\Mongo;

use App\Enums\Rol;
use App\Models\Usuario;
use App\Repositories\Contracts\UsuarioRepositorio;
use Illuminate\Support\Collection;

final class UsuarioRepositorioMongo implements UsuarioRepositorio
{
    public function buscarPorCorreo(string $correo): ?Usuario
    {
        return Usuario::query()->where('correo', $correo)->first();
    }

    public function conRol(Rol $rol): Collection
    {
        return Usuario::query()->where('rol', $rol->value)->orderBy('nombre')->get();
    }

    public function crearConsumidor(array $datos, string $hashContrasena): Usuario
    {
        $usuario = new Usuario($datos);
        $usuario->rol = Rol::Consumidor;
        $usuario->estado = 'activo';
        $usuario->direcciones = [];
        // El hash no es asignable en masa: se fija de forma explícita.
        $usuario->contrasena_hash = $hashContrasena;
        $usuario->save();

        return $usuario;
    }
}

