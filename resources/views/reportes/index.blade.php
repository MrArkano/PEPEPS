@extends('layouts.app')

@section('titulo', 'Reportes y Analítica PEPEPS')

@section('contenido')
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-stone-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">Analítica y Consultas NoSQL 📊</h1>
            <p class="text-xs text-stone-600 mt-0.5">Información estratégica y pipelines de agregación de MongoDB sobre los mercados campesinos.</p>
        </div>
    </div>

    {{-- Selector de Reportes (Pestañas) --}}
    <div class="mt-5 flex flex-wrap gap-2 border-b border-stone-200 pb-3 text-xs font-bold">
        <a href="{{ route('reportes.index', ['reporte' => 'productos-mas-vendidos']) }}"
           class="rounded-xl px-3 py-2 transition {{ $pestanaActiva === 'productos-mas-vendidos' ? 'bg-emerald-800 text-white shadow-xs' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
            1. Más vendidos por municipio
        </a>
        <a href="{{ route('reportes.index', ['reporte' => 'productores-pendientes']) }}"
           class="rounded-xl px-3 py-2 transition {{ $pestanaActiva === 'productores-pendientes' ? 'bg-emerald-800 text-white shadow-xs' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
            2. Productores con pedidos pendientes
        </a>
        <a href="{{ route('reportes.index', ['reporte' => 'historial-consumidores']) }}"
           class="rounded-xl px-3 py-2 transition {{ $pestanaActiva === 'historial-consumidores' ? 'bg-emerald-800 text-white shadow-xs' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
            3. Historial por consumidor
        </a>
        <a href="{{ route('reportes.index', ['reporte' => 'pagos-por-medio']) }}"
           class="rounded-xl px-3 py-2 transition {{ $pestanaActiva === 'pagos-por-medio' ? 'bg-emerald-800 text-white shadow-xs' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
            4. Pagos por medio y estado
        </a>
        <a href="{{ route('reportes.index', ['reporte' => 'stock-critico']) }}"
           class="rounded-xl px-3 py-2 transition {{ $pestanaActiva === 'stock-critico' ? 'bg-emerald-800 text-white shadow-xs' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
            5. Stock agotado / bajo por productor
        </a>
    </div>

    {{-- Contenido de la pestaña activa --}}
    <div class="mt-6">
        {{-- REPORTE 1 --}}
        @if ($pestanaActiva === 'productos-mas-vendidos')
            <div class="tarjeta">
                <div class="mb-4">
                    <span class="rounded bg-emerald-100 text-emerald-900 px-2 py-0.5 text-xs font-bold uppercase">Consulta 1</span>
                    <h2 class="text-lg font-bold text-stone-900 mt-1">Productos más vendidos agrupados por municipio del productor</h2>
                    <p class="text-xs text-stone-600 mt-0.5">
                        Detecta la demanda real en cada zona de Santander cruzando 3 colecciones (`pedidos`, `productos` y `productores`). Excluye pedidos cancelados.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-stone-50 border-y border-stone-200 text-xs font-bold uppercase text-stone-600">
                            <tr>
                                <th class="px-4 py-2.5">Municipio de Origen</th>
                                <th class="px-4 py-2.5">Producto</th>
                                <th class="px-4 py-2.5 text-right">Cantidad Total Vendida</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-xs">
                            @forelse ($datos as $fila)
                                <tr class="hover:bg-stone-50">
                                    <td class="px-4 py-2.5 font-bold text-stone-900">{{ $fila['municipio'] }}</td>
                                    <td class="px-4 py-2.5 font-medium text-emerald-900">{{ $fila['producto'] }}</td>
                                    <td class="px-4 py-2.5 text-right font-extrabold text-stone-800 text-sm">
                                        {{ $fila['cantidad_total_vendida'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-stone-500">Sin datos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        {{-- REPORTE 2 --}}
        @elseif ($pestanaActiva === 'productores-pendientes')
            <div class="tarjeta">
                <div class="mb-4">
                    <span class="rounded bg-amber-100 text-amber-900 px-2 py-0.5 text-xs font-bold uppercase">Consulta 2</span>
                    <h2 class="text-lg font-bold text-stone-900 mt-1">Productores con pedidos en estado "pendiente"</h2>
                    <p class="text-xs text-stone-600 mt-0.5">
                        Prioriza la gestión de despacho de los productores que tienen pedidos pendientes de cosecha o preparación sin duplicar conteos.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-stone-50 border-y border-stone-200 text-xs font-bold uppercase text-stone-600">
                            <tr>
                                <th class="px-4 py-2.5">Finca / Asociación</th>
                                <th class="px-4 py-2.5">Municipio</th>
                                <th class="px-4 py-2.5 text-right">Pedidos Pendientes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-xs">
                            @forelse ($datos as $fila)
                                <tr class="hover:bg-stone-50">
                                    <td class="px-4 py-2.5 font-bold text-stone-900">{{ $fila['finca_asociacion'] }}</td>
                                    <td class="px-4 py-2.5 text-stone-600">{{ $fila['municipio'] }}</td>
                                    <td class="px-4 py-2.5 text-right font-extrabold text-amber-900 text-sm">
                                        {{ $fila['cantidad_pedidos_pendientes'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-stone-500">No hay pedidos pendientes en este momento.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        {{-- REPORTE 3 --}}
        @elseif ($pestanaActiva === 'historial-consumidores')
            <div class="tarjeta">
                <div class="mb-4">
                    <span class="rounded bg-blue-100 text-blue-900 px-2 py-0.5 text-xs font-bold uppercase">Consulta 3</span>
                    <h2 class="text-lg font-bold text-stone-900 mt-1">Historial cronológico de pedidos por consumidor</h2>
                    <p class="text-xs text-stone-600 mt-0.5">
                        Permite a soporte al cliente y analítica revisar el comportamiento de compra de cada consumidor registrado.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-stone-50 border-y border-stone-200 text-xs font-bold uppercase text-stone-600">
                            <tr>
                                <th class="px-4 py-2.5">Consumidor</th>
                                <th class="px-4 py-2.5">Fecha</th>
                                <th class="px-4 py-2.5">Modalidad</th>
                                <th class="px-4 py-2.5">Estado</th>
                                <th class="px-4 py-2.5 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-xs">
                            @forelse ($datos as $fila)
                                <tr class="hover:bg-stone-50">
                                    <td class="px-4 py-2.5 font-bold text-stone-900">{{ $fila['consumidor'] }}</td>
                                    <td class="px-4 py-2.5 text-stone-600">
                                        @php
                                            $f = $fila['fecha'] ?? null;
                                            $fechaStr = '—';
                                            if ($f instanceof \MongoDB\BSON\UTCDateTime) {
                                                $fechaStr = $f->toDateTime()->format('d/m/Y');
                                            } elseif (is_numeric($f)) {
                                                $fechaStr = \Carbon\Carbon::createFromTimestampMs((int) $f)->format('d/m/Y');
                                            } elseif ($f) {
                                                $fechaStr = \Carbon\Carbon::parse($f)->format('d/m/Y');
                                            }
                                        @endphp
                                        {{ $fechaStr }}
                                    </td>
                                    <td class="px-4 py-2.5 capitalize">{{ str_replace('_', ' ', (string) ($fila['tipo_entrega'] ?? '')) }}</td>
                                    <td class="px-4 py-2.5">
                                        <span class="rounded bg-stone-100 px-2 py-0.5 font-semibold capitalize">{{ $fila['estado'] ?? '' }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-bold text-emerald-900">
                                        @cop((string) ($fila['total'] ?? 0))
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-stone-500">Sin historial registrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        {{-- REPORTE 4 --}}
        @elseif ($pestanaActiva === 'pagos-por-medio')
            <div class="tarjeta">
                <div class="mb-4">
                    <span class="rounded bg-purple-100 text-purple-900 px-2 py-0.5 text-xs font-bold uppercase">Consulta 4</span>
                    <h2 class="text-lg font-bold text-stone-900 mt-1">Comportamiento financiero de pagos por método y estado</h2>
                    <p class="text-xs text-stone-600 mt-0.5">
                        Agrupa las transacciones monetarias según método de pago y estado de conciliación con conversión `$toDecimal`.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-stone-50 border-y border-stone-200 text-xs font-bold uppercase text-stone-600">
                            <tr>
                                <th class="px-4 py-2.5">Método de Pago</th>
                                <th class="px-4 py-2.5">Estado del Pago</th>
                                <th class="px-4 py-2.5 text-center">Cantidad de Pagos</th>
                                <th class="px-4 py-2.5 text-right">Valor Total Acumulado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-xs">
                            @forelse ($datos as $fila)
                                <tr class="hover:bg-stone-50">
                                    <td class="px-4 py-2.5 font-bold uppercase text-stone-900">{{ str_replace('_', ' ', $fila['metodo_pago']) }}</td>
                                    <td class="px-4 py-2.5">
                                        <span class="rounded px-2 py-0.5 font-semibold uppercase {{ $fila['estado_pago'] === 'aprobado' ? 'bg-emerald-100 text-emerald-900' : ($fila['estado_pago'] === 'rechazado' ? 'bg-red-100 text-red-900' : 'bg-amber-100 text-amber-900') }}">
                                            {{ $fila['estado_pago'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-center font-bold text-stone-700">{{ $fila['cantidad_pagos'] }}</td>
                                    <td class="px-4 py-2.5 text-right font-extrabold text-emerald-950 text-sm">
                                        @cop((string) $fila['valor_total'])
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-stone-500">Sin datos de pagos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        {{-- REPORTE 5 --}}
        @elseif ($pestanaActiva === 'stock-critico')
            <div class="tarjeta">
                <div class="mb-4">
                    <span class="rounded bg-red-100 text-red-900 px-2 py-0.5 text-xs font-bold uppercase">Consulta 5</span>
                    <h2 class="text-lg font-bold text-stone-900 mt-1">Productos agotados o con existencias bajas (&le; 10 unidades)</h2>
                    <p class="text-xs text-stone-600 mt-0.5">
                        Detecta alertas de inventario crítico por productor para priorizar reabastecimiento o cosecha.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-stone-50 border-y border-stone-200 text-xs font-bold uppercase text-stone-600">
                            <tr>
                                <th class="px-4 py-2.5">Finca / Asociación</th>
                                <th class="px-4 py-2.5">Municipio</th>
                                <th class="px-4 py-2.5">Producto</th>
                                <th class="px-4 py-2.5 text-center">Stock Disponible</th>
                                <th class="px-4 py-2.5 text-right">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-xs">
                            @forelse ($datos as $fila)
                                <tr class="hover:bg-stone-50">
                                    <td class="px-4 py-2.5 font-bold text-stone-900">{{ $fila['productor'] }}</td>
                                    <td class="px-4 py-2.5 text-stone-600">{{ $fila['municipio'] }}</td>
                                    <td class="px-4 py-2.5 font-medium text-stone-800">{{ $fila['producto'] }}</td>
                                    <td class="px-4 py-2.5 text-center font-bold {{ $fila['cantidad_disponible'] === 0 ? 'text-red-700' : 'text-amber-800' }}">
                                        {{ $fila['cantidad_disponible'] }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right">
                                        <span class="rounded px-2 py-0.5 font-semibold capitalize {{ $fila['estado_inventario'] === 'agotado' ? 'bg-red-100 text-red-900' : 'bg-amber-100 text-amber-900' }}">
                                            {{ $fila['estado_inventario'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-stone-500">No hay productos en estado crítico.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
