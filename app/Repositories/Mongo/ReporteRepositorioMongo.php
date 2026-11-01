<?php

declare(strict_types=1);

namespace App\Repositories\Mongo;

use App\Repositories\Contracts\ReporteRepositorio;
use App\Repositories\Mongo\Concerns\UsaMongo;

/**
 * Ejecuta los 5 pipelines de agregación definidos en CONSULTAS_PEPEPS.js
 * de forma nativa en el servidor MongoDB sin operaciones intermedias en memoria PHP.
 */
final class ReporteRepositorioMongo implements ReporteRepositorio
{
    use UsaMongo;

    public function productosMasVendidosPorMunicipio(): array
    {
        $pipeline = [
            // Regla requerida: En la consulta 1 excluye pedidos cancelados
            ['$match' => ['estado' => ['$ne' => 'cancelado']]],
            ['$unwind' => '$items'],
            ['$lookup' => [
                'from' => 'productos',
                'localField' => 'items.producto_id',
                'foreignField' => '_id',
                'as' => 'producto_info',
            ]],
            ['$unwind' => '$producto_info'],
            ['$lookup' => [
                'from' => 'productores',
                'localField' => 'producto_info.productor_id',
                'foreignField' => '_id',
                'as' => 'productor_info',
            ]],
            ['$unwind' => '$productor_info'],
            ['$group' => [
                '_id' => [
                    'municipio' => '$productor_info.municipio',
                    'producto' => '$items.nombre_producto',
                ],
                'cantidad_total_vendida' => ['$sum' => '$items.cantidad'],
            ]],
            ['$project' => [
                '_id' => 0,
                'municipio' => '$_id.municipio',
                'producto' => '$_id.producto',
                'cantidad_total_vendida' => 1,
            ]],
            ['$sort' => ['municipio' => 1, 'cantidad_total_vendida' => -1]],
        ];

        return iterator_to_array($this->coleccion('pedidos')->aggregate($pipeline));
    }

    public function productoresConPedidosPendientes(): array
    {
        $pipeline = [
            ['$match' => ['estado' => 'pendiente']],
            ['$unwind' => '$items'],
            ['$lookup' => [
                'from' => 'productos',
                'localField' => 'items.producto_id',
                'foreignField' => '_id',
                'as' => 'producto_info',
            ]],
            ['$unwind' => '$producto_info'],
            ['$lookup' => [
                'from' => 'productores',
                'localField' => 'producto_info.productor_id',
                'foreignField' => '_id',
                'as' => 'productor_info',
            ]],
            ['$unwind' => '$productor_info'],
            ['$group' => [
                '_id' => [
                    'productor' => '$productor_info.nombre_finca_asociacion',
                    'municipio' => '$productor_info.municipio',
                    'pedido' => '$_id',
                ],
            ]],
            ['$group' => [
                '_id' => [
                    'productor' => '$_id.productor',
                    'municipio' => '$_id.municipio',
                ],
                'cantidad_pedidos_pendientes' => ['$sum' => 1],
            ]],
            ['$project' => [
                '_id' => 0,
                'finca_asociacion' => '$_id.productor',
                'municipio' => '$_id.municipio',
                'cantidad_pedidos_pendientes' => 1,
            ]],
            ['$sort' => ['cantidad_pedidos_pendientes' => -1]],
        ];

        return iterator_to_array($this->coleccion('pedidos')->aggregate($pipeline));
    }

    public function historialPedidosPorConsumidor(): array
    {
        $pipeline = [
            ['$lookup' => [
                'from' => 'usuarios',
                'localField' => 'consumidor_id',
                'foreignField' => '_id',
                'as' => 'consumidor_info',
            ]],
            ['$unwind' => '$consumidor_info'],
            ['$project' => [
                '_id' => 0,
                'consumidor' => [
                    '$concat' => ['$consumidor_info.nombre', ' ', '$consumidor_info.apellido'],
                ],
                'fecha' => 1,
                'estado' => 1,
                'tipo_entrega' => 1,
                'total' => 1,
            ]],
            ['$sort' => ['consumidor' => 1, 'fecha' => 1]],
        ];

        return iterator_to_array($this->coleccion('pedidos')->aggregate($pipeline));
    }

    public function pagosPorMedioYEstado(): array
    {
        $pipeline = [
            ['$lookup' => [
                'from' => 'pedidos',
                'localField' => 'pedido_id',
                'foreignField' => '_id',
                'as' => 'pedido_info',
            ]],
            ['$unwind' => '$pedido_info'],
            ['$group' => [
                '_id' => [
                    'metodo_pago' => '$metodo_pago',
                    'estado_pago' => '$estado',
                ],
                'cantidad_pagos' => ['$sum' => 1],
                'valor_total' => ['$sum' => ['$toDecimal' => '$valor']],
            ]],
            ['$project' => [
                '_id' => 0,
                'metodo_pago' => '$_id.metodo_pago',
                'estado_pago' => '$_id.estado_pago',
                'cantidad_pagos' => 1,
                'valor_total' => 1,
            ]],
            ['$sort' => ['metodo_pago' => 1, 'estado_pago' => 1]],
        ];

        return iterator_to_array($this->coleccion('pagos')->aggregate($pipeline));
    }

    public function productosAgotadosOStockBajo(): array
    {
        $pipeline = [
            ['$match' => [
                '$or' => [
                    ['inventario.estado' => 'agotado'],
                    ['inventario.cantidad_disponible' => ['$lte' => 10]],
                ],
            ]],
            ['$lookup' => [
                'from' => 'productores',
                'localField' => 'productor_id',
                'foreignField' => '_id',
                'as' => 'productor_info',
            ]],
            ['$unwind' => '$productor_info'],
            ['$project' => [
                '_id' => 0,
                'productor' => '$productor_info.nombre_finca_asociacion',
                'municipio' => '$productor_info.municipio',
                'producto' => '$nombre',
                'cantidad_disponible' => '$inventario.cantidad_disponible',
                'estado_inventario' => '$inventario.estado',
            ]],
            ['$sort' => ['cantidad_disponible' => 1]],
        ];

        return iterator_to_array($this->coleccion('productos')->aggregate($pipeline));
    }
}

