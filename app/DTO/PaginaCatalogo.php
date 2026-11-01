<?php

declare(strict_types=1);

namespace App\DTO;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/** Datos necesarios para pintar una página del catálogo. */
final readonly class PaginaCatalogo
{
    /**
     * @param  array<string, array{promedio: float, total: int}>  $calificacionesProductor  Indexado por id de productor.
     * @param  list<string>  $municipios
     * @param  Collection<int, \App\Models\Categoria>  $categorias
     * @param  Collection<int, \App\Models\Productor>  $productores
     */
    public function __construct(
        public LengthAwarePaginator $productos,
        public array $calificacionesProductor,
        public array $municipios,
        public Collection $categorias,
        public Collection $productores,
    ) {}
}

