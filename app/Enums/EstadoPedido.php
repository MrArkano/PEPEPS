<?php

declare(strict_types=1);

namespace App\Enums;

enum EstadoPedido: string
{
    case Pendiente = 'pendiente';
    case Confirmado = 'confirmado';
    case EnCamino = 'en_camino';
    case Entregado = 'entregado';
    case Cancelado = 'cancelado';
}

