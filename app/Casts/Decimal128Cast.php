<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use MongoDB\BSON\Decimal128;

/**
 * Convierte Decimal128 (BSON) <-> string decimal exacto.
 * Se expone como string para operar con bcmath sin perder precisión (nunca float).
 *
 * @implements CastsAttributes<string|null, string|int|float|Decimal128|null>
 */
final class Decimal128Cast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        return match (true) {
            $value instanceof Decimal128 => (string) $value,
            is_int($value), is_string($value) => (string) $value,
            // Datos heredados guardados como double: se normaliza a 2 decimales.
            is_float($value) => number_format($value, 2, '.', ''),
            default => throw new InvalidArgumentException("Valor no soportado para '{$key}'."),
        };
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?Decimal128
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Decimal128) {
            return $value;
        }

        $texto = (string) $value;

        if (! preg_match('/^-?\d+(\.\d+)?$/', $texto)) {
            throw new InvalidArgumentException("'{$key}' debe ser un número decimal válido.");
        }

        return new Decimal128($texto);
    }
}

