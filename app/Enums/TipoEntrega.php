<?php

declare(strict_types=1);

namespace App\Enums;

enum TipoEntrega: string
{
    case Domicilio = 'domicilio';
    case RecogidaPunto = 'recogida_punto';
}

