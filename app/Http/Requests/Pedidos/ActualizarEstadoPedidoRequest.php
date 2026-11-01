<?php

declare(strict_types=1);

namespace App\Http\Requests\Pedidos;

use App\Models\Pedido;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

final class ActualizarEstadoPedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        $pedido = $this->route('pedido');

        return $pedido instanceof Pedido && Gate::forUser($this->user())->allows('update', $pedido);
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'estado' => ['required', 'in:pendiente,confirmado,en_camino,entregado,cancelado'],
        ];
    }
}

