<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

final class DemasiadosIntentos extends ErrorAutenticacion
{
    public function __construct(public readonly int $segundosRestantes)
    {
        parent::__construct("Demasiados intentos. Vuelve a intentarlo en {$segundosRestantes} segundos.");
    }
}

