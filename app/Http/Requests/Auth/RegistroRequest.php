<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class RegistroRequest extends FormRequest
{
    private const PATRON_NOMBRE = "/^[\\pL][\\pL\\s'.-]*$/u";

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombre' => trim((string) $this->input('nombre')),
            'apellido' => trim((string) $this->input('apellido')),
            'correo' => mb_strtolower(trim((string) $this->input('correo'))),
            'telefono' => preg_replace('/\D/', '', (string) $this->input('telefono')),
        ]);
    }

    /**
     * Solo estos campos llegan al servicio (el rol nunca viene del cliente).
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:60', 'regex:'.self::PATRON_NOMBRE],
            'apellido' => ['required', 'string', 'max:60', 'regex:'.self::PATRON_NOMBRE],
            'correo' => ['required', 'string', 'email:rfc', 'max:120', Rule::unique('mongodb.usuarios', 'correo')],
            'telefono' => ['required', 'regex:/^3\d{9}$/'],
            'contrasena' => ['required', 'string', 'max:200', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['contrasena' => 'contraseña'];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'telefono.regex' => 'El teléfono debe ser un celular colombiano de 10 dígitos (empieza por 3).',
            'correo.unique' => 'Este correo ya está registrado.',
        ];
    }
}

