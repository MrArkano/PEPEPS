<?php

declare(strict_types=1);

namespace App\Enums;

enum EstadoEntrega: string
{
    case Programada = 'programada';
    case EnCamino = 'en_camino';
    case Entregado = 'entregado';
    case Cancelada = 'cancelada';
}

