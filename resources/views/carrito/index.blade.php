@extends('layouts.app')

@section('titulo', 'Mi Carrito de Compras')

@section('contenido')
    <div class="flex items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-stone-900">Carrito de Compras 🛒</h1>
        @if ($detalle->isNotEmpty())
            <form method="POST" action="{{ route('carrito.vaciar') }}" onsubmit="return confirm('¿Seguro que deseas vaciar el carrito?');">
                @csrf
                <button type="submit" class="text-xs font-semibold text-red-700 hover:underline">Vaciar Carrito</button>
            </form>
        @endif
    </div>

    @if (session('error'))
        <div class="alerta-error mt-4" role="alert">{{ session('error') }}</div>
    @endif

    @if ($detalle->isEmpty())
        <div class="tarjeta mt-6 text-center py-12">
            <span class="text-4xl block mb-2">🧺</span>
            <h2 class="text-lg font-bold text-stone-800">Tu carrito está vacío</h2>
            <p class="text-sm text-stone-600 mt-1">Explora los productos frescos directos del campo santandereano.</p>
            <div class="mt-4">
                <a href="{{ route('catalogo.index') }}" class="btn">Explorar Catálogo</a>
            </div>
        </div>
    @else
        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-3">
                @foreach ($detalle as $linea)
                    @php
                        $producto = $linea['producto'];
                        $cantidad = $linea['cantidad'];
                        $subtotalItem = $linea['subtotal'];
                    @endphp
                    <div class="tarjeta flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3 flex-1">
                            <img src="{{ \App\Support\ImagenProducto::url($producto) }}" alt="" class="size-16 rounded-xl object-cover bg-emerald-50 shrink-0">
                            <div>
                                <h3 class="font-bold text-stone-900 leading-snug">
                                    <a href="{{ route('productos.show', $producto->getKey()) }}" class="hover:underline text-emerald-800">
                                        {{ $producto->nombre }}
                                    </a>
                                </h3>
                                <p class="text-xs text-stone-500">{{ $producto->productor?->nombre_finca_asociacion }} · {{ $producto->productor?->municipio }}</p>
                                <p class="text-xs font-semibold text-stone-700 mt-0.5">
                                    @cop($producto->precio) / {{ $producto->unidad_medida }}
                                </p>
                            </div>
                        </div>

                        {{-- Formulario para cambiar cantidad --}}
                        <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end border-t sm:border-t-0 pt-2 sm:pt-0 border-stone-100">
                            <form method="POST" action="{{ route('carrito.actualizar', $producto->getKey()) }}" class="flex items-center gap-1">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="cantidad" value="{{ $cantidad }}" min="1" max="{{ $producto->cantidadDisponible() }}"
                                       class="campo !min-h-9 !w-16 text-center text-sm font-bold" onchange="this.form.submit()">
                            </form>

                            <p class="font-bold text-emerald-900 text-base min-w-20 text-right">
                                @cop($subtotalItem)
                            </p>

                            <form method="POST" action="{{ route('carrito.remover', $producto->getKey()) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-stone-400 hover:text-red-700 p-1 text-sm font-bold" title="Eliminar ítem">✕</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Resumen del Carrito --}}
            <div>
                <div class="tarjeta sticky top-20">
                    <h2 class="text-lg font-bold text-stone-900 border-b border-stone-100 pb-2">Resumen de la Orden</h2>

                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-stone-600">Subtotal productos</dt>
                            <dd class="font-bold text-stone-900">@cop($subtotal)</dd>
                        </div>
                        <div class="flex justify-between text-xs text-stone-500">
                            <dt>Envío</dt>
                            <dd>Calculado en el checkout</dd>
                        </div>
                    </dl>

                    <div class="mt-6">
                        @auth
                            <a href="{{ route('carrito.checkout') }}" class="btn w-full !min-h-11 text-base font-bold">
                                Continuar a la Entrega →
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn w-full !min-h-11 text-sm">
                                Ingresa para Comprar
                            </a>
                        @endauth
                        <a href="{{ route('catalogo.index') }}" class="btn-secundario w-full mt-2 text-xs">
                            ← Seguir explorando
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

