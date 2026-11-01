@extends('layouts.app')

@section('titulo', 'Gestión de Fincas y Asociaciones')

@section('contenido')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">Fincas y Asociaciones</h1>
            <p class="text-stone-600 text-sm">Orígenes y ubicaciones productivas en Santander.</p>
        </div>
        <a href="{{ route('gestion.fincas.create') }}" class="btn">
            + Nueva Finca
        </a>
    </div>

    @if (session('error'))
        <div class="alerta-error mt-4" role="alert">{{ session('error') }}</div>
    @endif

    <div class="tarjeta mt-5 overflow-x-auto !p-0">
        <table class="w-full text-left text-sm text-stone-700">
            <thead class="border-b border-stone-200 bg-stone-50 text-xs uppercase text-stone-500">
                <tr>
                    <th scope="col" class="px-4 py-3">Finca / Asociación</th>
                    <th scope="col" class="px-4 py-3">Ubicación</th>
                    <th scope="col" class="px-4 py-3">Verificación</th>
                    <th scope="col" class="px-4 py-3">Fecha de Registro</th>
                    <th scope="col" class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($fincas as $finca)
                    <tr class="hover:bg-stone-50">
                        <td class="px-4 py-3 font-semibold text-stone-900">
                            {{ $finca->nombre_finca_asociacion }}
                            @if ($finca->descripcion)
                                <span class="block text-xs font-normal text-stone-500 line-clamp-1">{{ $finca->descripcion }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            {{ $finca->municipio }}
                            <span class="block text-xs text-stone-500">{{ $finca->direccion_rural }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($finca->estaVerificado())
                                <x-insignia-verificado />
                            @else
                                <span class="insignia-gris">Pendiente</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-stone-500 text-xs">
                            {{ $finca->fecha_registro ? \Carbon\Carbon::parse($finca->fecha_registro)->format('d/m/Y') : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                            <a href="{{ route('gestion.fincas.edit', $finca->getKey()) }}" class="text-emerald-700 hover:underline font-semibold">Editar</a>
                            <form method="POST" action="{{ route('gestion.fincas.destroy', $finca->getKey()) }}" class="inline-block" onsubmit="return confirm('¿Seguro que deseas eliminar esta finca?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-700 hover:underline font-semibold">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-stone-500">
                            No hay fincas registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $fincas->links() }}
    </div>
@endsection

