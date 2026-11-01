<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\TieneReferenciasObjectId;
use App\Casts\ObjectIdCast;
use MongoDB\Laravel\Relations\BelongsTo;

/** Colección `productores` (fincas o asociaciones). */
class Productor extends MongoModel
{
    use TieneReferenciasObjectId;

    protected $table = 'productores';

    protected $fillable = [
        'usuario_id', 'nombre_finca_asociacion', 'municipio', 'direccion_rural',
        'descripcion', 'estado_verificacion', 'fecha_registro',
    ];

    protected function casts(): array
    {
        return [
            'usuario_id' => ObjectIdCast::class,
            'fecha_registro' => 'datetime',
        ];
    }

    public function estaVerificado(): bool
    {
        return $this->estado_verificacion === 'verificado';
    }

    /** @return BelongsTo<Usuario, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', '_id');
    }

    /** @return Builder<Producto> */
    public function productos(): Builder
    {
        return $this->documentosReferenciados(Producto::class, 'productor_id');
    }
}
