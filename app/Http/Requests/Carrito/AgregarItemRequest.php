<?php

declare(strict_types=1);

namespace App\Http\Requests\Carrito;

use Illuminate\Foundation\Http\FormRequest;

final class AgregarItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'producto_id' => ['required', 'string', 'regex:/^[a-f\d]{24}$/i'],
            'cantidad' => ['nullable', 'integer', 'min:1', 'max:500'],
        ];
    }
}

