<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Productor;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use MongoDB\BSON\ObjectId;

/**
 * @extends Factory<Productor>
 */
class ProductorFactory extends Factory
{
    protected $model = Productor::class;

    public function definition(): array
    {
        return [
            'usuario_id' => new ObjectId,
            'nombre_finca_asociacion' => 'Finca ' . fake()->company(),
            'municipio' => fake()->randomElement(['Floridablanca', 'Lebrija', 'Piedecuesta', 'San Gil', 'Socorro']),
            'direccion_rural' => 'Vereda ' . fake()->streetName(),
            'descripcion' => fake()->paragraph(),
            'estado_verificacion' => 'verificado',
            'fecha_registro' => now(),
        ];
    }
}
