<?php

declare(strict_types=1);

namespace App\Enums;

enum Rol: string
{
    case Administrador = 'Administrador';
    case Productor = 'Productor';
    case Consumidor = 'Consumidor';

    /** Nombre de la ruta del panel principal de cada rol. */
    public function rutaPanel(): string
    {
        return match ($this) {
            self::Administrador => 'admin.panel',
            self::Productor => 'productor.panel',
            self::Consumidor => 'consumidor.panel',
        };
    }
}
