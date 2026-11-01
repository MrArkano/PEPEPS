<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use RuntimeException;

/** Base de los errores de autenticación; se traducen de forma centralizada en bootstrap/app.php. */
abstract class ErrorAutenticacion extends RuntimeException
{
}

