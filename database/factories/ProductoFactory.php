<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;
use MongoDB\BSON\Decimal128;
use MongoDB\BSON\ObjectId;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition(): array
    {
        return [
            'productor_id' => new ObjectId,
            'categoria_id' => new ObjectId,
            'nombre' => fake()->unique()->word() . ' Campesino',
            'descripcion' => fake()->sentence(),
            'precio' => new Decimal128((string) fake()->numberBetween(2000, 15000)),
            'unidad_medida' => fake()->randomElement(['kg', 'libra', 'bandeja', 'unidad']),
            'estado' => 'activo',
            'imagenes' => ['aguacate.png'],
            'inventario' => [
                'presentacion' => 'Empaque x 1 kg',
                'cantidad_disponible' => fake()->numberBetween(10, 100),
                'estado' => 'disponible',
                'fecha_actualizacion' => now(),
            ],
            'fecha_creacion' => now(),
        ];
    }
}
