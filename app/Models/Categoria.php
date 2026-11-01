<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\TieneReferenciasObjectId;

/** Colección `categorias`. */
class Categoria extends MongoModel
{
    use TieneReferenciasObjectId;

    protected $table = 'categorias';

    protected $fillable = ['nombre', 'descripcion', 'estado'];

    /** @return Builder<Producto> */
    public function productos(): Builder
    {
        return $this->documentosReferenciados(Producto::class, 'categoria_id');
    }
}
