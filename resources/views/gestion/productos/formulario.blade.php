@extends('layouts.app')

@php
    $esEdicion = (bool) $producto->getKey();
@endphp

@section('titulo', $esEdicion ? "Editar: {$producto->nombre}" : 'Nuevo Producto')

@section('contenido')
    <nav aria-label="Ruta de navegación" class="mb-4 text-sm">
        <a href="{{ route('gestion.productos.index') }}" class="font-semibold text-emerald-800 underline underline-offset-2">← Volver al listado</a>
    </nav>

    <div class="tarjeta mx-auto max-w-2xl">
        <h1 class="text-2xl font-bold text-stone-900">{{ $esEdicion ? "Editar: {$producto->nombre}" : 'Registrar Nuevo Producto' }}</h1>
        <p class="mt-1 text-sm text-stone-600">Completa la información técnica y comercial del producto.</p>

        @if ($errors->any())
            <div class="alerta-error mt-4" role="alert">
                Revisa los campos marcados antes de continuar.
            </div>
        @endif

        <form method="POST" action="{{ $esEdicion ? route('gestion.productos.update', $producto->getKey()) : route('gestion.productos.store') }}" class="mt-6 space-y-4">
            @csrf
            @if ($esEdicion)
                @method('PUT')
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-campo nombre="nombre" etiqueta="Nombre del producto" :valor="$producto->nombre" maximo="80" placeholder="Ej. Mora de Castilla" />
                </div>

                <x-selector nombre="productor_id" etiqueta="Finca u origen"
                            :opciones="$fincas->mapWithKeys(fn ($f) => [(string) $f->getKey() => $f->nombre_finca_asociacion . ' (' . $f->municipio . ')'])->all()"
                            :valor="$producto->productor_id ? (string) $producto->productor_id : null" />

                <x-selector nombre="categoria_id" etiqueta="Categoría"
                            :opciones="$categorias->mapWithKeys(fn ($c) => [(string) $c->getKey() => $c->nombre])->all()"
                            :valor="$producto->categoria_id ? (string) $producto->categoria_id : null" />

                <x-campo nombre="precio" etiqueta="Precio unitario (COP)" tipo="text" modo="decimal" :valor="$producto->precio" ayuda="Sin puntos ni comas de miles. Ej: 4500 o 4500.50" />

                <x-selector nombre="unidad_medida" etiqueta="Unidad de venta"
                            :opciones="['kg' => 'Kilogramo (kg)', 'libra' => 'Libra', 'unidad' => 'Unidad', 'litro' => 'Litro']"
                            :valor="$producto->unidad_medida ?? 'kg'" />

                <div class="sm:col-span-2">
                    <x-area-texto nombre="descripcion" etiqueta="Descripción" :valor="$producto->descripcion" maximo="500" />
                </div>

                <x-selector nombre="estado" etiqueta="Estado de publicación"
                            :opciones="['activo' => 'Activo (Visible en catálogo)', 'inactivo' => 'Inactivo (Oculto)']"
                            :valor="$producto->estado ?? 'activo'" />
            </div>

            <fieldset class="border-t border-stone-200 pt-4 mt-6">
                <legend class="text-base font-bold text-stone-900 mb-3">Inventario Inicial</legend>
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-campo nombre="presentacion" etiqueta="Presentación comercial" :valor="$producto->inventario['presentacion'] ?? ''" placeholder="Ej. Canastilla x 10 kg" />
                    
                    <x-campo nombre="cantidad_disponible" etiqueta="Cantidad disponible" tipo="number" minimo="0" maximo="100000" :valor="$producto->cantidadDisponible()" />

                    <x-selector nombre="estado_inventario" etiqueta="Estado de inventario"
                                :opciones="['disponible' => 'Disponible', 'reservado' => 'Reservado', 'agotado' => 'Agotado']"
                                :valor="$producto->inventario['estado'] ?? 'disponible'" />
                </div>
            </fieldset>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-100">
                <a href="{{ route('gestion.productos.index') }}" class="btn-secundario">Cancelar</a>
                <button type="submit" class="btn">
                    {{ $esEdicion ? 'Guardar Cambios' : 'Crear Producto' }}
                </button>
            </div>
        </form>
    </div>
@endsection

