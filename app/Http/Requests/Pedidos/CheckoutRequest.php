<?php

declare(strict_types=1);

namespace App\Http\Requests\Pedidos;

use Illuminate\Foundation\Http\FormRequest;

final class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'direccion' => trim((string) $this->input('direccion')),
            'municipio' => trim((string) $this->input('municipio')),
            'barrio' => trim((string) $this->input('barrio')),
            'referencia' => trim((string) $this->input('referencia')),
        ]);
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'tipo_entrega' => ['required', 'in:domicilio,recogida_punto'],
            'metodo_pago' => ['required', 'in:PSE,tarjeta_credito,tarjeta_debito,transferencia,efectivo'],
            'direccion' => ['required_if:tipo_entrega,domicilio', 'string', 'max:120'],
            'municipio' => ['required', 'string', 'max:60'],
            'barrio' => ['nullable', 'string', 'max:60'],
            'referencia' => ['nullable', 'string', 'max:120'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'tipo_entrega' => 'El tipo de entrega',
            'metodo_pago' => 'El método de pago',
            'direccion' => 'La dirección',
            'municipio' => 'El municipio',
        ];
    }
}

