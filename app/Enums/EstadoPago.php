<?php

declare(strict_types=1);

namespace App\Enums;

enum EstadoPago: string
{
    case Pendiente = 'pendiente';
    case Aprobado = 'aprobado';
    case Rechazado = 'rechazado';
    case Reembolsado = 'reembolsado';
}

