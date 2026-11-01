<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['correo' => mb_strtolower(trim((string) $this->input('correo')))]);
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'correo' => ['required', 'string', 'email', 'max:120'],
            'contrasena' => ['required', 'string', 'max:200'],
        ];
    }
}

