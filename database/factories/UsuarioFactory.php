<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Rol;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'correo' => fake()->unique()->safeEmail(),
            'telefono' => '3' . fake()->numerify('#########'),
            'rol' => Rol::Consumidor,
            'estado' => 'activo',
            'contrasena_hash' => Hash::make('password123'),
            'direcciones' => [
                [
                    'direccion' => fake()->streetAddress(),
                    'municipio' => 'Bucaramanga',
                    'barrio' => 'Cabecera',
                    'referencia' => 'Frente al parque',
                ],
            ],
        ];
    }

    public function administrador(): static
    {
        return $this->state(fn () => ['rol' => Rol::Administrador]);
    }

    public function productor(): static
    {
        return $this->state(fn () => ['rol' => Rol::Productor]);
    }

    public function consumidor(): static
    {
        return $this->state(fn () => ['rol' => Rol::Consumidor]);
    }
}
