@extends('layouts.app')

@section('titulo', 'Panel del Consumidor')

@section('contenido')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">Hola, {{ auth()->user()->nombre }} 👋</h1>
            <p class="text-sm text-stone-600">Bienvenido a tu espacio campesino en PEPEPS.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('catalogo.index') }}" class="btn text-xs !min-h-9">Explorar Catálogo 🌾</a>
            <a href="{{ route('pedidos.index') }}" class="btn-secundario text-xs !min-h-9">Mis Pedidos 📦</a>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <a href="{{ route('catalogo.index') }}" class="tarjeta flex flex-col justify-between hover:border-emerald-600 transition">
            <div>
                <span class="text-3xl">🧺</span>
                <h2 class="text-base font-bold text-stone-900 mt-2">Mercado Campesino</h2>
                <p class="text-xs text-stone-600 mt-1">Descubre frutas, verduras, tubérculos, lácteos y café fresco de Santander con origen verificado.</p>
            </div>
            <span class="text-xs font-semibold text-emerald-800 mt-4">Comprar ahora →</span>
        </a>

        <a href="{{ route('pedidos.index') }}" class="tarjeta flex flex-col justify-between hover:border-emerald-600 transition">
            <div>
                <span class="text-3xl">📦</span>
                <h2 class="text-base font-bold text-stone-900 mt-2">Historial de Pedidos</h2>
                <p class="text-xs text-stone-600 mt-1">Revisa el estado de tus compras, seguimiento de entrega a domicilio y califica a tus productores.</p>
            </div>
            <span class="text-xs font-semibold text-emerald-800 mt-4">Ver mis pedidos →</span>
        </a>
    </div>

    @php
        $ultimosPedidos = app(\App\Repositories\Contracts\PedidoRepositorio::class)->delConsumidor((string) auth()->user()->getKey(), 3);
    @endphp

    <section class="mt-8">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-bold text-stone-900">Tus Compras Recientes</h2>
            <a href="{{ route('pedidos.index') }}" class="text-xs font-semibold text-emerald-800 hover:underline">Ver todas</a>
        </div>

        <div class="mt-3 divide-y divide-stone-100 tarjeta !p-0">
            @forelse ($ultimosPedidos as $p)
                <div class="p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div>
                        <span class="text-xs font-bold text-stone-900">Pedido #{{ substr((string) $p->getKey(), -6) }}</span>
                        <span class="text-xs text-stone-500 block">{{ \Carbon\Carbon::parse($p->fecha)->format('d/m/Y h:i A') }} · {{ count($p->items ?? []) }} productos</span>
                    </div>
                    <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
                        <span class="font-bold text-emerald-900 text-sm">@cop($p->total)</span>
                        <span class="insignia capitalize">{{ $p->estado->value }}</span>
                        <a href="{{ route('pedidos.show', $p->getKey()) }}" class="btn-secundario !min-h-8 !px-2.5 !py-1 text-xs">
                            Ver
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-xs text-stone-500">
                    Aún no tienes pedidos registrados. ¡Anímate a pedir tu primer mercado!
                </div>
            @endforelse
        </div>
    </section>
@endsection
