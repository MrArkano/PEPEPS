<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Decimal128Cast;
use App\Casts\ObjectIdCast;
use App\Enums\EstadoPago;
use App\Enums\MetodoPago;
use MongoDB\Laravel\Relations\BelongsTo;

/** Colección `pagos`. */
class Pago extends MongoModel
{
    protected $table = 'pagos';

    protected $fillable = [
        'pedido_id', 'metodo_pago', 'valor', 'estado', 'fecha_pago', 'referencia_transaccion',
    ];

    protected function casts(): array
    {
        return [
            'pedido_id' => ObjectIdCast::class,
            'metodo_pago' => MetodoPago::class,
            'estado' => EstadoPago::class,
            'valor' => Decimal128Cast::class,
            'fecha_pago' => 'datetime',
        ];
    }

    /** @return BelongsTo<Pedido, $this> */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id', '_id');
    }
}
