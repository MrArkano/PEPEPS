<?php

declare(strict_types=1);

namespace App\Services\Pedidos;

use App\Models\Producto;
use App\Repositories\Contracts\ProductoRepositorio;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * Carrito de compras en sesión.
 * Utiliza bcmath para todos los cálculos monetarios sin pérdida de precisión.
 */
final class CarritoService
{
    private const SESION_CLAVE = 'pepeps_carrito';

    public function __construct(
        private readonly Session $session,
        private readonly ProductoRepositorio $productos,
    ) {}

    /** @return array<string, int> Map de productoId => cantidad */
    public function items(): array
    {
        return $this->session->get(self::SESION_CLAVE, []);
    }

    public function totalItems(): int
    {
        return array_sum($this->items());
    }

    public function agregar(string $productoId, int $cantidad = 1): void
    {
        $items = $this->items();
        $actual = $items[$productoId] ?? 0;
        $items[$productoId] = $actual + $cantidad;

        $this->session->put(self::SESION_CLAVE, $items);
    }

    public function actualizar(string $productoId, int $cantidad): void
    {
        $items = $this->items();

        if ($cantidad <= 0) {
            unset($items[$productoId]);
        } else {
            $items[$productoId] = $cantidad;
        }

        $this->session->put(self::SESION_CLAVE, $items);
    }

    public function remover(string $productoId): void
    {
        $items = $this->items();
        unset($items[$productoId]);
        $this->session->put(self::SESION_CLAVE, $items);
    }

    public function vaciar(): void
    {
        $this->session->forget(self::SESION_CLAVE);
    }

    /**
     * Devuelve una colección de detalles con los modelos Producto hidratados
     * y subtotales calculados.
     *
     * @return Collection<int, array{producto: Producto, cantidad: int, subtotal: string}>
     */
    public function detalle(): Collection
    {
        $items = $this->items();
        if ($items === []) {
            return collect();
        }

        $resultado = collect();

        foreach ($items as $productoId => $cantidad) {
            try {
                $producto = $this->productos->encontrarOFallar($productoId);
                // Si el producto está inactivo o sin stock, no se calcula
                $subtotal = bcmul((string) $producto->precio, (string) $cantidad, 2);

                $resultado->push([
                    'producto' => $producto,
                    'cantidad' => $cantidad,
                    'subtotal' => $subtotal,
                ]);
            } catch (\Throwable) {
                // Si el producto fue eliminado, se remueve silenciosamente
                $this->remover($productoId);
            }
        }

        return $resultado;
    }

    public function subtotal(): string
    {
        $acumulado = '0.00';
        foreach ($this->detalle() as $fila) {
            $acumulado = bcadd($acumulado, $fila['subtotal'], 2);
        }

        return $acumulado;
    }
}

