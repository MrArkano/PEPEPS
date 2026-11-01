<?php

declare(strict_types=1);

namespace App\Enums;

enum MetodoPago: string
{
    case Pse = 'PSE';
    case TarjetaCredito = 'tarjeta_credito';
    case TarjetaDebito = 'tarjeta_debito';
    case Transferencia = 'transferencia';
    case Efectivo = 'efectivo';
}

