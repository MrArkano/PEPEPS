<?php

declare(strict_types=1);

namespace App\Http\Requests\Gestion;

use App\Enums\Rol;
use App\Models\Productor;
use App\Repositories\Contracts\UsuarioRepositorio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

final class FincaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $finca = $this->route('finca');

        return $finca instanceof Productor
            ? Gate::forUser($this->user())->allows('update', $finca)
            : Gate::forUser($this->user())->allows('create', Productor::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['nombre_finca_asociacion' => trim((string) $this->input('nombre_finca_asociacion'))]);
    }

    /** @return array<string, list<mixed>> */
    public function rules(UsuarioRepositorio $usuarios): array
    {
        $reglas = [
            'nombre_finca_asociacion' => ['required', 'string', 'max:80'],
            'municipio' => ['required', 'string', 'max:60'],
            'direccion_rural' => ['required', 'string', 'max:120'],
            'descripcion' => ['required', 'string', 'max:500'],
        ];

        // Dueño y verificación: solo el Administrador (un Productor no puede auto-verificarse).
        if ($this->user()->tieneRol(Rol::Administrador)) {
            $reglas['usuario_id'] = ['required', 'string', Rule::in(
                $usuarios->conRol(Rol::Productor)->map(static fn ($u): string => (string) $u->getKey())->all(),
            )];
            $reglas['estado_verificacion'] = ['required', 'in:pendiente,verificado'];
        }

        return $reglas;
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'nombre_finca_asociacion' => 'El nombre de la finca o asociación',
            'direccion_rural' => 'La vereda o dirección rural',
            'usuario_id' => 'El productor responsable',
            'estado_verificacion' => 'El estado de verificación',
        ];
    }
}

