@extends('layouts.app')

@section('titulo', 'Bitácora de Auditoría')

@section('contenido')
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-stone-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">Bitácora de Auditoría 🛡️</h1>
            <p class="text-xs text-stone-600 mt-0.5">Registro inmutable de cambios de estado de pedidos, modificaciones de precios e inventario.</p>
        </div>
    </div>

    <div class="tarjeta mt-6 overflow-x-auto !p-0">
        <table class="w-full text-left text-sm text-stone-700">
            <thead class="border-b border-stone-200 bg-stone-50 text-xs uppercase text-stone-500">
                <tr>
                    <th scope="col" class="px-4 py-3">Fecha / Hora</th>
                    <th scope="col" class="px-4 py-3">Usuario Responsable</th>
                    <th scope="col" class="px-4 py-3">Entidad / Registro</th>
                    <th scope="col" class="px-4 py-3">Acción / Campo</th>
                    <th scope="col" class="px-4 py-3">Valor Anterior</th>
                    <th scope="col" class="px-4 py-3">Valor Nuevo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 text-xs">
                @forelse ($registros as $reg)
                    <tr class="hover:bg-stone-50">
                        <td class="px-4 py-3 whitespace-nowrap text-stone-500">
                            {{ $reg->fecha ? \Carbon\Carbon::parse($reg->fecha)->format('d/m/Y h:i:s A') : '—' }}
                        </td>
                        <td class="px-4 py-3 font-semibold text-stone-900">
                            {{ $reg->nombre_usuario ?? 'Sistema' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded bg-stone-100 px-2 py-0.5 font-bold uppercase text-[10px]">{{ $reg->entidad }}</span>
                            <span class="block text-[11px] font-mono text-stone-500">#{{ substr((string) $reg->entidad_id, -6) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-semibold text-stone-800">{{ str_replace('_', ' ', (string) $reg->accion) }}</span>
                            <span class="block text-stone-400 text-[10px] font-mono">{{ $reg->campo }}</span>
                        </td>
                        <td class="px-4 py-3 text-red-800 font-mono">
                            <span class="bg-red-50 border border-red-200 px-1.5 py-0.5 rounded text-[11px]">{{ $reg->valor_anterior }}</span>
                        </td>
                        <td class="px-4 py-3 text-emerald-800 font-mono">
                            <span class="bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded text-[11px] font-bold">{{ $reg->valor_nuevo }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-stone-500">
                            No se han registrado eventos de auditoría aún.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $registros->links() }}
    </div>
@endsection

