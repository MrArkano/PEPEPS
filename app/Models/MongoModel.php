<?php

declare(strict_types=1);

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * Base de los modelos del dominio PEPEPS: conexión MongoDB y sin timestamps
 * automáticos (las colecciones usan campos propios: fecha, fecha_creacion...).
 */
abstract class MongoModel extends Model
{
    protected $connection = 'mongodb';

    public $timestamps = false;
}

