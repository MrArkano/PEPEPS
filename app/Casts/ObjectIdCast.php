<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use MongoDB\BSON\ObjectId;

/**
 * Referencias entre colecciones: se guardan siempre como ObjectId y se
 * leen como ObjectId (necesario para que los lookups/relaciones coincidan).
 * Al asignar se acepta un string hexadecimal (p. ej. el id de una ruta).
 *
 * @implements CastsAttributes<ObjectId|null, string|ObjectId|null>
 */
final class ObjectIdCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?ObjectId
    {
        return $value === null ? null : $this->aObjectId($value, $key);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?ObjectId
    {
        return $value === null ? null : $this->aObjectId($value, $key);
    }

    private function aObjectId(mixed $value, string $key): ObjectId
    {
        if ($value instanceof ObjectId) {
            return $value;
        }

        if (is_string($value) && preg_match('/^[a-f\d]{24}$/i', $value)) {
            return new ObjectId($value);
        }

        throw new InvalidArgumentException("'{$key}' no es un ObjectId válido.");
    }
}

