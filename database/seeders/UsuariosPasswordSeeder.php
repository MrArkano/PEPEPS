<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Reemplaza ÚNICAMENTE `contrasena_hash` de los usuarios existentes (los datos
 * de prueba traen marcadores que no sirven para iniciar sesión).
 * No crea, borra ni modifica ningún otro campo. Es idempotente.
 *
 * Uso: php artisan db:seed --class=UsuariosPasswordSeeder
 * Solo para entornos de desarrollo: la clave es pública.
 */
class UsuariosPasswordSeeder extends Seeder
{
    private const CLAVE_PRUEBA = 'password123';

    public function run(): void
    {
        $actualizados = 0;

        // Hash individual por usuario (salt distinto); 10 documentos, una sola pasada.
        foreach (Usuario::query()->get(['_id']) as $usuario) {
            $usuario->contrasena_hash = Hash::make(self::CLAVE_PRUEBA);
            $usuario->save(); // Solo el atributo modificado viaja en el $set.
            $actualizados++;
        }

        $this->command?->info("contrasena_hash actualizado en {$actualizados} usuarios.");
    }
}

