<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\ObjectIdCast;

/**
 * Colección `auditoria`.
 * Registra trazabilidad de cambios críticos: estados de pedidos, modificaciones de precios
 * o variaciones de inventario de productos.
 */
class Auditoria extends MongoModel
{
    protected $table = 'auditoria';

    protected $fillable = [
        'usuario_id',
        'nombre_usuario',
        'entidad',
        'entidad_id',
        'accion',
        'campo',
        'valor_anterior',
        'valor_nuevo',
        'fecha',
    ];

    protected function casts(): array
    {
        return [
            'usuario_id' => ObjectIdCast::class,
            'entidad_id' => ObjectIdCast::class,
            'fecha' => 'datetime',
        ];
    }
}

