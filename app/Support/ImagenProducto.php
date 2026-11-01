<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Producto;

final class ImagenProducto
{
    private const MARCADOR = 'img/producto-sin-imagen.svg';

    /** Primera imagen existente en public/img/productos; si no, un marcador SVG liviano. */
    public static function url(Producto $producto): string
    {
        $archivo = basename((string) ($producto->imagenes[0] ?? ''));

        if ($archivo !== '' && is_file(public_path("img/productos/{$archivo}"))) {
            return asset("img/productos/{$archivo}");
        }

        return asset(self::MARCADOR);
    }
}

