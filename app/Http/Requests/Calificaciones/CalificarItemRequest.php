<?php

declare(strict_types=1);

namespace App\Http\Requests\Calificaciones;

use App\Models\Pedido;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

final class CalificarItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $pedido = $this->route('pedido');

        return $pedido instanceof Pedido && Gate::forUser($this->user())->allows('calificar', $pedido);
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'producto_id' => ['required', 'string', 'regex:/^[a-f\d]{24}$/i'],
            'calificacion' => ['required', 'integer', 'between:1,5'],
            'comentario' => ['nullable', 'string', 'max:500'],
        ];
    }
}

