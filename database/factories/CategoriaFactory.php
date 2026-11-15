<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Categoria>
 */
class CategoriaFactory extends Factory
{
    protected $model = Categoria::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->randomElement(['Frutas Frescas', 'Hortalizas', 'Tubérculos Andinos', 'Hierbas Aromáticas', 'Cítricos']),
            'descripcion' => fake()->sentence(),
            'estado' => 'activo',
        ];
    }
}
