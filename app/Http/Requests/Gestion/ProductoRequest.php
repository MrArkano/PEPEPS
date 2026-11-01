<?php

declare(strict_types=1);

namespace App\Http\Requests\Gestion;

use App\Http\Requests\Concerns\ValidaInventario;
use App\Models\Producto;
use App\Repositories\Contracts\CategoriaRepositorio;
use App\Services\Productos\AlcanceProductor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class ProductoRequest extends FormRequest
{
    use ValidaInventario;

    public function authorize(): bool
    {
        $producto = $this->route('producto');

        return $producto instanceof Producto
            ? Gate::forUser($this->user())->allows('update', $producto)
            : Gate::forUser($this->user())->allows('create', Producto::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombre' => trim((string) $this->input('nombre')),
            'precio' => str_replace(',', '.', trim((string) $this->input('precio'))),
        ]);
    }

    /** @return array<string, list<mixed>> */
    public function rules(AlcanceProductor $alcance, CategoriaRepositorio $categorias): array
    {
        return [
            'nombre' => ['required', 'string', 'max:80'],
            'descripcion' => ['required', 'string', 'max:500'],
            // Zero Trust: solo fincas del propio actor (o cualquiera si es Administrador).
            'productor_id' => ['required', 'string', Rule::in($alcance->idsAsignables($this->user()))],
            'categoria_id' => ['required', 'string', Rule::in(
                $categorias->activas()->map(static fn ($c): string => (string) $c->getKey())->all(),
            )],
            'precio' => ['required', 'regex:/^\d{1,9}(\.\d{1,2})?$/', 'gt:0'],
            'unidad_medida' => ['required', 'in:kg,libra,unidad,litro'],
            'estado' => ['required', 'in:activo,inactivo'],
            ...$this->reglasInventario(),
        ];
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
            'productor_id' => 'La finca',
            'categoria_id' => 'La categoría',
            'unidad_medida' => 'La unidad de medida',
            'presentacion' => 'La presentación',
            'cantidad_disponible' => 'La cantidad',
            'estado_inventario' => 'El estado del inventario',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['precio.regex' => 'El precio debe ser un número positivo con máximo 2 decimales.'];
    }
}

