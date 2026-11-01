<?php

declare(strict_types=1);

namespace App\Http\Requests\Gestion;

use App\Http\Requests\Concerns\ValidaInventario;
use App\Models\Producto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

final class InventarioRequest extends FormRequest
{
    use ValidaInventario;

    public function authorize(): bool
    {
        $producto = $this->route('producto');

        return $producto instanceof Producto && Gate::forUser($this->user())->allows('update', $producto);
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return $this->reglasInventario();
    }

    /** @return list<\Closure(Validator): void> */
    public function after(): array
    {
        return [fn (Validator $v) => $this->validarCoherenciaInventario($v)];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'presentacion' => 'La presentación',
            'cantidad_disponible' => 'La cantidad',
            'estado_inventario' => 'El estado del inventario',
        ];
    }
}

