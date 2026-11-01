@extends('layouts.app')

@section('titulo', 'Mis Pedidos')

@section('contenido')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">Mis Pedidos</h1>
            <p class="text-sm text-stone-600">Historial y estado de tus compras campesinas.</p>
        </div>
        <a href="{{ route('catalogo.index') }}" class="btn-secundario text-xs !min-h-9">
            + Comprar más productos
        </a>
    </div>

    @if ($pedidos->isEmpty())
        <div class="tarjeta mt-6 text-center py-12">
            <span class="text-4xl block mb-2">📦</span>
            <h2 class="text-lg font-bold text-stone-800">No has realizado pedidos aún</h2>
            <p class="text-sm text-stone-600 mt-1">Conecta con los campesinos de Santander y compra productos frescos.</p>
            <div class="mt-4">
                <a href="{{ route('catalogo.index') }}" class="btn">Explorar Catálogo</a>
            </div>
        </div>
    @else
        <div class="tarjeta mt-6 overflow-x-auto !p-0">
            <table class="w-full text-left text-sm text-stone-700">
                <thead class="border-b border-stone-200 bg-stone-50 text-xs uppercase text-stone-500">
                    <tr>
                        <th scope="col" class="px-4 py-3">Fecha</th>
                        <th scope="col" class="px-4 py-3">Productos</th>
                        <th scope="col" class="px-4 py-3">Entrega</th>
                        <th scope="col" class="px-4 py-3">Total</th>
                        <th scope="col" class="px-4 py-3">Estado</th>
                        <th scope="col" class="px-4 py-3 text-right">Detalle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($pedidos as $ped)
                        <tr class="hover:bg-stone-50">
                            <td class="px-4 py-3 whitespace-nowrap text-xs font-semibold text-stone-900">
                                {{ \Carbon\Carbon::parse($ped->fecha)->format('d/m/Y h:i A') }}
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <span class="font-medium text-stone-900">{{ count($ped->items ?? []) }} {{ count($ped->items ?? []) === 1 ? 'producto' : 'productos' }}</span>
                                <span class="block text-stone-500 truncate max-w-xs">
                                    {{ implode(', ', array_column($ped->items ?? [], 'nombre_producto')) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-xs">
                                <span class="capitalize font-medium">{{ str_replace('_', ' ', $ped->tipo_entrega->value) }}</span>
                                <span class="block text-stone-500 text-[11px]">{{ $ped->direccion_entrega['municipio'] ?? '' }}</span>
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
                                <a href="{{ route('pedidos.show', $ped->getKey()) }}" class="btn-secundario !min-h-8 !px-2.5 !py-1 text-xs">
                                    Ver Detalle →
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $pedidos->links() }}
        </div>
    @endif
@endsection

