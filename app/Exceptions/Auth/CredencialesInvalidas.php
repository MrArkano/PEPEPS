<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

final class CredencialesInvalidas extends ErrorAutenticacion
{
    public function __construct()
    {
        // Mensaje único: no revela si el correo existe o está inactivo.
        parent::__construct('Las credenciales no son correctas o la cuenta no está activa.');
    }
}

