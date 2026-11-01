<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\Decimal128Cast;
use App\Casts\ObjectIdCast;
use MongoDB\Laravel\Relations\BelongsTo;

/**
 * Colección `productos`.
 * `inventario` es un documento embebido: presentacion, cantidad_disponible,
 * estado (disponible|reservado|agotado) y fecha_actualizacion.
 * Los embebidos se guardan como BSON nativo: NO usar el cast 'array' (serializa a JSON string).
 */
class Producto extends MongoModel
{
    /** Umbral de stock bajo definido en el documento del proyecto. */
    public const STOCK_BAJO_MAXIMO = 10;

    protected $table = 'productos';

    protected $fillable = [
        'productor_id', 'categoria_id', 'nombre', 'descripcion', 'precio',
        'unidad_medida', 'estado', 'imagenes', 'inventario', 'fecha_creacion',
    ];

    protected function casts(): array
    {
        return [
            'productor_id' => ObjectIdCast::class,
            'categoria_id' => ObjectIdCast::class,
            'precio' => Decimal128Cast::class,
            'fecha_creacion' => 'datetime',
        ];
    }

    public function cantidadDisponible(): int
    {
        return (int) ($this->inventario['cantidad_disponible'] ?? 0);
    }

    public function estaAgotado(): bool
    {
        return ($this->inventario['estado'] ?? null) === 'agotado';
    }

    public function esCritico(): bool
    {
        return $this->estaAgotado() || $this->cantidadDisponible() <= self::STOCK_BAJO_MAXIMO;
    }

    /** @return BelongsTo<Productor, $this> */
    public function productor(): BelongsTo
    {
        return $this->belongsTo(Productor::class, 'productor_id', '_id');
    }

    /** @return BelongsTo<Categoria, $this> */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id', '_id');
    }
}
