<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Productor;
use Illuminate\Database\Seeder;
use MongoDB\BSON\Decimal128;
use MongoDB\BSON\UTCDateTime;

/**
 * Seeder que incorpora el catálogo campesino oficial de 13 frutas de Santander
 * con sus precios en COP e imágenes locales de alta resolución.
 * Idempotente: actualiza si ya existen.
 */
class CatalogoFrutasSeeder extends Seeder
{
    public function run(): void
    {
        $catFrutas = Categoria::where('nombre', 'Frutas')->first() ?? Categoria::first();
        $catCitricos = Categoria::where('nombre', 'Citricos')->first();
        $catOrganicos = Categoria::where('nombre', 'Productos Organicos')->first();

        $productores = Productor::all();
        if ($productores->isEmpty()) {
            $this->command?->error('No hay productores registrados.');
            return;
        }

        $prodCount = $productores->count();
        $now = new UTCDateTime();

        $frutas = [
            [
                'nombre' => 'Aguacate Criollo de Santander',
                'descripcion' => 'Aguacate criollo de textura cremosa y sabor suave, cosechado tradicionalmente en fincas santandereanas.',
                'precio' => '3500',
                'unidad_medida' => 'kg',
                'imagen' => 'aguacate.png',
                'presentacion' => 'Malla x 1 kg',
                'stock' => 35,
                'categoria_id' => $catFrutas->getKey(),
            ],
            [
                'nombre' => 'Arándanos Silvestres de Páramo',
                'descripcion' => 'Arándanos frescos de alta montaña, ricos en antioxidantes y cultivados de forma sostenible.',
                'precio' => '8500',
                'unidad_medida' => 'bandeja',
                'imagen' => 'arandano.png',
                'presentacion' => 'Bandeja x 250 g',
                'stock' => 18,
                'categoria_id' => $catOrganicos?->getKey() ?? $catFrutas->getKey(),
            ],
            [
                'nombre' => 'Banano Criollo de Lebrija',
                'descripcion' => 'Bananos dulces madurados al sol en las tierras fértiles de Lebrija, ricos en potasio.',
                'precio' => '1200',
                'unidad_medida' => 'kg',
                'imagen' => 'banano.png',
                'presentacion' => 'Gajo x 1 kg',
                'stock' => 50,
                'categoria_id' => $catFrutas->getKey(),
            ],
            [
                'nombre' => 'Cerezas Frescas Seleccionadas',
                'descripcion' => 'Cerezas dulces y jugosas de cosecha selecta, ideales para consumo fresco y postres artesanales.',
                'precio' => '12000',
                'unidad_medida' => 'libra',
                'imagen' => 'cereza.png',
                'presentacion' => 'Bandeja x 500 g',
                'stock' => 8,
                'categoria_id' => $catFrutas->getKey(),
            ],
            [
                'nombre' => 'Coco Campesino',
                'descripcion' => 'Coco fresco con abundante agua dulce y pulpa carnosa, cosechado en valles cálidos de Santander.',
                'precio' => '4500',
                'unidad_medida' => 'unidad',
                'imagen' => 'coco.png',
                'presentacion' => 'Unidad entera',
                'stock' => 22,
                'categoria_id' => $catFrutas->getKey(),
            ],
            [
                'nombre' => 'Durazno Melocotón de Páramo',
                'descripcion' => 'Duraznos de piel aterciopelada y pulpa dulce aromática, cultivados en clima frío santandereano.',
                'precio' => '3000',
                'unidad_medida' => 'kg',
                'imagen' => 'durazno.png',
                'presentacion' => 'Canastilla x 1 kg',
                'stock' => 25,
                'categoria_id' => $catFrutas->getKey(),
            ],
            [
                'nombre' => 'Frambuesas Rojas de Huerta',
                'descripcion' => 'Frambuesas rojas frescas cosechadas en su punto óptimo de maduración, delicadas y aromáticas.',
                'precio' => '9500',
                'unidad_medida' => 'bandeja',
                'imagen' => 'frambuesa.png',
                'presentacion' => 'Bandeja x 200 g',
                'stock' => 12,
                'categoria_id' => $catOrganicos?->getKey() ?? $catFrutas->getKey(),
            ],
            [
                'nombre' => 'Fresas Dulces de Charta',
                'descripcion' => 'Fresas frescas de los campos de Charta, seleccionadas a mano y cultivadas con riego natural.',
                'precio' => '5000',
                'unidad_medida' => 'libra',
                'imagen' => 'fresa.png',
                'presentacion' => 'Cesta x 500 g',
                'stock' => 40,
                'categoria_id' => $catFrutas->getKey(),
            ],
            [
                'nombre' => 'Kiwi Campesino',
                'descripcion' => 'Kiwi fresco de pulpa verde brillante, sabor agridulce equilibrado y alto contenido de vitamina C.',
                'precio' => '3200',
                'unidad_medida' => 'libra',
                'imagen' => 'kiwi.png',
                'presentacion' => 'Bolsa x 500 g',
                'stock' => 15,
                'categoria_id' => $catFrutas->getKey(),
            ],
            [
                'nombre' => 'Mango de Azúcar',
                'descripcion' => 'Mango dulce de tamaño mediano con textura suave sin hilachas, directo de los árboles santandereanos.',
                'precio' => '2800',
                'unidad_medida' => 'kg',
                'imagen' => 'mango.png',
                'presentacion' => 'Bolsa x 1 kg',
                'stock' => 30,
                'categoria_id' => $catFrutas->getKey(),
            ],
            [
                'nombre' => 'Manzana Roja Santandereana',
                'descripcion' => 'Manzanas rojas crujientes y dulces, cosechadas en huertas familiares de Santander.',
                'precio' => '2500',
                'unidad_medida' => 'kg',
                'imagen' => 'manzana.png',
                'presentacion' => 'Malla x 1 kg',
                'stock' => 35,
                'categoria_id' => $catFrutas->getKey(),
            ],
            [
                'nombre' => 'Pera Campesina',
                'descripcion' => 'Peras jugosas y aromáticas de pulpa blanda, cultivadas con abonos orgánicos sin químicos nocivos.',
                'precio' => '2600',
                'unidad_medida' => 'kg',
                'imagen' => 'pera.png',
                'presentacion' => 'Malla x 1 kg',
                'stock' => 28,
                'categoria_id' => $catFrutas->getKey(),
            ],
            [
                'nombre' => 'Piña Gold Miel de Lebrija',
                'descripcion' => 'Famosa piña miel de Lebrija (Santander), reconocida a nivel nacional por su dulzura y aroma intenso.',
                'precio' => '4000',
                'unidad_medida' => 'unidad',
                'imagen' => 'pina.png',
                'presentacion' => 'Unidad seleccionada',
                'stock' => 45,
                'categoria_id' => $catCitricos?->getKey() ?? $catFrutas->getKey(),
            ],
        ];

        foreach ($frutas as $idx => $f) {
            $productor = $productores[$idx % $prodCount];

            Producto::updateOrCreate(
                ['nombre' => $f['nombre']],
                [
                    'productor_id' => $productor->getKey(),
                    'categoria_id' => $f['categoria_id'],
                    'descripcion' => $f['descripcion'],
                    'precio' => new Decimal128($f['precio']),
                    'unidad_medida' => $f['unidad_medida'],
                    'estado' => 'activo',
                    'imagenes' => [$f['imagen']],
                    'inventario' => [
                        'presentacion' => $f['presentacion'],
                        'cantidad_disponible' => $f['stock'],
                        'estado' => $f['stock'] > 0 ? 'disponible' : 'agotado',
                        'fecha_actualizacion' => $now,
                    ],
                    'fecha_creacion' => $now,
                ]
            );
        }

        $this->command?->info('Catálogo campesino de 13 frutas sincronizado correctamente.');
    }
}
