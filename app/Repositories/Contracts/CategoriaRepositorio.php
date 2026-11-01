<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Categoria;
use Illuminate\Support\Collection;

interface CategoriaRepositorio
{
    /** @return Collection<int, Categoria> */
    public function activas(): Collection;
}

