<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use MongoDB\BSON\ObjectId;

/**
 * Las referencias entre colecciones están guardadas como ObjectId, pero el
 * paquete expone la clave primaria como string, por lo que `hasMany` no
 * encuentra coincidencias. Este trait consulta explícitamente con ObjectId.
 */
trait TieneReferenciasObjectId
{
    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelo
     */
    protected function documentosReferenciados(string $modelo, string $campoReferencia): Builder
    {
        return $modelo::query()->where($campoReferencia, new ObjectId((string) $this->getKey()));
    }
}

