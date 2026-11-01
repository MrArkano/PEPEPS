@extends('layouts.app')

@section('titulo', 'Gestión de Inventario')

@section('contenido')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">Control de Inventario</h1>
            <p class="text-stone-600 text-sm">Monitorea y actualiza existencias y estados en tiempo real.</p>
        </div>
    </div>

    @if (session('error'))
        <div class="alerta-error mt-4" role="alert">{{ session('error') }}</div>
    @endif

    {{-- Alerta destacada de productos críticos --}}
    @if ($criticos->isNotEmpty())
        <div class="mt-5 rounded-2xl border border-amber-300 bg-amber-50 p-4 sm:p-5" role="alert">
            <div class="flex items-start gap-3">
                <span class="text-amber-800 text-xl font-bold">⚠️</span>
                <div>
                    <h2 class="text-base font-bold text-amber-900">
                        Atención: Tienes {{ $criticos->count() }} {{ $criticos->count() === 1 ? 'producto crítico' : 'productos críticos' }} (agotados o con 10 unidades o menos)
                    </h2>
                    <p class="mt-1 text-xs text-amber-800">
                        Prioriza el reabastecimiento o actualización del estado de inventario de estos productos:
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($criticos as $crit)
                            <a href="#prod-{{ $crit->getKey() }}" class="inline-flex items-center gap-1.5 rounded-lg border border-amber-300 bg-white px-2.5 py-1 text-xs font-semibold text-amber-900 shadow-xs hover:bg-amber-100">
                                <span>{{ $crit->nombre }}</span>
                                <span class="rounded bg-amber-200 px-1 py-0.2 text-[10px]">{{ $crit->estaAgotado() ? 'Agotado' : $crit->cantidadDisponible() . ' disp.' }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Filtros --}}
    <form method="GET" action="{{ route('gestion.inventario.index') }}" class="tarjeta mt-5 grid gap-3 sm:grid-cols-3 lg:items-end">
        <div>
            <label for="q" class="etiqueta">Buscar</label>
            <input id="q" name="q" type="search" value="{{ $filtros['q'] ?? '' }}" placeholder="Nombre del producto..." class="campo">
        </div>

        <x-selector nombre="productor" etiqueta="Finca" :requerido="false" vacio="Todas mis fincas"
                    :opciones="$fincas->mapWithKeys(fn ($f) => [(string) $f->getKey() => $f->nombre_finca_asociacion])->all()"
                    :valor="$filtros['productor'] ?? null" />

        <div class="flex items-center gap-3">
            <button type="submit" class="btn">Filtrar</button>
            <a href="{{ route('gestion.inventario.index') }}" class="btn-secundario">Limpiar</a>
        </div>
    </form>

    <div class="mt-5 space-y-4">
        @forelse ($productos as $prod)
            <div id="prod-{{ $prod->getKey() }}" class="tarjeta {{ $prod->esCritico() ? 'border-amber-300 bg-amber-50/20' : '' }}">
                <div class="flex flex-wrap items-start justify-between gap-2 border-b border-stone-100 pb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-bold text-stone-900">{{ $prod->nombre }}</h2>
                            <x-estado-stock :producto="$prod" :gestion="true" />
                        </div>
                        <p class="text-xs text-stone-500">
                            {{ $prod->productor?->nombre_finca_asociacion }} · {{ $prod->productor?->municipio }}
                        </p>
                    </div>
                    <div class="text-right text-xs text-stone-500">
                        Última actualización:
                        <span class="font-medium text-stone-700">
                            {{ isset($prod->inventario['fecha_actualizacion']) ? \Carbon\Carbon::parse($prod->inventario['fecha_actualizacion'])->diffForHumans() : 'Sin registro' }}
                        </span>
                    </div>
                </div>

                {{-- Formulario rápido de actualización de inventario --}}
                <form method="POST" action="{{ route('gestion.inventario.actualizar', $prod->getKey()) }}" class="mt-4 grid gap-3 sm:grid-cols-4 sm:items-end">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="etiqueta text-xs">Presentación</label>
                        <input type="text" name="presentacion" value="{{ $prod->inventario['presentacion'] ?? '' }}" required class="campo !min-h-10 text-sm">
                    </div>

                    <div>
                        <label class="etiqueta text-xs">Cantidad disponible</label>
                        <input type="number" name="cantidad_disponible" value="{{ $prod->cantidadDisponible() }}" min="0" max="100000" required class="campo !min-h-10 text-sm">
                    </div>

                    <div>
                        <label class="etiqueta text-xs">Estado de inventario</label>
                        <select name="estado_inventario" required class="campo !min-h-10 text-sm">
                            <option value="disponible" @selected(($prod->inventario['estado'] ?? '') === 'disponible')>Disponible</option>
                            <option value="reservado" @selected(($prod->inventario['estado'] ?? '') === 'reservado')>Reservado</option>
                            <option value="agotado" @selected(($prod->inventario['estado'] ?? '') === 'agotado')>Agotado</option>
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="btn !min-h-10 w-full text-sm">
                            Guardar Stock
                        </button>
                    </div>
                </form>
            </div>
        @empty
            <div class="tarjeta text-center text-stone-500">
                No hay productos en esta consulta de inventario.
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $productos->links() }}
    </div>
@endsection

