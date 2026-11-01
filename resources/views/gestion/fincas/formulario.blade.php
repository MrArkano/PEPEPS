@extends('layouts.app')

@php
    $esEdicion = (bool) $finca->getKey();
    $esAdmin = auth()->user()->tieneRol(\App\Enums\Rol::Administrador);
@endphp

@section('titulo', $esEdicion ? "Editar: {$finca->nombre_finca_asociacion}" : 'Nueva Finca')

@section('contenido')
    <nav aria-label="Ruta de navegación" class="mb-4 text-sm">
        <a href="{{ route('gestion.fincas.index') }}" class="font-semibold text-emerald-800 underline underline-offset-2">← Volver al listado</a>
    </nav>

    <div class="tarjeta mx-auto max-w-2xl">
        <h1 class="text-2xl font-bold text-stone-900">{{ $esEdicion ? "Editar Finca: {$finca->nombre_finca_asociacion}" : 'Registrar Nueva Finca o Asociación' }}</h1>
        <p class="mt-1 text-sm text-stone-600">Configura los datos de procedencia y ubicación de la unidad productiva.</p>

        @if ($errors->any())
            <div class="alerta-error mt-4" role="alert">
                Revisa los campos marcados antes de continuar.
            </div>
        @endif

        <form method="POST" action="{{ $esEdicion ? route('gestion.fincas.update', $finca->getKey()) : route('gestion.fincas.store') }}" class="mt-6 space-y-4">
            @csrf
            @if ($esEdicion)
                @method('PUT')
            @endif

            <x-campo nombre="nombre_finca_asociacion" etiqueta="Nombre de la finca o asociación" :valor="$finca->nombre_finca_asociacion" maximo="80" placeholder="Ej. Finca El Mirador" />

            <div class="grid gap-4 sm:grid-cols-2">
                <x-campo nombre="municipio" etiqueta="Municipio" :valor="$finca->municipio" maximo="60" placeholder="Ej. Bucaramanga" />
                <x-campo nombre="direccion_rural" etiqueta="Vereda / Dirección rural" :valor="$finca->direccion_rural" maximo="120" placeholder="Ej. Vereda Sevilla, Km 3" />
            </div>

            <x-area-texto nombre="descripcion" etiqueta="Descripción de la actividad productiva" :valor="$finca->descripcion" maximo="500" />

            @if ($esAdmin)
                <div class="border-t border-stone-200 pt-4 mt-4 grid gap-4 sm:grid-cols-2">
                    <x-selector nombre="usuario_id" etiqueta="Productor responsable (Usuario)"
                                :opciones="$productores->mapWithKeys(fn ($u) => [(string) $u->getKey() => $u->nombre_completo . ' (' . $u->correo . ')'])->all()"
                                :valor="$finca->usuario_id ? (string) $finca->usuario_id : null" />

                    <x-selector nombre="estado_verificacion" etiqueta="Estado de verificación"
                                :opciones="['pendiente' => 'Pendiente', 'verificado' => 'Verificado (Insignia Oficial)']"
                                :valor="$finca->estado_verificacion ?? 'pendiente'" />
                </div>
            @endif

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-100">
                <a href="{{ route('gestion.fincas.index') }}" class="btn-secundario">Cancelar</a>
                <button type="submit" class="btn">
                    {{ $esEdicion ? 'Guardar Cambios' : 'Registrar Finca' }}
                </button>
            </div>
        </form>
    </div>
@endsection

