<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\ObjectIdCast;
use App\Enums\EstadoEntrega;
use App\Enums\TipoResponsable;
use MongoDB\Laravel\Relations\BelongsTo;

/**
 * Colección `entregas`.
 * `responsable_id` apunta a un Productor o a un transportador, según
 * `tipo_responsable`. Los transportadores no tienen colección propia.
 */
class Entrega extends MongoModel
{
    protected $table = 'entregas';

    protected $fillable = [
        'pedido_id', 'responsable_id', 'tipo_responsable', 'estado_entrega',
        'fecha_programada', 'fecha_entrega', 'calificacion_entrega',
        'comentario_entrega', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'pedido_id' => ObjectIdCast::class,
            'responsable_id' => ObjectIdCast::class,
            'tipo_responsable' => TipoResponsable::class,
            'estado_entrega' => EstadoEntrega::class,
            'fecha_programada' => 'datetime',
            'fecha_entrega' => 'datetime',
            'calificacion_entrega' => 'integer',
        ];
    }

    /** @return BelongsTo<Pedido, $this> */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id', '_id');
    }
}
