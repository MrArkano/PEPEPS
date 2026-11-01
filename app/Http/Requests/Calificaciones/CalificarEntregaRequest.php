<?php

declare(strict_types=1);

namespace App\Http\Requests\Calificaciones;

use App\Models\Entrega;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

final class CalificarEntregaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $entrega = $this->route('entrega');

        return $entrega instanceof Entrega && Gate::forUser($this->user())->allows('calificar', $entrega);
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'calificacion' => ['required', 'integer', 'between:1,5'],
            'comentario' => ['nullable', 'string', 'max:500'],
        ];
    }
}

