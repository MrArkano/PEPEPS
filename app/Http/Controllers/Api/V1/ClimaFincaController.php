<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ProductorRepositorio;
use App\Services\Externos\ServicioMeteorologicoAgricola;
use Illuminate\Http\JsonResponse;

class ClimaFincaController extends Controller
{
    public function __construct(
        private readonly ProductorRepositorio $productores,
        private readonly ServicioMeteorologicoAgricola $climaService,
    ) {}

    /**
     * Consulta el clima y pronóstico agropecuario en tiempo real para una finca de Santander
     * consumiendo la API Externa de Open-Meteo.
     */
    public function show(string $fincaId): JsonResponse
    {
        try {
            $finca = $this->productores->encontrarOFallar($fincaId);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => "Finca campesina con ID {$fincaId} no encontrada.",
            ], 404);
        }

        $clima = $this->climaService->consultarClimaFinca($finca);

        return response()->json([
            'status' => 'success',
            'data' => $clima,
        ], 200);
    }
}
