<?php

declare(strict_types=1);

namespace App\Exceptions\Pedidos;

use App\Exceptions\ErrorDeNegocio;

final class StockInsuficiente extends ErrorDeNegocio
{
    public function __construct(string $nombreProducto, int $disponible)
    {
        parent::__construct("No hay suficiente stock para «{$nombreProducto}». Disponibles actualmente: {$disponible} unidades.");
    }
}

