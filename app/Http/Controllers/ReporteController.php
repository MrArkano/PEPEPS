<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\Contracts\ReporteRepositorio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class ReporteController extends Controller
{
    public function __construct(private readonly ReporteRepositorio $reportes) {}

    public function index(Request $request): View
    {
        $pestana = $request->query('reporte', 'productos-mas-vendidos');

        $datos = match ($pestana) {
            'productos-mas-vendidos' => $this->reportes->productosMasVendidosPorMunicipio(),
            'productores-pendientes' => $this->reportes->productoresConPedidosPendientes(),
            'historial-consumidores' => $this->reportes->historialPedidosPorConsumidor(),
            'pagos-por-medio' => $this->reportes->pagosPorMedioYEstado(),
            'stock-critico' => $this->reportes->productosAgotadosOStockBajo(),
            default => $this->reportes->productosMasVendidosPorMunicipio(),
        };

        return view('reportes.index', [
            'pestanaActiva' => $pestana,
            'datos' => $datos,
        ]);
    }
}

