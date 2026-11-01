@extends('layouts.app')

@section('titulo', 'Gestión de Pedidos')

@section('contenido')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">Gestión de Pedidos</h1>
            <p class="text-sm text-stone-600">Control de órdenes, despachos y seguimiento de pagos.</p>
        </div>
    </div>

    @if (session('error'))
        <div class="alerta-error mt-4" role="alert">{{ session('error') }}</div>
    @endif

    {{-- Seguimiento prioritario de pedidos pendientes --}}
    @if ($pendientes->isNotEmpty())
        <div class="mt-5 rounded-2xl border border-amber-300 bg-amber-50 p-4 sm:p-5" role="alert">
            <div class="flex items-start gap-3">
                <span class="text-2xl">⏳</span>
                <div>
                    <h2 class="text-base font-bold text-amber-900">
                        Seguimiento: Hay {{ $pendientes->count() }} {{ $pendientes->count() === 1 ? 'pedido pendiente' : 'pedidos pendientes' }} por gestionar y despachar
                    </h2>
                    <p class="text-xs text-amber-800 mt-0.5">
                        Prioriza estos pedidos para coordinar la cosecha, empaque y entrega con los clientes.
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($pendientes as $pend)
                            <a href="{{ route('gestion.pedidos.show', $pend->getKey()) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-amber-300 bg-white px-2.5 py-1 text-xs font-semibold text-amber-900 hover:bg-amber-100">
                                <span>Pedido #{{ substr((string) $pend->getKey(), -6) }}</span>
                                <span class="rounded bg-amber-200 px-1 text-[10px]">@cop($pend->total)</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Filtros de estado --}}
    <div class="tarjeta mt-5 flex flex-wrap items-center gap-2 text-xs font-semibold">
        <span class="text-stone-500 mr-2">Filtrar por estado:</span>
        <a href="{{ route('gestion.pedidos.index') }}"
           class="rounded-lg px-3 py-1.5 {{ empty($estadoSeleccionado) ? 'bg-emerald-800 text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
            Todos
        </a>
        @foreach (['pendiente' => 'Pendientes', 'confirmado' => 'Confirmados', 'en_camino' => 'En Camino', 'entregado' => 'Entregados', 'cancelado' => 'Cancelados'] as $val => $etiqueta)
            <a href="{{ route('gestion.pedidos.index', ['estado' => $val]) }}"
               class="rounded-lg px-3 py-1.5 {{ $estadoSeleccionado === $val ? 'bg-emerald-800 text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
                {{ $etiqueta }}
            </a>
        @endforeach
    </div>

    {{-- Tabla de Pedidos --}}
    <div class="tarjeta mt-5 overflow-x-auto !p-0">
        <table class="w-full text-left text-sm text-stone-700">
            <thead class="border-b border-stone-200 bg-stone-50 text-xs uppercase text-stone-500">
                <tr>
                    <th scope="col" class="px-4 py-3">ID / Fecha</th>
                    <th scope="col" class="px-4 py-3">Consumidor</th>
                    <th scope="col" class="px-4 py-3">Items / Resumen</th>
                    <th scope="col" class="px-4 py-3">Total</th>
                    <th scope="col" class="px-4 py-3">Estado</th>
                    <th scope="col" class="px-4 py-3 text-right">Gestión</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($pedidos as $ped)
                    <tr class="hover:bg-stone-50">
                        <td class="px-4 py-3 whitespace-nowrap text-xs">
                            <span class="font-bold text-stone-900 block">#{{ substr((string) $ped->getKey(), -6) }}</span>
                            <span class="text-stone-500">{{ \Carbon\Carbon::parse($ped->fecha)->format('d/m/Y') }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs">
                            <span class="font-bold text-stone-900 block">{{ $ped->consumidor?->nombre_completo ?? 'Cliente' }}</span>
                            <span class="text-stone-500">{{ $ped->direccion_entrega['municipio'] ?? '' }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs">
                            <span class="font-semibold text-stone-800">{{ count($ped->items ?? []) }} ítems:</span>
                            <span class="text-stone-500 block truncate max-w-xs">
                                {{ implode(', ', array_column($ped->items ?? [], 'nombre_producto')) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap font-bold text-emerald-900">
                            @cop($ped->total)
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @php
                                $colores = [
                                    'pendiente' => 'bg-amber-100 text-amber-900',
                                    'confirmado' => 'bg-blue-100 text-blue-900',
                                    'en_camino' => 'bg-purple-100 text-purple-900',
                                    'entregado' => 'bg-emerald-100 text-emerald-900',
                                    'cancelado' => 'bg-stone-200 text-stone-800',
                                ];
                            @endphp
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize {{ $colores[$ped->estado->value] ?? 'bg-stone-100 text-stone-800' }}">
                                {{ str_replace('_', ' ', $ped->estado->value) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <a href="{{ route('gestion.pedidos.show', $ped->getKey()) }}" class="btn-secundario !min-h-8 !px-2.5 !py-1 text-xs">
                                Administrar →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-stone-500">
                            No se encontraron pedidos con el criterio seleccionado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $pedidos->links() }}
    </div>
@endsection

