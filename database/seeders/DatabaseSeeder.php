<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder Principal del Sistema PEPEPS.
 * Garantiza la inicialización limpia del entorno de desarrollo y pruebas.
 * Ejecutable vía: php artisan db:seed
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Asegurar usuarios base de prueba para cada uno de los 3 roles (si no existen)
        $usuariosBase = [
            [
                'nombre' => 'Carlos',
                'apellido' => 'Ramírez',
                'correo' => 'carlos.ramirez@pepeps.co',
                'telefono' => '3101234567',
                'rol' => Rol::Administrador,
                'estado' => 'activo',
                'contrasena_hash' => Hash::make('password123'),
                'direcciones' => [[
                    'direccion' => 'Calle 36 #14-25',
                    'municipio' => 'Bucaramanga',
                    'barrio' => 'Centro',
                    'referencia' => 'Edificio Cámara de Comercio',
                ]],
            ],
            [
                'nombre' => 'María',
                'apellido' => 'Suárez',
                'correo' => 'maria.suarez@finca.co',
                'telefono' => '3123334455',
                'rol' => Rol::Productor,
                'estado' => 'activo',
                'contrasena_hash' => Hash::make('password123'),
                'direcciones' => [[
                    'direccion' => 'Vereda La Esperanza',
                    'municipio' => 'Floridablanca',
                    'barrio' => 'Zona Rural',
                    'referencia' => 'Finca La Esperanza',
                ]],
            ],
            [
                'nombre' => 'Andrés',
                'apellido' => 'Mantilla',
                'correo' => 'andres.mantilla@gmail.com',
                'telefono' => '3156667788',
                'rol' => Rol::Consumidor,
                'estado' => 'activo',
                'contrasena_hash' => Hash::make('password123'),
                'direcciones' => [[
                    'direccion' => 'Calle 45 #20-30',
                    'municipio' => 'Bucaramanga',
                    'barrio' => 'Álvarez',
                    'referencia' => 'Casa esquinera',
                ]],
            ],
        ];

        foreach ($usuariosBase as $u) {
            Usuario::firstOrCreate(
                ['correo' => $u['correo']],
                $u
            );
        }

        // 2. Ejecutar actualizador de contraseñas para los usuarios demo existentes
        $this->call(UsuariosPasswordSeeder::class);

        // 3. Sincronizar catálogo oficial de 13 frutas de Santander con imágenes y precios COP
        $this->call(CatalogoFrutasSeeder::class);
    }
}
