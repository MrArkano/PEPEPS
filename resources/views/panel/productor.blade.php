@extends('layouts.app')

@section('titulo', 'Panel del Productor')

@section('contenido')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">Hola, {{ auth()->user()->nombre }} 👋</h1>
            <p class="mt-1 text-sm text-stone-600">Panel de control de productor rural.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('gestion.productos.create') }}" class="btn !min-h-10 text-sm">+ Nuevo Producto</a>
            <a href="{{ route('gestion.fincas.create') }}" class="btn-secundario !min-h-10 text-sm">+ Nueva Finca</a>
        </div>
    </div>

    {{-- Alerta de stock bajo --}}
    @if ($criticos->isNotEmpty())
        <div class="mt-6 rounded-2xl border border-amber-300 bg-amber-50 p-4 sm:p-5" role="alert">
            <div class="flex items-start gap-3">
                <span class="text-2xl">⚠️</span>
                <div class="flex-1">
                    <h2 class="text-base font-bold text-amber-900">
                        Alerta de Existencias Bajas ({{ $criticos->count() }} {{ $criticos->count() === 1 ? 'producto' : 'productos' }})
                    </h2>
                    <p class="text-xs text-amber-800 mt-0.5">
                        Tienes productos agotados o con 10 o menos unidades disponibles en tus fincas.
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($criticos as $crit)
                            <a href="{{ route('gestion.inventario.index', ['productor' => (string) $crit->productor_id]) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-amber-300 bg-white px-2.5 py-1 text-xs font-semibold text-amber-900 hover:bg-amber-100">
                                <span>{{ $crit->nombre }}</span>
                                <span class="rounded bg-amber-200 px-1 text-[10px]">{{ $crit->estaAgotado() ? 'Agotado' : $crit->cantidadDisponible() . ' disp.' }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('gestion.inventario.index') }}" class="btn-secundario !min-h-8 !px-2.5 !py-1 text-xs font-bold text-amber-950 border-amber-300 bg-amber-100/50">
                    Gestionar Inventario →
                </a>
            </div>
        </div>
    @endif

    {{-- Accesos Rápidos --}}
    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <a href="{{ route('gestion.productos.index') }}" class="tarjeta flex flex-col justify-between hover:border-emerald-600 transition">
            <div>
                <span class="text-2xl">📦</span>
                <h2 class="text-base font-bold text-stone-900 mt-2">Mis Productos</h2>
                <p class="text-xs text-stone-600 mt-1">Registra, edita o desactiva los productos ofrecidos en el catálogo.</p>
            </div>
            <span class="text-xs font-semibold text-emerald-800 mt-3">Ir a productos →</span>
        </a>

        <a href="{{ route('gestion.inventario.index') }}" class="tarjeta flex flex-col justify-between hover:border-emerald-600 transition">
            <div>
                <span class="text-2xl">📊</span>
                <h2 class="text-base font-bold text-stone-900 mt-2">Inventario</h2>
                <p class="text-xs text-stone-600 mt-1">Actualiza existencias y estados (disponible, reservado, agotado).</p>
            </div>
            <span class="text-xs font-semibold text-emerald-800 mt-3">Gestionar stock →</span>
        </a>

        <a href="{{ route('gestion.fincas.index') }}" class="tarjeta flex flex-col justify-between hover:border-emerald-600 transition">
            <div>
                <span class="text-2xl">🏡</span>
                <h2 class="text-base font-bold text-stone-900 mt-2">Fincas y Veredas</h2>
                <p class="text-xs text-stone-600 mt-1">Gestiona las parcelas, asociaciones y orígenes de tu producción.</p>
            </div>
            <span class="text-xs font-semibold text-emerald-800 mt-3">Ver fincas ({{ $fincas->count() }}) →</span>
        </a>
    </div>

    {{-- Mis Fincas --}}
    <section class="mt-8" aria-labelledby="fincas-titulo">
        <div class="flex items-center justify-between">
            <h2 id="fincas-titulo" class="text-lg font-bold text-stone-900">Mis Fincas Registradas</h2>
            <a href="{{ route('gestion.fincas.index') }}" class="text-xs font-semibold text-emerald-800 hover:underline">Ver todas</a>
        </div>

        <ul class="mt-4 grid gap-3 sm:grid-cols-2">
            @forelse ($fincas as $finca)
                <li class="tarjeta flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="font-bold text-stone-900">{{ $finca->nombre_finca_asociacion }}</h3>
                            @if ($finca->estaVerificado())
                                <x-insignia-verificado />
                            @else
                                <span class="insignia-gris">Verificación pendiente</span>
                            @endif
                        </div>
                        <p class="text-xs text-stone-600 mt-1">{{ $finca->municipio }} · {{ $finca->direccion_rural }}</p>
                        @if ($finca->descripcion)
                            <p class="text-xs text-stone-500 mt-2 line-clamp-2">{{ $finca->descripcion }}</p>
                        @endif
                    </div>
                    <div class="mt-4 flex gap-2 border-t border-stone-100 pt-2 text-xs font-semibold">
                        <a href="{{ route('gestion.productos.index', ['productor' => (string) $finca->getKey()]) }}" class="text-emerald-800 hover:underline">Ver sus productos</a>
                        <span class="text-stone-300">|</span>
                        <a href="{{ route('gestion.fincas.edit', $finca->getKey()) }}" class="text-stone-600 hover:underline">Editar finca</a>
                    </div>
                </li>
            @empty
                <li class="tarjeta text-stone-600 sm:col-span-2 text-center py-6">
                    Aún no tienes fincas registradas.
                    <div class="mt-2">
                        <a href="{{ route('gestion.fincas.create') }}" class="btn !min-h-9 text-xs">Registrar primera finca</a>
                    </div>
                </li>
            @endforelse
        </ul>
    </section>
@endsection
