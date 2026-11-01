<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface ReporteRepositorio
{
    /** Consulta 1: Productos más vendidos por municipio (excluyendo cancelados). */
    public function productosMasVendidosPorMunicipio(): array;

    /** Consulta 2: Productores con pedidos pendientes. */
    public function productoresConPedidosPendientes(): array;

    /** Consulta 3: Historial de pedidos por consumidor. */
    public function historialPedidosPorConsumidor(): array;

    /** Consulta 4: Pagos agrupados por método de pago y estado. */
    public function pagosPorMedioYEstado(): array;

    /** Consulta 5: Productos agotados o con existencias bajas (<= 10) por productor. */
    public function productosAgotadosOStockBajo(): array;
}

