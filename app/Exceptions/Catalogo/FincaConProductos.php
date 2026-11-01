<?php

declare(strict_types=1);

namespace App\Exceptions\Catalogo;

use App\Exceptions\ErrorDeNegocio;

final class FincaConProductos extends ErrorDeNegocio
{
    public function __construct()
    {
        parent::__construct('No se puede eliminar la finca porque todavía tiene productos asociados.');
    }
}

