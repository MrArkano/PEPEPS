<?php

declare(strict_types=1);

namespace App\Repositories\Mongo\Concerns;

use Illuminate\Support\Facades\DB;
use MongoDB\BSON\ObjectId;
use MongoDB\Collection;

/** Utilidades compartidas por los repositorios MongoDB. */
trait UsaMongo
{
    protected function coleccion(string $nombre): Collection
    {
        return DB::connection('mongodb')->getCollection($nombre);
    }

    protected function oid(string $id): ObjectId
    {
        return new ObjectId($id);
    }

    /**
     * @param  list<string>  $ids
     * @return list<ObjectId>
     */
    protected function oids(array $ids): array
    {
        return array_map($this->oid(...), $ids);
    }
}

