<?php

declare(strict_types=1);

namespace App\Services\Productos;

use App\Enums\Rol;
use App\Exceptions\Catalogo\FincaConProductos;
use App\Models\Productor;
use App\Models\Usuario;
use App\Repositories\Contracts\ProductorRepositorio;

final class FincaService
{
    public function __construct(private readonly ProductorRepositorio $productores) {}

    /**
     * Solo el Administrador define dueño y estado de verificación; para un
     * Productor la finca queda a su nombre y "pendiente" de verificación.
     *
     * @param  array<string, mixed>  $datos
     */
    public function crear(Usuario $actor, array $datos): Productor
    {
        $esAdmin = $actor->tieneRol(Rol::Administrador);

        $finca = new Productor($this->atributos($datos));
        $finca->usuario_id = $esAdmin ? $datos['usuario_id'] : (string) $actor->getKey();
        $finca->estado_verificacion = $esAdmin ? $datos['estado_verificacion'] : 'pendiente';
        $finca->fecha_registro = now();

        $this->productores->guardar($finca);

        return $finca;
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizar(Usuario $actor, Productor $finca, array $datos): Productor
    {
        $finca->fill($this->atributos($datos));

        if ($actor->tieneRol(Rol::Administrador)) {
            $finca->usuario_id = $datos['usuario_id'];
            $finca->estado_verificacion = $datos['estado_verificacion'];
        }

        $this->productores->guardar($finca);

        return $finca;
    }

    /** @throws FincaConProductos */
    public function eliminar(Productor $finca): void
    {
        if ($this->productores->tieneProductos($finca)) {
            throw new FincaConProductos;
        }

        $this->productores->eliminar($finca);
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function atributos(array $datos): array
    {
        return [
            'nombre_finca_asociacion' => $datos['nombre_finca_asociacion'],
            'municipio' => $datos['municipio'],
            'direccion_rural' => $datos['direccion_rural'],
            'descripcion' => $datos['descripcion'],
        ];
    }
}

