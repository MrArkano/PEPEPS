@extends('layouts.app')

@php
    $producto = $ficha->producto;
    $finca = $producto->productor;
@endphp

@section('titulo', $producto->nombre)

@section('contenido')
    <nav aria-label="Ruta de navegación" class="mb-4 text-sm">
        <a href="{{ route('catalogo.index') }}" class="font-semibold text-emerald-800 underline underline-offset-2">← Volver al catálogo</a>
    </nav>

    <article class="grid gap-6 md:grid-cols-2">
        <img src="{{ \App\Support\ImagenProducto::url($producto) }}" alt="Imagen de {{ $producto->nombre }}" width="320" height="240"
             decoding="async" class="aspect-[4/3] w-full rounded-2xl bg-emerald-50 object-cover shadow-sm">

        <div class="space-y-4">
            <div>
                <h1 class="text-2xl font-bold text-stone-900">{{ $producto->nombre }}</h1>
                <p class="text-sm text-stone-600">{{ $producto->categoria?->nombre }}</p>
            </div>

            <p class="text-3xl font-extrabold text-emerald-800">
                @cop($producto->precio)
                <span class="text-base font-normal text-stone-600">/ {{ $producto->unidad_medida }}</span>
            </p>

            <p class="text-stone-700 text-sm leading-relaxed">{{ $producto->descripcion }}</p>

            <div class="flex flex-wrap items-center gap-3">
                <x-estado-stock :producto="$producto" />
                <x-calificacion :datos="$ficha->calificacionProducto" />
            </div>

            <dl class="tarjeta grid grid-cols-2 gap-2 text-sm !p-4 bg-stone-50">
                <dt class="text-stone-600 text-xs uppercase tracking-wider">Presentación</dt>
                <dd class="font-semibold text-stone-900">{{ $producto->inventario['presentacion'] ?? '—' }}</dd>
                <dt class="text-stone-600 text-xs uppercase tracking-wider">Disponibles</dt>
                <dd class="font-semibold text-stone-900">{{ $producto->cantidadDisponible() }} {{ $producto->unidad_medida }}</dd>
            </dl>

            {{-- Formulario para añadir al carrito con selector de cantidad --}}
            <div class="pt-2">
                @if ($producto->estaAgotado())
                    <div class="alerta-error text-center py-2 text-xs">
                        Este producto se encuentra agotado temporalmente.
                    </div>
                @else
                    <form method="POST" action="{{ route('carrito.agregar') }}" class="flex items-end gap-3">
                        @csrf
                        <input type="hidden" name="producto_id" value="{{ $producto->getKey() }}">
                        
                        <div class="w-24">
                            <label for="cantidad" class="etiqueta text-xs">Cantidad</label>
                            <input id="cantidad" name="cantidad" type="number" value="1" min="1" max="{{ $producto->cantidadDisponible() }}" required class="campo !min-h-10 text-center font-bold">
                        </div>

                        <button type="submit" class="btn flex-1 !min-h-10">
                            🛒 Añadir al Carrito
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </article>

    {{-- Diferenciador: trazabilidad del origen --}}
    <section class="tarjeta mt-8" aria-labelledby="titulo-origen">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-stone-100 pb-3">
            <h2 id="titulo-origen" class="text-lg font-bold text-stone-900">Origen y Trazabilidad Campesina</h2>
            @if ($finca?->estaVerificado())
                <x-insignia-verificado />
            @else
                <span class="insignia-gris">Verificación pendiente</span>
            @endif
        </div>

        <dl class="mt-4 grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-stone-500 text-xs uppercase">Finca o asociación</dt>
                <dd class="font-bold text-stone-900">{{ $finca?->nombre_finca_asociacion }}</dd>
            </div>
            <div>
                <dt class="text-stone-500 text-xs uppercase">Vereda de procedencia</dt>
                <dd class="font-bold text-stone-900">{{ $finca?->direccion_rural }}</dd>
            </div>
            <div>
                <dt class="text-stone-500 text-xs uppercase">Municipio (Santander)</dt>
                <dd class="font-bold text-stone-900">{{ $finca?->municipio }}</dd>
            </div>
            <div>
                <dt class="text-stone-500 text-xs uppercase">Reputación del productor</dt>
                <dd class="mt-0.5"><x-calificacion :datos="$ficha->calificacionProductor" /></dd>
            </div>
        </dl>

        @if ($finca?->descripcion)
            <p class="mt-4 text-xs text-stone-600 bg-emerald-50/50 p-3 rounded-xl border border-emerald-100">{{ $finca->descripcion }}</p>
        @endif

        <div class="mt-4 pt-3 border-t border-stone-100">
            <a href="{{ route('catalogo.index', ['productor' => (string) $finca?->getKey()]) }}"
               class="text-xs font-semibold text-emerald-800 hover:underline">
                Ver todos los productos de {{ $finca?->nombre_finca_asociacion }} →
            </a>
        </div>
    </section>
@endsection
