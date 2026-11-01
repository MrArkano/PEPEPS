<?php

declare(strict_types=1);

namespace App\Http\Requests\Pagos;

use App\Models\Pago;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

final class ConciliarPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $pago = $this->route('pago');

        return $pago instanceof Pago && Gate::forUser($this->user())->allows('conciliar', $pago);
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'estado' => ['required', 'in:pendiente,aprobado,rechazado,reembolsado'],
        ];
    }
}

