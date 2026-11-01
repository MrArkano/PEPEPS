<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\TieneReferenciasObjectId;
use App\Casts\Decimal128Cast;
use App\Casts\ObjectIdCast;
use App\Enums\EstadoPedido;
use App\Enums\TipoEntrega;
use MongoDB\Laravel\Relations\BelongsTo;

/**
 * Colección `pedidos`.
 * `items` y `direccion_entrega` son embebidos. Cada ítem conserva una "foto"
 * del nombre y precio del producto al momento de la compra, además de
 * calificacion_producto / comentario_producto opcionales.
 */
class Pedido extends MongoModel
{
    use TieneReferenciasObjectId;

    protected $table = 'pedidos';

    protected $fillable = [
        'consumidor_id', 'fecha', 'estado', 'tipo_entrega', 'items',
        'direccion_entrega', 'subtotal', 'costo_envio', 'total',
    ];

    protected function casts(): array
    {
        return [
            'consumidor_id' => ObjectIdCast::class,
            'fecha' => 'datetime',
            'estado' => EstadoPedido::class,
            'tipo_entrega' => TipoEntrega::class,
            'subtotal' => Decimal128Cast::class,
            'costo_envio' => Decimal128Cast::class,
            'total' => Decimal128Cast::class,
        ];
    }

    /** @return BelongsTo<Usuario, $this> */
    public function consumidor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'consumidor_id', '_id');
    }

    /** @return Builder<Pago> */
    public function pagos(): Builder
    {
        return $this->documentosReferenciados(Pago::class, 'pedido_id');
    }

    /** @return Builder<Entrega> */
    public function entregas(): Builder
    {
        return $this->documentosReferenciados(Entrega::class, 'pedido_id');
    }
}
