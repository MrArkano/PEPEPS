<?php

declare(strict_types=1);

namespace App\Services\Catalogo;

use App\DTO\FichaProducto;
use App\DTO\FiltrosProductos;
use App\DTO\PaginaCatalogo;
use App\Models\Producto;
use App\Repositories\Contracts\CalificacionRepositorio;
use App\Repositories\Contracts\CategoriaRepositorio;
use App\Repositories\Contracts\ProductoRepositorio;
use App\Repositories\Contracts\ProductorRepositorio;

final class CatalogoService
{
    private const POR_PAGINA = 12;

    public function __construct(
        private readonly ProductoRepositorio $productos,
        private readonly ProductorRepositorio $productores,
        private readonly CategoriaRepositorio $categorias,
        private readonly CalificacionRepositorio $calificaciones,
    ) {}

    public function pagina(FiltrosProductos $filtros): PaginaCatalogo
    {
        // Catálogo público: solo productos activos.
        $filtros = new FiltrosProductos(
            $filtros->busqueda, $filtros->municipio, $filtros->categoriaId, $filtros->productorId,
            $filtros->productorIdsPermitidos, soloActivos: true,
        );

        $pagina = $this->productos->buscar($filtros, self::POR_PAGINA);

        $idsProductor = $pagina->getCollection()
            ->map(static fn (Producto $p): string => (string) $p->productor_id)->unique()->values()->all();

        return new PaginaCatalogo(
            productos: $pagina,
            calificacionesProductor: $this->calificaciones->promedioPorProductor($idsProductor),
            municipios: $this->productores->municipios(),
            categorias: $this->categorias->activas(),
            productores: $this->productores->todos(),
        );
    }

    public function ficha(string $productoId): FichaProducto
    {
        $producto = $this->productos->encontrarOFallar($productoId);
        $idProductor = (string) $producto->productor_id;

        return new FichaProducto(
            producto: $producto,
            calificacionProducto: $this->calificaciones->promedioDeProducto($productoId),
            calificacionProductor: $this->calificaciones->promedioPorProductor([$idProductor])[$idProductor] ?? null,
        );
    }
}

