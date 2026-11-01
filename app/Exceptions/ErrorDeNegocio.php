<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/** Base de errores de reglas de negocio; se muestran al usuario de forma centralizada. */
abstract class ErrorDeNegocio extends RuntimeException
{
}

