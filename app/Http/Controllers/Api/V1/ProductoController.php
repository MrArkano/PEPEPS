<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTO\FiltrosProductos;
use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Repositories\Contracts\ProductoRepositorio;
use App\Services\Catalogo\CatalogoService;
use App\Support\ImagenProducto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function __construct(
        private readonly CatalogoService $catalogoService,
        private readonly ProductoRepositorio $productoRepositorio,
    ) {}

    /**
     * Listado público de productos con filtros y paginación.
     */
    public function index(Request $request): JsonResponse
    {
        $filtros = new FiltrosProductos(
            busqueda: $request->query('q'),
            municipio: $request->query('municipio'),
            categoriaId: $request->query('categoria'),
            productorId: $request->query('productor'),
            productorIdsPermitidos: null,
            soloActivos: true,
        );

        $pagina = $this->catalogoService->pagina($filtros);

        $productosFormateados = $pagina->productos->getCollection()->map(function (Producto $p) {
            return [
                'id' => (string) $p->getKey(),
                'nombre' => $p->nombre,
                'descripcion' => $p->descripcion,
                'precio' => (float) (string) $p->precio,
                'precio_formateado' => '$' . number_format((float) (string) $p->precio, 0, ',', '.') . ' COP',
                'unidad_medida' => $p->unidad_medida,
                'imagen_url' => ImagenProducto::url($p),
                'inventario' => [
                    'disponible' => $p->cantidadDisponible(),
                    'estado' => $p->inventario['estado'] ?? 'disponible',
                    'presentacion' => $p->inventario['presentacion'] ?? 'Unidad',
                ],
                'productor' => [
                    'id' => (string) $p->productor_id,
                    'finca' => $p->productor?->nombre_finca_asociacion,
                    'municipio' => $p->productor?->municipio,
                    'verificado' => $p->productor?->estaVerificado() ?? false,
                ],
                'categoria' => [
                    'id' => (string) $p->categoria_id,
                    'nombre' => $p->categoria?->nombre,
                ],
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $productosFormateados,
            'meta' => [
                'total' => $pagina->productos->total(),
                'pagina_actual' => $pagina->productos->currentPage(),
                'por_pagina' => $pagina->productos->perPage(),
                'ultima_pagina' => $pagina->productos->lastPage(),
            ],
            'filtros_disponibles' => [
                'municipios' => $pagina->municipios,
                'categorias' => $pagina->categorias->map(fn($c) => ['id' => (string)$c->getKey(), 'nombre' => $c->nombre]),
            ],
        ], 200);
    }

    /**
     * Consulta detallada de un producto por ID con ficha de trazabilidad.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $ficha = $this->catalogoService->ficha($id);
            $p = $ficha->producto;

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => (string) $p->getKey(),
                    'nombre' => $p->nombre,
                    'descripcion' => $p->descripcion,
                    'precio' => (float) (string) $p->precio,
                    'precio_formateado' => '$' . number_format((float) (string) $p->precio, 0, ',', '.') . ' COP',
                    'unidad_medida' => $p->unidad_medida,
                    'imagen_url' => ImagenProducto::url($p),
                    'imagenes' => $p->imagenes ?? [],
                    'inventario' => [
                        'disponible' => $p->cantidadDisponible(),
                        'estado' => $p->inventario['estado'] ?? 'disponible',
                        'presentacion' => $p->inventario['presentacion'] ?? 'Unidad',
                    ],
                    'finca' => [
                        'id' => (string) $p->productor_id,
                        'nombre' => $p->productor?->nombre_finca_asociacion,
                        'municipio' => $p->productor?->municipio,
                        'direccion_rural' => $p->productor?->direccion_rural,
                        'verificado' => $p->productor?->estaVerificado() ?? false,
                        'calificacion_promedio' => $ficha->calificacionProductor,
                    ],
                    'categoria' => [
                        'id' => (string) $p->categoria_id,
                        'nombre' => $p->categoria?->nombre,
                    ],
                    'calificacion_producto' => $ficha->calificacionProducto,
                ],
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => "Producto con ID {$id} no encontrado en el catálogo campesino.",
            ], 404);
        }
    }
}
