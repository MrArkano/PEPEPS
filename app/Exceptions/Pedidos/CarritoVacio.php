<?php

declare(strict_types=1);

namespace App\Exceptions\Pedidos;

use App\Exceptions\ErrorDeNegocio;

final class CarritoVacio extends ErrorDeNegocio
{
    public function __construct()
    {
        parent::__construct('Tu carrito de compras está vacío.');
    }
}

