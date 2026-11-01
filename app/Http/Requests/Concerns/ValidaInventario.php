<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Validator;

/** Reglas compartidas del inventario (formulario de producto y de inventario). */
trait ValidaInventario
{
    /** @return array<string, list<string>> */
    protected function reglasInventario(): array
    {
        return [
            'presentacion' => ['required', 'string', 'max:60'],
            'cantidad_disponible' => ['required', 'integer', 'min:0', 'max:100000'],
            'estado_inventario' => ['required', 'in:disponible,reservado,agotado'],
        ];
    }

    /** Coherencia: agotado ⇔ cantidad 0. */
    protected function validarCoherenciaInventario(Validator $validador): void
    {
        if ($validador->errors()->hasAny(['cantidad_disponible', 'estado_inventario'])) {
            return;
        }

        $cantidad = (int) $this->input('cantidad_disponible');
        $estado = $this->input('estado_inventario');

        if ($estado === 'agotado' && $cantidad !== 0) {
            $validador->errors()->add('estado_inventario', 'Un producto agotado debe tener cantidad 0.');
        }

        if ($estado !== 'agotado' && $cantidad === 0) {
            $validador->errors()->add('estado_inventario', 'Con cantidad 0 el estado debe ser "agotado".');
        }
    }
}

