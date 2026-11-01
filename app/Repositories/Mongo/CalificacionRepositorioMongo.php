<?php

declare(strict_types=1);

namespace App\Repositories\Mongo;

use App\Repositories\Contracts\CalificacionRepositorio;
use App\Repositories\Mongo\Concerns\UsaMongo;

/**
 * Las calificaciones viven embebidas en `pedidos.items.calificacion_producto`.
 * Se agregan en el servidor con un único pipeline (sin traer documentos a PHP).
 */
final class CalificacionRepositorioMongo implements CalificacionRepositorio
{
    use UsaMongo;

    public function promedioPorProductor(array $productorIds): array
    {
        if ($productorIds === []) {
            return [];
        }

        $pipeline = [
            ['$unwind' => '$items'],
            ['$match' => ['items.calificacion_producto' => ['$exists' => true]]],
            ['$lookup' => [
                'from' => 'productos',
                'localField' => 'items.producto_id',
                'foreignField' => '_id',
                'as' => 'producto',
            ]],
            ['$unwind' => '$producto'],
            ['$match' => ['producto.productor_id' => ['$in' => $this->oids($productorIds)]]],
            ['$group' => [
                '_id' => '$producto.productor_id',
                'promedio' => ['$avg' => '$items.calificacion_producto'],
                'total' => ['$sum' => 1],
            ]],
        ];

        $resultado = [];
        foreach ($this->coleccion('pedidos')->aggregate($pipeline) as $fila) {
            $resultado[(string) $fila['_id']] = [
                'promedio' => round((float) $fila['promedio'], 1),
                'total' => (int) $fila['total'],
            ];
        }

        return $resultado;
    }

    public function promedioDeProducto(string $productoId): ?array
    {
        $pipeline = [
            ['$match' => ['items.producto_id' => $this->oid($productoId)]],
            ['$unwind' => '$items'],
            ['$match' => [
                'items.producto_id' => $this->oid($productoId),
                'items.calificacion_producto' => ['$exists' => true],
            ]],
            ['$group' => [
                '_id' => null,
                'promedio' => ['$avg' => '$items.calificacion_producto'],
                'total' => ['$sum' => 1],
            ]],
        ];

        foreach ($this->coleccion('pedidos')->aggregate($pipeline) as $fila) {
            return ['promedio' => round((float) $fila['promedio'], 1), 'total' => (int) $fila['total']];
        }

        return null;
    }
}

