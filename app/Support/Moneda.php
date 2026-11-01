<?php

declare(strict_types=1);

namespace App\Support;

/** Formato de pesos colombianos a partir de decimales exactos (string). */
final class Moneda
{
    public static function formatear(?string $valor): string
    {
        if ($valor === null || $valor === '') {
            return '—';
        }

        [$entero, $decimales] = explode('.', bcadd($valor, '0', 2));

        return '$ '.number_format((int) $entero, 0, ',', '.').($decimales === '00' ? '' : ','.$decimales);
    }
}

