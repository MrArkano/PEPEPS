@props(['producto', 'gestion' => false])

@php
    $cantidad = $producto->cantidadDisponible();
    $estado = $producto->inventario['estado'] ?? 'disponible';
@endphp

@if ($producto->estaAgotado())
    <span class="insignia-alerta">Agotado</span>
@elseif ($producto->esCritico())
    <span class="insignia-alerta">{{ $gestion ? "Stock bajo: {$cantidad}" : "Quedan {$cantidad}" }}</span>
@elseif ($estado === 'reservado')
    <span class="insignia-gris">Reservado</span>
@else
    <span class="insignia">Disponible</span>
@endif

