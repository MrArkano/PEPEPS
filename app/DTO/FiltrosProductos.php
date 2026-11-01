<?php

declare(strict_types=1);

namespace App\DTO;

/** Criterios de búsqueda de productos (catálogo público y gestión). */
final readonly class FiltrosProductos
{
    /**
     * @param  list<string>|null  $productorIdsPermitidos  Alcance del actor: null = sin restricción (Administrador).
     */
    public function __construct(
        public ?string $busqueda = null,
        public ?string $municipio = null,
        public ?string $categoriaId = null,
        public ?string $productorId = null,
        public ?array $productorIdsPermitidos = null,
        public bool $soloActivos = false,
        public bool $soloCriticos = false,
    ) {}
}

