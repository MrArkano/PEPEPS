<?php

declare(strict_types=1);

namespace App\Services\Auditoria;

use App\Models\Auditoria;
use App\Models\Usuario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use MongoDB\BSON\ObjectId;

final class AuditoriaService
{
    public function registrarCambio(
        ?Usuario $actor,
        string $entidad,
        string $entidadId,
        string $accion,
        string $campo,
        mixed $anterior,
        mixed $nuevo,
    ): Auditoria {
        $auditoria = new Auditoria([
            'usuario_id' => $actor ? new ObjectId((string) $actor->getKey()) : null,
            'nombre_usuario' => $actor ? $actor->nombre_completo : 'Sistema',
            'entidad' => $entidad,
            'entidad_id' => new ObjectId($entidadId),
            'accion' => $accion,
            'campo' => $campo,
            'valor_anterior' => is_scalar($anterior) ? (string) $anterior : json_encode($anterior, JSON_UNESCAPED_UNICODE),
            'valor_nuevo' => is_scalar($nuevo) ? (string) $nuevo : json_encode($nuevo, JSON_UNESCAPED_UNICODE),
            'fecha' => now(),
        ]);

        $auditoria->save();

        return $auditoria;
    }

    public function paginar(int $porPagina = 15): LengthAwarePaginator
    {
        return Auditoria::query()->orderBy('fecha', 'desc')->paginate($porPagina);
    }
}

