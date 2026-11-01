<?php

declare(strict_types=1);

namespace App\Repositories\Mongo;

use App\Models\Categoria;
use App\Repositories\Contracts\CategoriaRepositorio;
use Illuminate\Support\Collection;

final class CategoriaRepositorioMongo implements CategoriaRepositorio
{
    public function activas(): Collection
    {
        return Categoria::query()->where('estado', 'activa')->orderBy('nombre')->get();
    }
}

