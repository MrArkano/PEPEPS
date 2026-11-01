<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTO\FiltrosProductos;
use Illuminate\Foundation\Http\FormRequest;

/** Filtros por querystring (catálogo y gestión): todo se valida y tipifica en el borde. */
final class FiltrosProductosRequest extends FormRequest
{
    private const ID = ['nullable', 'string', 'regex:/^[a-f\d]{24}$/i'];

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:60'],
            'municipio' => ['nullable', 'string', 'max:60'],
            'categoria' => self::ID,
            'productor' => self::ID,
            'criticos' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /** @param  list<string>|null  $productorIdsPermitidos */
    public function filtros(?array $productorIdsPermitidos = null): FiltrosProductos
    {
        $valor = static fn (?string $v): ?string => ($v === null || trim($v) === '') ? null : trim($v);

        return new FiltrosProductos(
            busqueda: $valor($this->query('q')),
            municipio: $valor($this->query('municipio')),
            categoriaId: $valor($this->query('categoria')),
            productorId: $valor($this->query('productor')),
            productorIdsPermitidos: $productorIdsPermitidos,
            soloCriticos: $this->boolean('criticos'),
        );
    }
}

