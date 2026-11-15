<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CrearPedidoApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'direccion' => ['required', 'string', 'max:200'],
            'municipio' => ['required', 'string', 'in:Bucaramanga,Floridablanca,Giron,Piedecuesta,Lebrija,San Gil,Socorro,Barichara,Zapatoca,Charta'],
            'barrio' => ['nullable', 'string', 'max:100'],
            'referencia' => ['nullable', 'string', 'max:250'],
            'metodo_pago' => ['required', 'string', 'in:PSE,tarjeta_credito,tarjeta_debito,transferencia,efectivo'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'string', 'regex:/^[a-fA-F0-9]{24}$/'],
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'direccion.required' => 'La dirección de entrega es obligatoria.',
            'municipio.required' => 'El municipio de entrega en Santander es obligatorio.',
            'municipio.in' => 'El municipio seleccionado no tiene cobertura actualmente en Santander.',
            'metodo_pago.required' => 'Debe seleccionar un método de pago válido.',
            'items.required' => 'Debe incluir al menos un producto campesino en el pedido.',
            'items.min' => 'El pedido debe contener al menos 1 producto.',
            'items.*.producto_id.required' => 'El identificador del producto es obligatorio.',
            'items.*.producto_id.regex' => 'El identificador del producto debe ser un ObjectId válido.',
            'items.*.cantidad.required' => 'La cantidad del producto es obligatoria.',
            'items.*.cantidad.min' => 'La cantidad mínima por producto es 1.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Error de validación al estructurar el pedido.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
