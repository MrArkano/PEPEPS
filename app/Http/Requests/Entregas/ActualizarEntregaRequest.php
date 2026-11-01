<?php

declare(strict_types=1);

namespace App\Http\Requests\Entregas;

use App\Models\Entrega;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

final class ActualizarEntregaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $entrega = $this->route('entrega');

        return $entrega instanceof Entrega && Gate::forUser($this->user())->allows('update', $entrega);
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'responsable_id' => ['nullable', 'string', 'regex:/^[a-f\d]{24}$/i'],
            'tipo_responsable' => ['nullable', 'in:Productor,Transportador'],
            'estado_entrega' => ['required', 'in:programada,en_camino,entregado,cancelada'],
            'fecha_programada' => ['nullable', 'date'],
            'fecha_entrega' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ];
    }
}

