@extends('layouts.app')

@section('titulo', 'Gestión de Productos')

@section('contenido')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">Mis Productos</h1>
            <p class="text-stone-600 text-sm">Administra la oferta de tus fincas.</p>
        </div>
        <a href="{{ route('gestion.productos.create') }}" class="btn">
            + Nuevo Producto
        </a>
    </div>

    @if (session('error'))
        <div class="alerta-error mt-4" role="alert">{{ session('error') }}</div>
    @endif

    {{-- Filtros --}}
    <form method="GET" action="{{ route('gestion.productos.index') }}" class="tarjeta mt-5 grid gap-3 sm:grid-cols-3 lg:items-end">
        <div>
            <label for="q" class="etiqueta">Buscar</label>
            <input id="q" name="q" type="search" value="{{ $filtros['q'] ?? '' }}" placeholder="Nombre del producto..." class="campo">
        </div>

        <x-selector nombre="productor" etiqueta="Finca" :requerido="false" vacio="Todas mis fincas"
                    :opciones="$fincas->mapWithKeys(fn ($f) => [(string) $f->getKey() => $f->nombre_finca_asociacion])->all()"
                    :valor="$filtros['productor'] ?? null" />

        <div class="flex gap-2">
            <button type="submit" class="btn">Filtrar</button>
            <a href="{{ route('gestion.productos.index') }}" class="btn-secundario">Limpiar</a>
        </div>
    </form>

    <div class="tarjeta mt-5 overflow-x-auto !p-0">
        <table class="w-full text-left text-sm text-stone-700">
            <thead class="border-b border-stone-200 bg-stone-50 text-xs uppercase text-stone-500">
                <tr>
                    <th scope="col" class="px-4 py-3">Producto</th>
                    <th scope="col" class="px-4 py-3">Finca / Municipio</th>
                    <th scope="col" class="px-4 py-3">Precio</th>
                    <th scope="col" class="px-4 py-3">Stock / Estado</th>
                    <th scope="col" class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($productos as $prod)
                    <tr class="hover:bg-stone-50">
                        <td class="px-4 py-3 font-semibold text-stone-900">
                            <a href="{{ route('productos.show', $prod->getKey()) }}" class="hover:underline text-emerald-800">
                                {{ $prod->nombre }}
                            </a>
                            <span class="block text-xs font-normal text-stone-500">{{ $prod->categoria?->nombre }}</span>
                        </td>
                        <td class="px-4 py-3">
                            {{ $prod->productor?->nombre_finca_asociacion }}
                            <span class="block text-xs text-stone-500">{{ $prod->productor?->municipio }}</span>
                        </td>
                        <td class="px-4 py-3 font-medium">
                            @cop($prod->precio) <span class="text-xs text-stone-500">/ {{ $prod->unidad_medida }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <x-estado-stock :producto="$prod" :gestion="true" />
                            <span class="ml-1 text-xs text-stone-500">({{ $prod->cantidadDisponible() }} {{ $prod->unidad_medida }})</span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                            <a href="{{ route('gestion.productos.edit', $prod->getKey()) }}" class="text-emerald-700 hover:underline font-semibold">Editar</a>
                            <form method="POST" action="{{ route('gestion.productos.destroy', $prod->getKey()) }}" class="inline-block" onsubmit="return confirm('¿Seguro que deseas eliminar este producto?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-700 hover:underline font-semibold">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-stone-500">
                            No tienes productos registrados que coincidan con la búsqueda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $productos->links() }}
    </div>
@endsection

