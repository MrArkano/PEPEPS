<?php

declare(strict_types=1);

namespace App\Exceptions\Catalogo;

use App\Exceptions\ErrorDeNegocio;

final class ProductoConPedidos extends ErrorDeNegocio
{
    public function __construct()
    {
        parent::__construct('No se puede eliminar: el producto ya tiene pedidos. Márcalo como inactivo para retirarlo del catálogo.');
    }
}

